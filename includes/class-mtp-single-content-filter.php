<?php
/**
 * Single CPT Content Filter Class
 *
 * Automatically populates single CPT pages with their shortcode content
 *
 * @package MeinTurnierplan
 * @since   0.3.1
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Single CPT Content Filter Class
 */
class MTP_Single_Content_Filter {

  /**
   * Table renderer instance
   */
  private $table_renderer;

  /**
   * Matches renderer instance
   */
  private $matches_renderer;

  /**
   * Constructor
   *
   * @param object $table_renderer Table renderer instance
   * @param object $matches_renderer Matches renderer instance
   */
  public function __construct($table_renderer, $matches_renderer) {
    $this->table_renderer = $table_renderer;
    $this->matches_renderer = $matches_renderer;
    $this->init();
  }

  /**
   * Initialize content filter
   */
  public function init() {
    add_filter('the_content', array($this, 'populate_single_cpt_content'));
  }

  /**
   * Populate single CPT page content with shortcode output
   *
   * @param string $content The post content
   * @return string Modified content
   */
  public function populate_single_cpt_content($content) {
    // Only run on single CPT pages in the main query
    if (!is_singular(array('mtp_table', 'mtp_match_list')) || !in_the_loop() || !is_main_query()) {
      return $content;
    }

    global $post;
    $post_id = $post->ID;
    $post_type = get_post_type($post_id);

    // Get the tournament ID from post meta
    $tournament_id = get_post_meta($post_id, '_mtp_tournament_id', true);

    // If no tournament ID, return original content
    if (empty($tournament_id)) {
      return $content;
    }

    // Get shortcode attributes from post meta
    $shortcode_content = '';

    if ($post_type === 'mtp_table') {
      $shortcode_content = $this->generate_table_content($post_id, $tournament_id);
    } elseif ($post_type === 'mtp_match_list') {
      $shortcode_content = $this->generate_matches_content($post_id, $tournament_id);
    }

    // Prepend shortcode content to the original content (if any)
    return $shortcode_content . $content;
  }

  /**
   * Generate table content
   *
   * @param int $post_id WordPress post ID
   * @param string $tournament_id External tournament table ID
   * @return string HTML content
   */
  private function generate_table_content($post_id, $tournament_id) {
    // Get all saved shortcode attributes from post meta
    // Map meta field names to shortcode attribute names
    $atts = array(
      'id' => $tournament_id,
      'post_id' => $post_id,
      'lang' => get_post_meta($post_id, '_mtp_language', true) ?: 'en',
      'group' => get_post_meta($post_id, '_mtp_group', true) ?: '',
      's-size' => get_post_meta($post_id, '_mtp_font_size', true) ?: '9',
      's-sizeheader' => get_post_meta($post_id, '_mtp_header_font_size', true) ?: '10',
      's-color' => get_post_meta($post_id, '_mtp_text_color', true) ?: '000000',
      's-maincolor' => get_post_meta($post_id, '_mtp_main_color', true) ?: '173f75',
      's-padding' => get_post_meta($post_id, '_mtp_table_padding', true) ?: '2',
      's-innerpadding' => get_post_meta($post_id, '_mtp_inner_padding', true) ?: '5',
      's-bgcolor' => $this->get_color_with_opacity($post_id, '_mtp_bg_color', '_mtp_bg_opacity', '00000000'),
      's-logosize' => get_post_meta($post_id, '_mtp_logo_size', true) ?: '20',
      's-bcolor' => get_post_meta($post_id, '_mtp_border_color', true) ?: 'bbbbbb',
      's-bsizeh' => get_post_meta($post_id, '_mtp_bsizeh', true) ?: '1',
      's-bsizev' => get_post_meta($post_id, '_mtp_bsizev', true) ?: '1',
      's-bsizeoh' => get_post_meta($post_id, '_mtp_bsizeoh', true) ?: '1',
      's-bsizeov' => get_post_meta($post_id, '_mtp_bsizeov', true) ?: '1',
      's-bbcolor' => get_post_meta($post_id, '_mtp_head_bottom_border_color', true) ?: 'bbbbbb',
      's-bbsize' => get_post_meta($post_id, '_mtp_bbsize', true) ?: '2',
      's-bgeven' => $this->get_color_with_opacity($post_id, '_mtp_even_bg_color', '_mtp_even_bg_opacity', 'f0f8ffb0'),
      's-bgodd' => $this->get_color_with_opacity($post_id, '_mtp_odd_bg_color', '_mtp_odd_bg_opacity', 'ffffffb0'),
      's-bgover' => $this->get_color_with_opacity($post_id, '_mtp_hover_bg_color', '_mtp_hover_bg_opacity', 'eeeeffb0'),
      's-bghead' => $this->get_color_with_opacity($post_id, '_mtp_head_bg_color', '_mtp_head_bg_opacity', 'eeeeffff'),
      'width' => get_post_meta($post_id, '_mtp_width', true) ?: '',
      'height' => get_post_meta($post_id, '_mtp_height', true) ?: '',
      'sw' => get_post_meta($post_id, '_mtp_suppress_wins', true) ?: '0',
      'sl' => get_post_meta($post_id, '_mtp_suppress_logos', true) ?: '0',
      'sn' => get_post_meta($post_id, '_mtp_suppress_num_matches', true) ?: '0',
      'bm' => get_post_meta($post_id, '_mtp_projector_presentation', true) ?: '0',
      'nav' => get_post_meta($post_id, '_mtp_navigation_for_groups', true) ?: '0',
      'setlang' => get_post_meta($post_id, '_mtp_language', true) ?: 'en'
    );

    return $this->table_renderer->render_table_html($post_id, $atts);
  }

