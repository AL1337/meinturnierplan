<?php
/**
 * Main Plugin Class
 *
 * @package MeinTurnierplan
 * @since   0.1.0
 * @version 1.3.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Main Plugin Class
 */
class MTRN_Plugin {

  /**
   * The single instance of the class
   */
  protected static $_instance = null;

  /**
   * Plugin components
   */
  public $renderer;
  public $table_post_type;
  public $matches_post_type;
  public $shortcodes;
  public $blocks;
  public $meta_boxes = array();
  public $ajax_handler;
  public $assets;
  public $installer;
  public $single_content_filter;
  public $admin_notices;

  /**
   * Main Plugin Instance
   */
  public static function instance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Constructor
   */
  public function __construct() {
    $this->init_hooks();
    $this->includes();
  }

  /**
   * Hook into actions and filters
   */
  private function init_hooks() {
    add_action('init', array($this, 'init'), 0);

    // Activation and deactivation hooks
    register_activation_hook(MTRN_PLUGIN_FILE, array($this, 'activate'));
    register_deactivation_hook(MTRN_PLUGIN_FILE, array($this, 'deactivate'));
  }

  /**
   * Include required core files
   */
  public function includes() {
    // Option registry and the shared embed renderer
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-admin-utilities.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-options.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-embed-renderer.php';

    // Post types
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-table-post-type.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-matches-post-type.php';

    // Output channels
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-shortcodes.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-blocks.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-embed-widget.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-single-content-filter.php';

    // Admin
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-admin-meta-boxes.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-ajax-handler.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-assets.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-admin-notices.php';
  }

  /**
   * Initialize plugin components
   */
  public function init() {
    $this->installer = new MTRN_Installer();
    $this->renderer = new MTRN_Embed_Renderer();
    $this->table_post_type = new MTRN_Table_Post_Type();
    $this->matches_post_type = new MTRN_Matches_Post_Type();
    $this->shortcodes = new MTRN_Shortcodes($this->renderer);
    $this->blocks = new MTRN_Blocks($this->renderer);
    $this->single_content_filter = new MTRN_Single_Content_Filter($this->renderer);
    foreach (array_keys(MTRN_Options::types()) as $type) {
      $this->meta_boxes[$type] = new MTRN_Admin_Meta_Boxes($type, $this->renderer);
    }
    $this->ajax_handler = new MTRN_Ajax_Handler($this->renderer);
    $this->admin_notices = new MTRN_Admin_Notices();
    $this->assets = new MTRN_Assets();

    // One widget per embed type, each with its own widget id
    add_action('widgets_init', function () {
      foreach (array_keys(MTRN_Options::types()) as $type) {
        register_widget(new MTRN_Embed_Widget($type, $this->renderer));
      }
    });
  }

  /**
   * Plugin activation
   */
  public function activate() {
    if (!is_null($this->installer)) {
      $this->installer->activate();
    }
  }

  /**
   * Plugin deactivation
   */
  public function deactivate() {
    if (!is_null($this->installer)) {
      $this->installer->deactivate();
    }
  }

  /**
   * Get the plugin URL
   */
  public function plugin_url() {
    return untrailingslashit(plugins_url('/', MTRN_PLUGIN_FILE));
  }

  /**
   * Get the plugin path
   */
  public function plugin_path() {
    return untrailingslashit(plugin_dir_path(MTRN_PLUGIN_FILE));
  }
}
