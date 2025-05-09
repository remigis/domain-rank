<?php
/**
 * Plugin Name: Domain Rank Viewer
 * Description: Displays domain stats from JSON and optionally fetches PageRank from OpenPageRank API.
 * Version: 1.2
 * Author: remigis
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/viewer-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/viewer-template.php';

add_action('admin_init', function () {
    if (empty(defined('OPEN_PAGE_RANK_API_KEY') ? OPEN_PAGE_RANK_API_KEY : '')) {
        add_action('admin_notices', 'showMissingApiKeyNotice');
    }
});

add_action('admin_menu', function() {
    add_menu_page(
        'Domain Rank Viewer',
        'Domain Rank Viewer',
        'manage_options',
        'domain-rank-viewer',
      'renderPage',
        'dashicons-chart-bar',
        20
    );
});
