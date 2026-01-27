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
