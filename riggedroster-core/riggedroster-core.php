<?php
/**
 * Plugin Name: Rigged Roster Core
 * Description: Custom shortcodes, widgets, scripts, and API integrations for riggedroster.com
 * Version: 1.0.0
 * Author: Bri Gre
 */

if (!defined('ABSPATH')) exit;

define('RR_CORE_PATH', plugin_dir_path(__FILE__));
define('RR_CORE_URL',  plugin_dir_url(__FILE__));
define('RR_CORE_VER',  '1.0.0');

/**
 * Enqueue site-wide CSS/JS (safe and theme-agnostic)
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('rr-core', RR_CORE_URL . 'assets/css/rr.css', [], RR_CORE_VER);
    wp_enqueue_script('rr-core', RR_CORE_URL . 'assets/js/rr.js', ['jquery'], RR_CORE_VER, true);
});

/**
 * Basic settings (e.g., cache TTL)
 */
require_once RR_CORE_PATH . 'includes/admin-settings.php';

/**
 * Shortcodes (e.g., [nhl_scores])
 */
require_once RR_CORE_PATH . 'includes/shortcodes.php';

/**
 * Classic widget (shows the same content as the shortcode)
 */
require_once RR_CORE_PATH . 'includes/widget.php';

/**
 * Activation: set default options if not present
 */
register_activation_hook(__FILE__, function () {
    if (get_option('rr_core_cache_ttl', null) === null) {
        add_option('rr_core_cache_ttl', 10);
    }
});
