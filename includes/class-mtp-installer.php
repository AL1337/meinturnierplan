<?php
/**
 * Plugin Installer Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Plugin Installer Class
 */
class MTP_Installer {

  /**
   * Constructor
   */
  public function __construct() {
    // Constructor can be used for any initialization if needed
  }

  /**
   * Plugin activation
   */
  public static function activate() {
    // Register the post type (needs to be done before flushing rewrite rules)
    self::register_post_type_for_activation();

    // Flush rewrite rules to ensure our custom post type URLs work
    flush_rewrite_rules();

    // Set default options if needed
    self::set_default_options();

    // Run any database updates if needed
    self::maybe_update_database();
  }

  /**
   * Plugin deactivation
   */
  public static function deactivate() {
    // Flush rewrite rules to clean up
    flush_rewrite_rules();

    // Clean up any temporary data if needed
    self::cleanup_temporary_data();
  }

  /**
   * Register post type for activation (temporary)
   */
  private static function register_post_type_for_activation() {
    // Simple registration for activation - the full registration is handled by MTP_Table_Post_Type
    register_post_type('mtp_table', array(
      'public' => true,
      'rewrite' => array('slug' => 'tournament-table'),
    ));
  }

  /**
   * Set default plugin options
   */
  private static function set_default_options() {
    // Set plugin version
    if (!get_option('mtp_plugin_version')) {
      add_option('mtp_plugin_version', MTP_PLUGIN_VERSION);
    }

    // Set default settings if needed
    if (!get_option('mtp_default_settings')) {
      $default_settings = array(
        'default_width' => '300',
        'default_height' => '152',
        'default_font_size' => '9',
        'default_text_color' => '000000',
        'default_main_color' => '173f75',
      );
      add_option('mtp_default_settings', $default_settings);
    }
  }

  /**
   * Maybe update database
   */
  private static function maybe_update_database() {
    $current_version = get_option('mtp_plugin_version', '0.0.0');

    // If this is a new installation or upgrade, run updates
    if (version_compare($current_version, MTP_PLUGIN_VERSION, '<')) {
      self::run_database_updates($current_version);
      update_option('mtp_plugin_version', MTP_PLUGIN_VERSION);
    }
  }

  /**
   * Run database updates
   */
  private static function run_database_updates($from_version) {
    // Add version-specific updates here as the plugin evolves

    // Example for future versions:
    // if (version_compare($from_version, '1.1.0', '<')) {
    //   $this->update_to_1_1_0();
    // }

    // For now, no specific updates needed
  }

  /**
   * Clean up temporary data
   */
  private static function cleanup_temporary_data() {
    // Clean up any transients or temporary data
    delete_transient('mtp_temporary_data');

    // Note: We don't delete user data on deactivation
    // Only clean up temporary/cache data
  }

  /**
   * Uninstall plugin (static method for uninstall hook)
   */
  public static function uninstall() {
    // This method should only be called from uninstall.php
    // Remove all plugin data if user wants to completely remove the plugin

    // Remove plugin options
    delete_option('mtp_plugin_version');
    delete_option('mtp_default_settings');

    // Remove all posts of our custom post type
    $posts = get_posts(array(
      'post_type' => 'mtp_table',
      'numberposts' => -1,
      'post_status' => 'any'
    ));

    foreach ($posts as $post) {
      wp_delete_post($post->ID, true);
    }

    // Remove all meta data associated with our post type
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_mtp_%'");

    // Clean up any remaining transients
    delete_transient('mtp_temporary_data');

    // Flush rewrite rules
    flush_rewrite_rules();
  }
}
