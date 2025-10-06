<?php
/**
 * Main Plugin Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Main Plugin Class
 */
class MTP_Plugin {

  /**
   * The single instance of the class
   */
  protected static $_instance = null;

  /**
   * Plugin components
   */
  public $post_type;
  public $shortcode;
  public $admin_meta_boxes;
  public $table_renderer;
  public $ajax_handler;
  public $assets;
  public $installer;
  public $gutenberg_block;

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
    $this->init();
  }

  /**
   * Hook into actions and filters
   */
  private function init_hooks() {
    add_action('init', array($this, 'init'), 0);
    add_action('plugins_loaded', array($this, 'load_textdomain'));

    // Activation and deactivation hooks
    register_activation_hook(MTP_PLUGIN_FILE, array($this, 'activate'));
    register_deactivation_hook(MTP_PLUGIN_FILE, array($this, 'deactivate'));
  }

  /**
   * Include required core files
   */
  public function includes() {
    // Core classes
    include_once MTP_PLUGIN_PATH . 'includes/class-mtp-installer.php';
    include_once MTP_PLUGIN_PATH . 'includes/class-mtp-post-type.php';
    include_once MTP_PLUGIN_PATH . 'includes/class-mtp-shortcode.php';
    include_once MTP_PLUGIN_PATH . 'includes/class-mtp-admin-meta-boxes.php';
    include_once MTP_PLUGIN_PATH . 'includes/class-mtp-table-renderer.php';
    include_once MTP_PLUGIN_PATH . 'includes/class-mtp-ajax-handler.php';
    include_once MTP_PLUGIN_PATH . 'includes/class-mtp-assets.php';

    // Widget class
    include_once MTP_PLUGIN_PATH . 'includes/class-mtp-table-widget.php';

    // Gutenberg block class
    include_once MTP_PLUGIN_PATH . 'includes/class-mtp-gutenberg-block.php';
  }

  /**
   * Initialize plugin components
   */
  public function init() {
    // Initialize components
    $this->installer = new MTP_Installer();
    $this->table_renderer = new MTP_Table_Renderer();
    $this->post_type = new MTP_Post_Type();
    $this->shortcode = new MTP_Shortcode($this->table_renderer);
    $this->admin_meta_boxes = new MTP_Admin_Meta_Boxes($this->table_renderer);
    $this->ajax_handler = new MTP_Ajax_Handler($this->table_renderer);
    $this->assets = new MTP_Assets();
    $this->gutenberg_block = new MTP_Gutenberg_Block($this->table_renderer);

    // Initialize widget
    add_action('widgets_init', function() {
      register_widget('MTP_Table_Widget');
    });
  }

  /**
   * Load plugin text domain
   */
  public function load_textdomain() {
    load_plugin_textdomain(
      'meinturnierplan',
      false,
      dirname(plugin_basename(MTP_PLUGIN_FILE)) . '/languages'
    );
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
    return untrailingslashit(plugins_url('/', MTP_PLUGIN_FILE));
  }

  /**
   * Get the plugin path
   */
  public function plugin_path() {
    return untrailingslashit(plugin_dir_path(MTP_PLUGIN_FILE));
  }
}
