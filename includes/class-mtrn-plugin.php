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
  public $table_post_type;
  public $matches_post_type;
  public $table_shortcode;
  public $matches_shortcode;
  public $table_admin_meta_boxes;
  public $matches_admin_meta_boxes;
  public $table_renderer;
  public $matches_renderer;
  public $table_ajax_handler;
  public $matches_ajax_handler;
  public $assets;
  public $installer;
  public $table_gutenberg_block;
  public $matches_gutenberg_block;
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

    // Core Table classes
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-table-post-type.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-table-renderer.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-table-shortcode.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-table-admin-meta-boxes.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-table-ajax-handler.php';

    // Core Matches classes
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-matches-post-type.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-matches-renderer.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-matches-shortcode.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-matches-admin-meta-boxes.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-matches-ajax-handler.php';

    // Core classes
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-admin-utilities.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-assets.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-admin-notices.php';

    // Widget class
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-table-widget.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-matches-widget.php';

    // Gutenberg block class
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-table-block.php';
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-matches-block.php';

    // Single content filter
    include_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-single-content-filter.php';
  }

  /**
   * Initialize plugin components
   */
  public function init() {
    // Initialize components
    $this->installer = new MTRN_Installer();
    $this->table_renderer = new MTRN_Table_Renderer();
    $this->matches_renderer = new MTRN_Matches_Renderer();
    $this->table_post_type = new MTRN_Table_Post_Type();
    $this->matches_post_type = new MTRN_Matches_Post_Type();
    $this->table_shortcode = new MTRN_Table_Shortcode($this->table_renderer);
    $this->matches_shortcode = new MTRN_Matches_Shortcode($this->matches_renderer);
    $this->table_admin_meta_boxes = new MTRN_Admin_Table_Meta_Boxes($this->table_renderer);
    $this->matches_admin_meta_boxes = new MTRN_Admin_Matches_Meta_Boxes($this->matches_renderer);
    $this->table_ajax_handler = new MTRN_Table_Ajax_Handler($this->table_renderer);
    $this->matches_ajax_handler = new MTRN_Matches_Ajax_Handler($this->matches_renderer);
    $this->admin_notices = new MTRN_Admin_Notices();
    $this->assets = new MTRN_Assets();
    $this->table_gutenberg_block = new MTRN_Table_Gutenberg_Block($this->table_renderer);
    $this->matches_gutenberg_block = new MTRN_Matches_Gutenberg_Block($this->matches_renderer);
    $this->single_content_filter = new MTRN_Single_Content_Filter($this->table_renderer, $this->matches_renderer);

    // Initialize widgets
    add_action('widgets_init', function () {
      register_widget('MTRN_Table_Widget');
      register_widget('MTRN_Matches_Widget');
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
