<?php
/**
 * Plugin Installer Class
 *
 * @package MeinTurnierplan
 * @since   0.1.0
 * @version 1.2.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Plugin Installer Class
 */
class MTRN_Installer {

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
   * Register post types for activation (temporary)
   */
  private static function register_post_type_for_activation() {
    // Simple registration for activation - the full registration is handled by respective Post Type classes
    register_post_type('mtrn_table', array(
      'public' => true,
      'rewrite' => array('slug' => 'tournament-table'),
    ));

    register_post_type('mtrn_match_list', array(
      'public' => true,
      'rewrite' => array('slug' => 'tournament-match-list'),
    ));
  }

  /**
   * Set default plugin options
   */
  private static function set_default_options() {
    // Set plugin version
    if (!get_option('mtrn_plugin_version')) {
      add_option('mtrn_plugin_version', MTRN_PLUGIN_VERSION);
    }

    // Set default settings if needed
    if (!get_option('mtrn_default_settings')) {
      $default_settings = array(
        'default_width' => '300',
        'default_height' => '152',
        'default_font_size' => '9',
        'default_text_color' => '000000',
        'default_main_color' => '173f75',
      );
      add_option('mtrn_default_settings', $default_settings);
    }
  }

  /**
   * Maybe update database
   */
  private static function maybe_update_database() {
    $current_version = get_option('mtrn_plugin_version', '0.0.0');

    // If this is a new installation or upgrade, run updates
    if (version_compare($current_version, MTRN_PLUGIN_VERSION, '<')) {
      self::run_database_updates($current_version);
      update_option('mtrn_plugin_version', MTRN_PLUGIN_VERSION);
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
    // Clear cached tournament data fetched from the JSON API
    self::delete_cached_tournament_data();

    // Note: We don't delete user data on deactivation
    // Only clean up temporary/cache data
  }

  /**
   * Delete the cached tournament data (groups, teams, options) fetched from
   * the MeinTurnierplan JSON API. Transient names are mtrn_groups_{lang}_{id},
   * mtrn_teams_{lang}_{id} and mtrn_data_{lang}_{id}.
   */
  private static function delete_cached_tournament_data() {
    global $wpdb;

    foreach (array('mtrn_groups_', 'mtrn_teams_', 'mtrn_data_') as $prefix) {
      // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Transients are removed by prefix; there is no API for that
      $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_' . $prefix) . '%',
        $wpdb->esc_like('_transient_timeout_' . $prefix) . '%'
      ));
    }
  }

  /**
   * Uninstall plugin (static method for uninstall hook)
   */
  public static function uninstall() {
    // This method should only be called from uninstall.php
    // Remove all plugin data if user wants to completely remove the plugin

    // Remove plugin options
    delete_option('mtrn_plugin_version');
    delete_option('mtrn_default_settings');

    // Remove all posts of our custom post types
    $post_types = array('mtrn_table', 'mtrn_match_list');

    foreach ($post_types as $post_type) {
      $posts = get_posts(array(
        'post_type' => $post_type,
        'numberposts' => -1,
        'post_status' => array_keys(get_post_stati()) // 'any' would skip trashed posts
      ));

      foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
      }
    }

    // Remove all meta data associated with our post types
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query acceptable during uninstall for cleanup
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", $wpdb->esc_like('_mtrn_') . '%'));

    // Remove cached tournament data and the service notice flag
    self::delete_cached_tournament_data();
    delete_option('mtrn_service_notice_dismissed');

    // Flush rewrite rules
    flush_rewrite_rules();
  }
}
