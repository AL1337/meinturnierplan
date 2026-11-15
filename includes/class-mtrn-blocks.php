<?php
/**
 * Gutenberg blocks: tournament table and match list.
 *
 * Each block stores the ID of a saved table / match list post; everything else
 * comes from that post's settings.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Block registration, rendering and the editor's post list endpoint
 */
class MTRN_Blocks {

  const EDITOR_SCRIPT = 'mtrn-blocks';

  /** @var MTRN_Embed_Renderer */
  private $renderer;

  public function __construct(MTRN_Embed_Renderer $renderer) {
    $this->renderer = $renderer;

    add_action('init', array($this, 'register_blocks'));

    foreach (MTRN_Options::types() as $type => $config) {
      add_action('wp_ajax_' . $config['ajax']['list'], function () use ($type) {
        $this->list_posts_ajax($type);
      });
    }
  }

  /**
   * Register both blocks with their editor scripts
   */
  public function register_blocks() {
    if (!function_exists('register_block_type')) {
      return;
    }

    $localized = array();
    foreach (MTRN_Options::types() as $type => $config) {
      $localized[$type] = array(
        'name'        => $config['block'],
        'title'       => $config['block_title'],
        'description' => $config['block_description'],
        'selectLabel' => $config['block_select_label'],
        'ajaxUrl'     => admin_url('admin-ajax.php'),
        'action'      => $config['ajax']['list'],
        'nonce'       => wp_create_nonce($config['block_nonce'])
      );
    }

    // Both block.json files name this handle as their editorScript
    wp_register_script(
      self::EDITOR_SCRIPT,
      MTRN_PLUGIN_URL . 'assets/js/blocks.js',
      array('wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'),
      MTRN_PLUGIN_VERSION,
      true
    );
    wp_localize_script(self::EDITOR_SCRIPT, 'mtrnBlocks', $localized);

    foreach (MTRN_Options::types() as $type => $config) {
      register_block_type(MTRN_PLUGIN_PATH . $config['block_json'], array(
        'render_callback' => function ($attributes) use ($type) {
          return $this->render_block($type, $attributes);
        }
      ));
    }
  }

  /**
   * Render a block on the frontend
   *
   * @param string $type       'table' or 'matches'.
   * @param array  $attributes Block attributes; 'tableId' is the saved post.
   * @return string HTML
   */
  public function render_block($type, $attributes) {
    $post_id = isset($attributes['tableId']) ? (int) $attributes['tableId'] : 0;

    if ($post_id <= 0) {
      return '<div class="mtrn-block-placeholder">' . MTRN_Options::type($type)['select_placeholder'] . '</div>';
    }

    return $this->renderer->render($type, $post_id, array());
  }

  /**
   * AJAX: the published posts of a type, for the block's select control
   */
  public function list_posts_ajax($type) {
    $config = MTRN_Options::type($type);

    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), $config['block_nonce'])) {
      wp_die(esc_html__('Security check failed', 'meinturnierplan'));
    }

    $posts = get_posts(array(
      'post_type' => $config['post_type'],
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
    ));

    $options = array(array('value' => '', 'label' => $config['select_label']));
    foreach ($posts as $post) {
      $options[] = array('value' => $post->ID, 'label' => $post->post_title);
    }

    wp_send_json_success($options);
  }
}
