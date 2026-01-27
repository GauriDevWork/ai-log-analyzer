<?php

namespace AILA\Admin;

use AILA\Core\Analyzer;

defined('ABSPATH') || exit;

class AdminPage {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu(): void {
        add_menu_page(
            'AI Log Analyzer',
            'AI Log Analyzer',
            'manage_options',
            'ai-log-analyzer',
            [$this, 'render_page'],
            'dashicons-search',
            80
        );
    }

    public function render_page(): void {
        if ( ! current_user_can('manage_options') ) {
            return;
        }

        $result = null;
        $error  = null;

        if ( isset($_POST['aila_run_analysis']) ) {
            check_admin_referer('aila_analyze_logs');

            $source = sanitize_text_field($_POST['log_source'] ?? '');

            try {
                if ( $source === 'manual' ) {
                    $logContent = trim($_POST['log_text'] ?? '');
                } elseif ( $source === 'wp_debug' ) {
                    $logFile = WP_CONTENT_DIR . '/debug.log';

                    if ( ! file_exists($logFile) ) {
                        throw new \Exception('WordPress debug.log not found.');
                    }

                    $logContent = file_get_contents($logFile);
                } else {
                    throw new \Exception('Invalid log source selected.');
                }

                if ( empty($logContent) ) {
                    throw new \Exception('Log content is empty.');
                }

                // Call analyzer
                $analyzer = new Analyzer();
                $result   = $analyzer->analyzeFromString($logContent);

            } catch ( \Throwable $e ) {
                $error = $e->getMessage();
            }
        }

        ?>
        <div class="wrap">
            <h1>AI Log Analyzer</h1>

            <form method="post">
                <?php wp_nonce_field('aila_analyze_logs'); ?>

                <h3>Log Source</h3>

                <label>
                    <input type="radio" name="log_source" value="manual" checked>
                    Paste log manually
                </label><br>

                <label>
                    <input type="radio" name="log_source" value="wp_debug">
                    Use WordPress debug.log
                </label>

                <br><br>

                <textarea
                    name="log_text"
                    rows="10"
                    style="width:100%;"
                    placeholder="Paste log content here..."
                ></textarea>

                <br><br>

                <button type="submit" class="button button-primary" name="aila_run_analysis">
                    Run Analysis
                </button>
            </form>

            <?php if ( $error ): ?>
                <div class="notice notice-error">
                    <p><strong>Error:</strong> <?php echo esc_html($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( $result ): ?>
                <h2>Analysis Result</h2>
                <pre style="background:#fff; padding:15px; border:1px solid #ccc; max-height:500px; overflow:auto;">
<?php print_r($result); ?>
                </pre>
            <?php endif; ?>
        </div>
        <?php
    }
}
