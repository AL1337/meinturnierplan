<?php
/**
 * Matches Gutenberg Block Handler Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Matches Gutenberg Block Handler Class
 */
class MTP_Matches_Gutenberg_Block {

  /**
   * Matches renderer instance
   */
  private $matches_renderer;

  /**
   * Constructor
   */
  public function __construct($matches_renderer) {
    $this->matches_renderer = $matches_renderer;
    $this->init();
  }

  /**
   * Initialize block
   */
  public function init() {
    add_action('init', array($this, 'register_block'));
    add_action('wp_ajax_mtp_get_matches', array($this, 'get_matches_ajax'));
    add_action('wp_ajax_nopriv_mtp_get_matches', array($this, 'get_matches_ajax'));
  }

  /**
   * Register the block
   */
  public function register_block() {
    // Only register if Gutenberg is available
    if (!function_exists('register_block_type')) {
      return;
    }

    wp_register_script(
      'mtp-matches-block',
      MTP_PLUGIN_URL . 'assets/js/matches-block.js',
      array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-data', 'wp-api-fetch'),
      MTP_PLUGIN_VERSION,
      true
    );

    wp_localize_script('mtp-matches-block', 'mtpMatchesBlock', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('mtp_matches_block_nonce')
    ));

    register_block_type(MTP_PLUGIN_PATH . 'blocks/matches/block.json', array(
      'render_callback' => array($this, 'render_block')
    ));
  }

  /**
   * Render the block on the frontend
   */
  public function render_block($attributes) {
    $matches_id = isset($attributes['matchesId']) ? $attributes['matchesId'] : '';

    if (empty($matches_id)) {
      return '<div class="mtp-block-placeholder">' . __('Please select Tournament Matches.', 'meinturnierplan-wp') . '</div>';
    }

    // Get saved width and height from post meta
    $width = get_post_meta($matches_id, '_mtp_matches_width', true);
    $height = get_post_meta($matches_id, '_mtp_matches_height', true);

    // Prepare shortcode attributes
    $shortcode_atts = array('post_id' => $matches_id);

    // Add width and height if they exist
    if (!empty($width)) {
      $shortcode_atts['width'] = $width;
    }
    if (!empty($height)) {
      $shortcode_atts['height'] = $height;
    }

    // Use the existing shortcode functionality
    $shortcode = new MTP_Matches_Shortcode($this->matches_renderer);
    return $shortcode->shortcode_callback($shortcode_atts);
  }

  /**
   * AJAX handler to get matches
   */
  public function get_matches_ajax() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mtp_matches_block_nonce')) {
      wp_die('Security check failed');
    }

    // Get all published matches
    $matches = get_posts(array(
      'post_type' => 'mtp_matches',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
    ));

    $options = array(
      array(
        'label' => __('-- Select Matches --', 'meinturnierplan-wp'),
        'value' => ''
      )
    );

    foreach ($matches as $match) {
      $options[] = array(
        'label' => $match->post_title,
        'value' => $match->ID
      );
    }

    wp_send_json_success($options);
  }
}
