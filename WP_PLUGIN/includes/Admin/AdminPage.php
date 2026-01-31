<?php

namespace AILA\Admin;

use AILA\Core\Analyzer;

defined('ABSPATH') || exit;

class AdminPage
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function registerMenu(): void
    {
        add_menu_page(
            'AI Log Analyzer',
            'AI Log Analyzer',
            'manage_options',
            'ai-log-analyzer',
            [$this, 'renderPage'],
            'dashicons-search',
            80
        );
    }

    public function enqueueAssets(): void
    {
        wp_enqueue_script(
            'aila-admin',
            AILA_PLUGIN_URL . 'assets/admin.js',
            ['jquery'],
            AILA_VERSION,
            true
        );

        wp_localize_script('aila-admin', 'AILA_AJAX', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('aila_ai_explain'),
        ]);
    }

    public function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Save API key
        if (isset($_POST['aila_save_api_key'])) {
            check_admin_referer('aila_save_api_key');
            update_option(
                'aila_ai_api_key',
                sanitize_text_field($_POST['aila_ai_api_key'] ?? '')
            );
            echo '<div class="notice notice-success"><p>API key saved.</p></div>';
        }

        $result = null;

        if (isset($_POST['aila_run_analysis'])) {
            check_admin_referer('aila_run_analysis');

            $source = $_POST['log_source'] ?? 'manual';

            if ($source === 'wp_debug') {
                $file = WP_CONTENT_DIR . '/debug.log';
                $log  = file_exists($file) ? file_get_contents($file) : '';
            } else {
                $log = trim($_POST['log_text'] ?? '');
            }

            if ($log) {
                $analyzer = new Analyzer();
                $result = $analyzer->analyzeFromString($log);
            }
        }
        ?>

        <div class="wrap">
            <h1>AI Log Analyzer</h1>

            <!-- AI SETTINGS -->
            <h2>AI Settings</h2>
            <form method="post">
                <?php wp_nonce_field('aila_save_api_key'); ?>
                <input
                    type="password"
                    name="aila_ai_api_key"
                    value="<?php echo esc_attr(get_option('aila_ai_api_key', '')); ?>"
                    class="regular-text"
                    placeholder="Enter AI API key"
                />
                <button class="button" name="aila_save_api_key">
                    Save API Key
                </button>
                <p class="description">
                    AI is used only when you explicitly ask for help on an error.
                </p>
            </form>

            <hr>

            <!-- LOG SOURCE -->
            <h2>Log Source</h2>
            <form method="post">
                <?php wp_nonce_field('aila_run_analysis'); ?>

                <label>
                    <input type="radio" name="log_source" value="manual" checked>
                    Paste log manually
                </label><br>

                <label>
                    <input type="radio" name="log_source" value="wp_debug">
                    Use WordPress debug.log
                </label>

                <textarea
                    name="log_text"
                    rows="8"
                    style="width:100%;margin-top:10px;"
                    placeholder="Paste log content here..."
                ></textarea>

                <p>
                    <button class="button button-primary" name="aila_run_analysis">
                        Run Analysis
                    </button>
                </p>
            </form>

            <!-- ANALYSIS RESULT -->
            <?php if ($result): ?>
                <h2>Analysis Result</h2>

                <p><strong>Total Lines:</strong> <?php echo esc_html($result['total_lines']); ?></p>
                <p><strong>Detected Errors:</strong> <?php echo esc_html($result['error_count']); ?></p>

                <?php foreach ($result['errors'] as $error): ?>
                    <div
                        class="aila-error"
                        data-error="<?php echo esc_attr(json_encode($error)); ?>"
                        style="border:1px solid #ddd;padding:12px;margin-bottom:12px;"
                    >
                        <strong><?php echo esc_html($error['type']); ?></strong>
                        (<?php echo esc_html($error['severity']); ?>)

                        <p><?php echo esc_html($error['message']); ?></p>

                        <button class="button aila-ai-btn">
                            Ask AI for help
                        </button>

                        <pre
                            class="aila-ai-response"
                            style="display:none;margin-top:10px;background:#f7f7f7;padding:10px;"
                        ></pre>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
