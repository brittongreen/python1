<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_options_page(
        'Rigged Roster Settings',
        'Rigged Roster',
        'manage_options',
        'rr-core-settings',
        'rr_core_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('rr_core', 'rr_cache_ttl', [
        'type' => 'integer',
        'default' => 900, // 15 minutes
        'sanitize_callback' => 'absint',
    ]);
});

function rr_core_settings_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
      <h1>Rigged Roster Settings</h1>
      <form method="post" action="options.php">
        <?php settings_fields('rr_core'); ?>
        <?php do_settings_sections('rr_core'); ?>

        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="rr_cache_ttl">Cache TTL (seconds)</label></th>
            <td>
              <input name="rr_cache_ttl" id="rr_cache_ttl" type="number" min="60" step="60"
                     value="<?php echo esc_attr(get_option('rr_cache_ttl', 900)); ?>">
              <p class="description">How long to cache API responses (default 900s = 15 minutes).</p>
            </td>
          </tr>
        </table>

        <?php submit_button(); ?>
      </form>
    </div>
    <?php
}
