<?php
/**
 * Assets Manager Class
 *
 * @package MeinTurnierplan
 * @since   1.0.0
 * @version 1.2.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Assets Manager Class
 */
class MTRN_Assets {

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
    // Enqueue admin scripts and styles
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

    // Enqueue frontend scripts for iframe auto-resizing
    add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
  }

  /**
   * Enqueue admin scripts and styles
   */
  public function enqueue_admin_scripts($hook) {
    // Only load on our post type edit pages
    if ('post.php' == $hook || 'post-new.php' == $hook) {
      global $post;
      if ($post && ($post->post_type == 'mtrn_table' || $post->post_type == 'mtrn_match_list')) {
        // Enqueue WordPress color picker
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        // Enqueue main plugin styles for admin
        wp_enqueue_style(
          'mtrn-admin-styles',
          MTRN_PLUGIN_URL . 'assets/css/style.css',
          array('wp-color-picker'),
          MTRN_PLUGIN_VERSION
        );

        // Enqueue frontend embed styles so the admin preview matches the site
        wp_enqueue_style(
          'mtrn-frontend-styles',
          MTRN_PLUGIN_URL . 'assets/css/frontend.css',
          array(),
          MTRN_PLUGIN_VERSION
        );

        // Enqueue jQuery (already available in admin)
        wp_enqueue_script('jquery');

        // Enqueue custom admin scripts if needed
        wp_enqueue_script(
          'mtrn-admin-scripts',
          MTRN_PLUGIN_URL . 'assets/js/admin.js',
          array('jquery', 'wp-color-picker'),
          MTRN_PLUGIN_VERSION,
          true
        );

        // Enqueue frontend auto-resize script for admin preview
        wp_enqueue_script(
          'mtrn-frontend-scripts',
          MTRN_PLUGIN_URL . 'assets/js/frontend.js',
          array(),
          MTRN_PLUGIN_VERSION,
          true
        );

        // Localize script for AJAX
        wp_localize_script('mtrn-admin-scripts', 'mtrn_ajax', array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'preview_nonce' => wp_create_nonce('mtrn_preview_nonce')
        ));
      }
    }
  }

  /**
   * Enqueue frontend scripts and styles
   */
  public function enqueue_frontend_scripts() {
    // Enqueue frontend embed styles (e.g. the responsive embed wrapper). Admin
    // UI styles in style.css are intentionally not loaded on the frontend.
    wp_enqueue_style(
      'mtrn-frontend-styles',
      MTRN_PLUGIN_URL . 'assets/css/frontend.css',
      array(),
      MTRN_PLUGIN_VERSION
    );

    // The resize script is loaded unconditionally: embeds can come from
    // shortcodes, blocks, widgets, single post views, templates and page
    // builders, so sniffing the post content would miss cases and leave
    // embeds at the fallback height. The script is small and does nothing
    // on pages without an embed.
    wp_enqueue_script(
      'mtrn-frontend-scripts',
      MTRN_PLUGIN_URL . 'assets/js/frontend.js',
      array(),
      MTRN_PLUGIN_VERSION,
      true
    );
  }

  /**
   * Get plugin URL
   */
  public function get_plugin_url() {
    return MTRN_PLUGIN_URL;
  }

  /**
   * Get assets URL
   */
  public function get_assets_url() {
    return MTRN_PLUGIN_URL . 'assets/';
  }
}