  /**
   * Generate matches content
   *
   * @param int $post_id WordPress post ID
   * @param string $tournament_id External tournament match list ID
   * @return string HTML content
   */
  private function generate_matches_content($post_id, $tournament_id) {
    // Get all saved shortcode attributes from post meta
    // Map meta field names to shortcode attribute names
    $atts = array(
      'id' => $tournament_id,
      'post_id' => $post_id,
      'lang' => get_post_meta($post_id, '_mtp_language', true) ?: 'en',
      'group' => get_post_meta($post_id, '_mtp_group', true) ?: '',
      's-size' => get_post_meta($post_id, '_mtp_font_size', true) ?: '9',
      's-sizeheader' => get_post_meta($post_id, '_mtp_header_font_size', true) ?: '10',
      's-color' => get_post_meta($post_id, '_mtp_text_color', true) ?: '000000',
      's-maincolor' => get_post_meta($post_id, '_mtp_main_color', true) ?: '173f75',
      's-padding' => get_post_meta($post_id, '_mtp_table_padding', true) ?: '2',
      's-innerpadding' => get_post_meta($post_id, '_mtp_inner_padding', true) ?: '5',
      's-bgcolor' => $this->get_color_with_opacity($post_id, '_mtp_bg_color', '_mtp_bg_opacity', '00000000'),
      's-logosize' => '20', // Not saved in matches, use default
      's-bcolor' => get_post_meta($post_id, '_mtp_border_color', true) ?: 'bbbbbb',
      's-bsizeh' => get_post_meta($post_id, '_mtp_bsizeh', true) ?: '1',
      's-bsizev' => get_post_meta($post_id, '_mtp_bsizev', true) ?: '1',
      's-bsizeoh' => get_post_meta($post_id, '_mtp_bsizeoh', true) ?: '1',
      's-bsizeov' => get_post_meta($post_id, '_mtp_bsizeov', true) ?: '1',
      's-ehrsize' => get_post_meta($post_id, '_mtp_ehrsize', true) ?: '10',
      's-ehrtop' => get_post_meta($post_id, '_mtp_ehrtop', true) ?: '9',
      's-ehrbottom' => get_post_meta($post_id, '_mtp_ehrbottom', true) ?: '3',
      's-bbcolor' => get_post_meta($post_id, '_mtp_head_bottom_border_color', true) ?: 'bbbbbb',
      's-bbsize' => get_post_meta($post_id, '_mtp_bbsize', true) ?: '2',
      's-bgeven' => $this->get_color_with_opacity($post_id, '_mtp_even_bg_color', '_mtp_even_bg_opacity', 'f0f8ffb0'),
      's-bgodd' => $this->get_color_with_opacity($post_id, '_mtp_odd_bg_color', '_mtp_odd_bg_opacity', 'ffffffb0'),
      's-bgover' => $this->get_color_with_opacity($post_id, '_mtp_hover_bg_color', '_mtp_hover_bg_opacity', 'eeeeffb0'),
      's-bghead' => $this->get_color_with_opacity($post_id, '_mtp_head_bg_color', '_mtp_head_bg_opacity', 'eeeeffff'),
      'width' => get_post_meta($post_id, '_mtp_width', true) ?: '',
      'height' => get_post_meta($post_id, '_mtp_height', true) ?: '',
      'bm' => get_post_meta($post_id, '_mtp_projector_presentation', true) ?: '0',
      'si' => get_post_meta($post_id, '_mtp_si', true) ?: '0',
      'sf' => get_post_meta($post_id, '_mtp_sf', true) ?: '0',
      'st' => get_post_meta($post_id, '_mtp_st', true) ?: '0',
      'sg' => get_post_meta($post_id, '_mtp_sg', true) ?: '0',
      'sr' => get_post_meta($post_id, '_mtp_sr', true) ?: '0',
      'se' => get_post_meta($post_id, '_mtp_se', true) ?: '0',
      'sp' => get_post_meta($post_id, '_mtp_sp', true) ?: '0',
      'sh' => get_post_meta($post_id, '_mtp_sh', true) ?: '0',
      'gamenumbers' => get_post_meta($post_id, '_mtp_match_number', true) ?: '',
      'setlang' => get_post_meta($post_id, '_mtp_language', true) ?: 'en'
    );

    return $this->matches_renderer->render_matches_html($post_id, $atts);
  }

  /**
   * Combine color and opacity into 8-character hex format
   *
   * @param int $post_id Post ID
   * @param string $color_meta_key Color meta key
   * @param string $opacity_meta_key Opacity meta key
   * @param string $default Default value
   * @return string 8-character hex color with opacity
   */
  private function get_color_with_opacity($post_id, $color_meta_key, $opacity_meta_key, $default) {
    $color = get_post_meta($post_id, $color_meta_key, true);
    $opacity = get_post_meta($post_id, $opacity_meta_key, true);

    if (empty($color)) {
      return $default;
    }

    // Remove # if present
    $color = ltrim($color, '#');

    // Convert opacity percentage to hex (0-100 to 00-ff)
    if ($opacity !== '' && $opacity !== false) {
      $opacity_hex = str_pad(dechex(round($opacity * 2.55)), 2, '0', STR_PAD_LEFT);
      return $color . $opacity_hex;
    }

    return $color . 'ff'; // Default to full opacity
  }
}
