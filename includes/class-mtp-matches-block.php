<?php
/**
 * Matches Gutenberg Block Handler Class
 *
 * @package MeinTurnierplan
 * @since 0.1.0
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
   * Initialize Gutenberg block
   */
  public function init() {
    add_action('init', array($this, 'register_block'));
    add_action('wp_ajax_mtp_get_matches', array($this, 'get_tables_ajax'));
    add_action('wp_ajax_nopriv_mtp_get_matches', array($this, 'get_tables_ajax'));
  }

  /**
   * Register the Matches Gutenberg block
   */
  public function register_block() {
    // Only register if Gutenberg is available
    if (!function_exists('register_block_type')) {
      return;
    }

    wp_register_script(
      'mtp-tournament-matches-block',
      MTP_PLUGIN_URL . 'assets/js/tournament-matches-block.js',
      array('wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-api-fetch'),
      MTP_PLUGIN_VERSION,
      true
    );

    wp_localize_script('mtp-tournament-matches-block', 'mtpMatchesBlock', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('mtp_matches_block_nonce')
    ));

    register_block_type(MTP_PLUGIN_PATH . 'blocks/tournament-matches/block.json', array(
      'render_callback' => array($this, 'render_block')
    ));
  }

  /**
   * Render the block on the frontend
   */
  public function render_block($attributes) {
    $table_id = isset($attributes['tableId']) ? $attributes['tableId'] : '';

    if (empty($table_id)) {
      return '<div class="mtp-block-placeholder">' . __('Please select a Matches Table.', 'meinturnierplan') . '</div>';
    }

    // Prepare shortcode attributes (width and height are now auto-determined)
    $shortcode_atts = array('post_id' => $table_id);

    // Load all styling parameters from post meta to ensure customizations are applied
    $shortcode_atts = array_merge($shortcode_atts, $this->get_styling_attributes_from_meta($table_id));

    // Load other configuration parameters from post meta
    $shortcode_atts = array_merge($shortcode_atts, $this->get_config_attributes_from_meta($table_id));

    // Use the existing shortcode functionality
    $shortcode = new MTP_Matches_Shortcode($this->matches_renderer);
    return $shortcode->shortcode_callback($shortcode_atts);
  }

  /**
   * Get styling attributes from post meta
   */
  private function get_styling_attributes_from_meta($table_id) {
    $attributes = array();

    // Define parameter mapping from meta keys to shortcode attribute names
    $param_mapping = array(
      '_mtp_font_size' => 's-size',
      '_mtp_header_font_size' => 's-sizeheader',
      '_mtp_text_color' => 's-color',
      '_mtp_main_color' => 's-maincolor',
      '_mtp_table_padding' => 's-padding',
      '_mtp_inner_padding' => 's-innerpadding',
      '_mtp_border_color' => 's-bcolor',
      '_mtp_bsizeh' => 's-bsizeh',
      '_mtp_bsizev' => 's-bsizev',
      '_mtp_bsizeoh' => 's-bsizeoh',
      '_mtp_bsizeov' => 's-bsizeov',
      '_mtp_ehrsize' => 's-ehrsize',
      '_mtp_ehrtop' => 's-ehrtop',
      '_mtp_ehrbottom' => 's-ehrbottom',
      '_mtp_head_bottom_border_color' => 's-bbcolor',
      '_mtp_bbsize' => 's-bbsize',
    );

    // Get simple color/styling values
    foreach ($param_mapping as $meta_key => $attr_name) {
      $value = get_post_meta($table_id, $meta_key, true);
      if (!empty($value)) {
        $attributes[$attr_name] = $value;
      }
    }

    // Handle color+opacity combinations
    $color_opacity_mapping = array(
      '_mtp_bg_color' => array('attr' => 's-bgcolor', 'opacity_meta' => '_mtp_bg_opacity'),
      '_mtp_even_bg_color' => array('attr' => 's-bgeven', 'opacity_meta' => '_mtp_even_bg_opacity'),
      '_mtp_odd_bg_color' => array('attr' => 's-bgodd', 'opacity_meta' => '_mtp_odd_bg_opacity'),
      '_mtp_hover_bg_color' => array('attr' => 's-bgover', 'opacity_meta' => '_mtp_hover_bg_opacity'),
      '_mtp_head_bg_color' => array('attr' => 's-bghead', 'opacity_meta' => '_mtp_head_bg_opacity'),
    );

    foreach ($color_opacity_mapping as $color_meta => $config) {
      $color = get_post_meta($table_id, $color_meta, true);
      $opacity = get_post_meta($table_id, $config['opacity_meta'], true);

      if (!empty($color)) {
        $combined_color = MTP_Admin_Utilities::combine_color_opacity($color, $opacity);
        $attributes[$config['attr']] = $combined_color;
      }
    }

    return $attributes;
  }

  /**
   * Get configuration attributes from post meta
   */
  private function get_config_attributes_from_meta($table_id) {
    $attributes = array();

    // Define boolean parameter mapping
    $boolean_params = array(
      '_mtp_projector_presentation' => 'bm',
      '_mtp_si' => 'si',
      '_mtp_sf' => 'sf',
      '_mtp_st' => 'st',
      '_mtp_sg' => 'sg',
      '_mtp_se' => 'se',
      '_mtp_sp' => 'sp',
      '_mtp_sh' => 'sh',
    );

    foreach ($boolean_params as $meta_key => $attr_name) {
      $value = get_post_meta($table_id, $meta_key, true);
      if ($value === '1') {
        $attributes[$attr_name] = '1';
      }
    }

    // Get language setting
    $language = get_post_meta($table_id, '_mtp_language', true);
    if (!empty($language)) {
      $attributes['lang'] = $language;
    }

    // Get group setting
    $group = get_post_meta($table_id, '_mtp_group', true);
    if (!empty($group)) {
      $attributes['group'] = $group;
    }

    return $attributes;
  }

  /**
   * AJAX handler to get matches
   */
  public function get_tables_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mtp_matches_block_nonce')) {
      wp_die(__('Security check failed', 'meinturnierplan'));
    }

    $matches = get_posts(array(
      'post_type' => 'mtp_match_list',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC'
    ));

    $options = array();
    $options[] = array(
      'value' => '',
      'label' => __('Select a Matches Table', 'meinturnierplan')
    );

    foreach ($matches as $match) {
      $options[] = array(
        'value' => $match->ID,
        'label' => $match->post_title
      );
    }

    wp_send_json_success($options);
  }
}
