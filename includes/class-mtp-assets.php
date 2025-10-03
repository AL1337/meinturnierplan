<?php
/**
 * Assets Manager Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Assets Manager Class
 */
class MTP_Assets {

  /**
   * Constructor
   */
  public function __construct() {
    $this->init();
  }

  /**
   * Initialize assets
   */
  public function init() {
    add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
  }

  /**
   * Enqueue frontend styles
   */
  public function enqueue_frontend_styles() {
    wp_enqueue_style(
      'mtp-tournament-table',
      MTP_PLUGIN_URL . 'assets/css/style.css',
      array(),
      MTP_PLUGIN_VERSION
    );
  }

  /**
   * Enqueue admin scripts and styles
   */
  public function enqueue_admin_scripts($hook) {
    // Only load on our post type edit pages
    if ('post.php' == $hook || 'post-new.php' == $hook) {
      global $post;
      if ($post && ($post->post_type == 'mtp_table' || $post->post_type == 'mtp_matches')) {
        // Enqueue WordPress color picker
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        // Enqueue main plugin styles for admin
        wp_enqueue_style(
          'mtp-admin-styles',
          MTP_PLUGIN_URL . 'assets/css/style.css',
          array('wp-color-picker'),
          MTP_PLUGIN_VERSION
        );

        // Enqueue jQuery (already available in admin)
        wp_enqueue_script('jquery');

        // Enqueue custom admin scripts if needed
        wp_enqueue_script(
          'mtp-admin-scripts',
          MTP_PLUGIN_URL . 'assets/js/admin.js',
          array('jquery', 'wp-color-picker'),
          MTP_PLUGIN_VERSION,
          true
        );

        // Localize script for AJAX
        wp_localize_script('mtp-admin-scripts', 'mtp_ajax', array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'preview_nonce' => wp_create_nonce('mtp_preview_nonce')
        ));
      }
    }
  }

  /**
   * Get plugin URL
   */
  public function get_plugin_url() {
    return MTP_PLUGIN_URL;
  }

  /**
   * Get assets URL
   */
  public function get_assets_url() {
    return MTP_PLUGIN_URL . 'assets/';
  }
}
