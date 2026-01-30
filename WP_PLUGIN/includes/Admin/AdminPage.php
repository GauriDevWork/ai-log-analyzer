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

        // Handle API key save
        if ( isset($_POST['aila_save_api_key']) ) {
            check_admin_referer('aila_save_api_key');

            $apiKey = sanitize_text_field($_POST['aila_ai_api_key'] ?? '');
            update_option('aila_ai_api_key', $apiKey);

            echo '<div class="notice notice-success"><p>API key saved.</p></div>';
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
            <h2>AI Settings</h2>

            <form method="post">
                <?php wp_nonce_field('aila_save_api_key'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aila_ai_api_key">AI API Key</label>
                        </th>
                        <td>
                            <input
                                type="password"
                                id="aila_ai_api_key"
                                name="aila_ai_api_key"
                                value="<?php echo esc_attr(get_option('aila_ai_api_key', '')); ?>"
                                class="regular-text"
                            />
                            <p class="description">
                                Used to generate AI-based explanations for log analysis.
                            </p>
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="submit" class="button button-secondary" name="aila_save_api_key">
                        Save API Key
                    </button>
                </p>
            </form>

            <hr>

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
                <?php if ( $result ): ?>
                <h2>Analysis Summary</h2>

                <p><strong>Total Lines:</strong> <?php echo esc_html($result['total_lines']); ?></p>
                <p><strong>Detected Errors:</strong> <?php echo esc_html($result['error_count']); ?></p>

                <?php if ( ! empty($result['ai_summary']['ai_response']) ): ?>
                    <div class="notice notice-info">
                        <p><strong>AI Explanation</strong></p>
                        <pre><?php echo esc_html($result['ai_summary']['ai_response']); ?></pre>
                    </div>
                <?php endif; ?>

                <details>
                    <summary><strong>Raw Analysis Data</strong></summary>
                    <pre><?php print_r($result); ?></pre>
                </details>
            <?php endif; ?>

            <?php endif; ?>
        </div>
        <?php
    }
}
