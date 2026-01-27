<?php

namespace AILA\Admin;

defined('ABSPATH') || exit;

class AdminPage {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    /**
     * Register admin menu
     */
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

    /**
     * Render admin page
     */
    public function render_page(): void {
        if ( ! current_user_can('manage_options') ) {
            return;
        }

        ?>
        <div class="wrap">
            <h1>AI Log Analyzer</h1>

            <p>
                Plugin loaded successfully.
            </p>

            <p>
                Core analyzer is connected. No analysis is running yet.
            </p>
        </div>
        <?php
    }
}
