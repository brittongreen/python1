<?php
if (!defined('ABSPATH')) exit;

function rr_core_sanitize_minutes($value) {
    $minutes = is_numeric($value) ? (int) $value : 0;
    return max(0, $minutes);
}

function rr_core_get_cache_ttl_seconds() {
    $minutes = (int) get_option('rr_core_cache_ttl', 10);
    if ($minutes <= 0) return 0;
    return $minutes * 60;
}

function rr_core_register_settings() {
    register_setting(
        'rr_core',
        'rr_core_cache_ttl',
        [
            'type'              => 'integer',
            'sanitize_callback' => 'rr_core_sanitize_minutes',
            'default'           => 10,
        ]
    );

    add_settings_section(
        'rr_core_section',
        __('Cache Settings', 'riggedroster-core'),
        '__return_null',
        'rr_core'
    );

    add_settings_field(
        'rr_core_cache_ttl',
        __('Cache TTL (minutes)', 'riggedroster-core'),
        'rr_core_render_ttl_field',
        'rr_core',
        'rr_core_section'
    );
}
add_action('admin_init', 'rr_core_register_settings');

function rr_core_render_ttl_field() {
    $value = (int) get_option('rr_core_cache_ttl', 10);
    echo '<input type="number" min="0" step="1" id="rr_core_cache_ttl" name="rr_core_cache_ttl" value="' . esc_attr($value) . '" />';
    echo '<p class="description">' . esc_html__('0 disables caching. Higher values cache longer.', 'riggedroster-core') . '</p>';
}

function rr_core_add_options_page() {
    add_options_page(
        __('Rigged Roster Core', 'riggedroster-core'),
        __('Rigged Roster', 'riggedroster-core'),
        'manage_options',
        'rr_core',
        'rr_core_render_settings_page'
    );
}
add_action('admin_menu', 'rr_core_add_options_page');

function rr_core_render_settings_page() {
    if (!current_user_can('manage_options')) return;
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Rigged Roster Core', 'riggedroster-core') . '</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('rr_core');
    do_settings_sections('rr_core');
    submit_button();
    echo '</form>';
    echo '</div>';
}
