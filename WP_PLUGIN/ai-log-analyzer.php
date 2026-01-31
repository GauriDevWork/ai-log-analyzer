<?php
/**
 * Plugin Name: AI Log Analyzer
 * Plugin URI:  https://github.com/your-username/ai-log-analyzer
 * Description: Analyze WordPress and PHP logs using AI to detect patterns, issues, and insights.
 * Version:     0.1.0
 * Author:      Gauri
 * Author URI:  https://your-site.com
 * License:     GPL v2 or later
 * Text Domain: ai-log-analyzer
 */

defined('ABSPATH') || exit;

/**
 * Plugin constants
 */
define('AILA_VERSION', '0.1.0');
define('AILA_PLUGIN_FILE', __FILE__);
define('AILA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AILA_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Autoload / manual includes
 * We are intentionally NOT using Composer yet.
 */
require_once AILA_PLUGIN_DIR . 'includes/Core/Analyzer.php';
require_once AILA_PLUGIN_DIR . 'includes/Parser/LogParser.php';
require_once AILA_PLUGIN_DIR . 'includes/Classifier/ErrorClassifier.php';
require_once AILA_PLUGIN_DIR . 'includes/Security/Redactor.php';
require_once AILA_PLUGIN_DIR . 'includes/AI/AIExplainer.php';
require_once AILA_PLUGIN_DIR . 'includes/Output/JsonFormatter.php';
require_once AILA_PLUGIN_DIR . 'includes/Admin/AdminPage.php';


/**
 * Plugin bootstrap class
 * Keeps global scope clean.
 */
final class AILA_Plugin {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Register WordPress hooks
     */
    private function init_hooks(): void {
        add_action('plugins_loaded', [$this, 'on_plugins_loaded']);
    }

    /**
     * Fired once all plugins are loaded
     */
    public function on_plugins_loaded(): void {
        // Future-safe place:
        // - load text domain
        // - register admin pages
        if ( is_admin() ) {
            new \AILA\Admin\AdminPage();
        } 
        // - register services
    }
}

/**
 * Initialize plugin
 */
AILA_Plugin::instance();

// JavaScript for admin page
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script(
        'aila-admin',
        AILA_PLUGIN_URL . 'assets/admin.js',
        ['jquery'],
        AILA_VERSION,
        true
    );

    wp_localize_script('aila-admin', 'AILA_AJAX', [
        'nonce' => wp_create_nonce('aila_ai_explain'),
    ]);
});


// AJAX handler for explaining errors
add_action('wp_ajax_aila_explain_error', function () {

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    check_ajax_referer('aila_ai_explain');

    $error = $_POST['error'] ?? null;
    if (!$error || !is_array($error)) {
        wp_send_json_error('Invalid error data');
    }

    $apiKey = get_option('aila_ai_api_key');
    if (!$apiKey) {
        wp_send_json_error('AI API key not configured');
    }

    try {
        $explainer = new \AILA\AI\AIExplainer($apiKey);
        $result = $explainer->explainSingleError($error);

        wp_send_json_success($result['ai_response'] ?? '');
    } catch (\Throwable $e) {
        wp_send_json_error($e->getMessage());
    }
});

