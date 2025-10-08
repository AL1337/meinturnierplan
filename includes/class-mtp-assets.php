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
      if ($post && $post->post_type == 'mtp_table') {
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

        // Enqueue frontend auto-resize script for admin preview
        wp_enqueue_script(
          'mtp-frontend-scripts',
          MTP_PLUGIN_URL . 'assets/js/frontend.js',
          array(),
          MTP_PLUGIN_VERSION,
          true
        );

        // Localize script for AJAX
        wp_localize_script('mtp-admin-scripts', 'mtp_ajax', array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'preview_nonce' => wp_create_nonce('mtp_preview_nonce')
        ));

        // Add debug info for admin
        wp_add_inline_script('mtp-frontend-scripts', 'console.log("[MTP] Frontend script loaded in admin for preview functionality");', 'before');
      }
    }
  }

  /**
   * Enqueue frontend scripts and styles
   */
  public function enqueue_frontend_scripts() {
    // Debug: Always enqueue for testing (remove this later)
    wp_enqueue_script(
      'mtp-frontend-scripts',
      MTP_PLUGIN_URL . 'assets/js/frontend.js',
      array(),
      MTP_PLUGIN_VERSION,
      true
    );

    // Also check if we have tournament tables on the page
    if ($this->page_has_tournament_tables()) {
      // Add debug info to the page
      wp_add_inline_script('mtp-frontend-scripts', 'console.log("[MTP] Tournament tables detected on page");', 'before');
    } else {
      wp_add_inline_script('mtp-frontend-scripts', 'console.log("[MTP] No tournament tables detected on page");', 'before');
    }
  }

  /**
   * Check if current page has tournament tables
   */
  private function page_has_tournament_tables() {
    global $post;

    // Check if we're on a page/post with tournament table shortcodes or blocks
    if ($post && ($post->post_content)) {
      // Check for shortcodes
      if (has_shortcode($post->post_content, 'mtp_table')) {
        return true;
      }

      // Check for Gutenberg blocks
      if (has_block('meinturnierplan/tournament-table', $post)) {
        return true;
      }

      // Check if this is a tournament table post type
      if ($post->post_type === 'mtp_table') {
        return true;
      }
    }

    // Also check if any widgets are displaying tournament tables
    if (is_active_widget(false, false, 'mtp_table_widget')) {
      return true;
    }

    return false;
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
