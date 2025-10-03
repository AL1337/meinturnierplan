<?php
/**
 * Matches AJAX Handler Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Matches AJAX Handler Class
 */
class MTP_Matches_Ajax_Handler {

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
   * Initialize AJAX handlers
   */
  public function init() {
    add_action('wp_ajax_mtp_preview_matches', array($this, 'ajax_preview_matches'));
  }

  /**
   * AJAX handler for matches preview
   */
  public function ajax_preview_matches() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mtp_preview_matches')) {
      wp_die('Security check failed');
    }

    $post_id = absint($_POST['post_id']);
    $data = $this->sanitize_ajax_data($_POST);

    // Convert form data to shortcode attributes
    $atts = $this->convert_form_data_to_atts($data);

    // Render matches HTML
    $html = $this->matches_renderer->render_matches_html($post_id, $atts);

    wp_send_json_success($html);
  }

  /**
   * Sanitize AJAX data
   */
  private function sanitize_ajax_data($data) {
    $sanitized = array();

    // Define allowed fields
    $allowed_fields = array(
      'mtp_matches_tournament_id', 'mtp_matches_width', 'mtp_matches_height',
      'mtp_matches_font_size', 'mtp_matches_header_font_size', 'mtp_matches_table_padding',
      'mtp_matches_inner_padding', 'mtp_matches_text_color', 'mtp_matches_main_color',
      'mtp_matches_bg_color', 'mtp_matches_bg_opacity', 'mtp_matches_border_color',
      'mtp_matches_head_bottom_border_color', 'mtp_matches_even_bg_color', 'mtp_matches_even_bg_opacity',
      'mtp_matches_odd_bg_color', 'mtp_matches_odd_bg_opacity', 'mtp_matches_hover_bg_color',
      'mtp_matches_hover_bg_opacity', 'mtp_matches_head_bg_color', 'mtp_matches_head_bg_opacity',
      'mtp_matches_bsizeh', 'mtp_matches_bsizev', 'mtp_matches_bsizeoh', 'mtp_matches_bsizeov',
      'mtp_matches_bbsize', 'mtp_matches_ehrsize', 'mtp_matches_ehrtop', 'mtp_matches_ehrbottom',
      'mtp_matches_wrap', 'mtp_matches_language'
    );

    foreach ($allowed_fields as $field) {
      if (isset($data[$field])) {
        $sanitized[$field] = sanitize_text_field($data[$field]);
      }
    }

    return $sanitized;
  }

  /**
   * Convert form data to shortcode attributes
   */
  private function convert_form_data_to_atts($data) {
    // Combine colors with opacity
    $combined_bg_color = $this->combine_color_opacity(
      $data['mtp_matches_bg_color'] ?? '000000',
      $data['mtp_matches_bg_opacity'] ?? '0'
    );
    $combined_even_bg_color = $this->combine_color_opacity(
      $data['mtp_matches_even_bg_color'] ?? 'f0f8ff',
      $data['mtp_matches_even_bg_opacity'] ?? '69'
    );
    $combined_odd_bg_color = $this->combine_color_opacity(
      $data['mtp_matches_odd_bg_color'] ?? 'ffffff',
      $data['mtp_matches_odd_bg_opacity'] ?? '69'
    );
    $combined_hover_bg_color = $this->combine_color_opacity(
      $data['mtp_matches_hover_bg_color'] ?? 'eeeeff',
      $data['mtp_matches_hover_bg_opacity'] ?? '69'
    );
    $combined_head_bg_color = $this->combine_color_opacity(
      $data['mtp_matches_head_bg_color'] ?? 'eeeeff',
      $data['mtp_matches_head_bg_opacity'] ?? '100'
    );

    return array(
      'id' => $data['mtp_matches_tournament_id'] ?? '',
      'lang' => $data['mtp_matches_language'] ?? 'en',
      's-size' => $data['mtp_matches_font_size'] ?? '9',
      's-sizeheader' => $data['mtp_matches_header_font_size'] ?? '10',
      's-color' => ltrim($data['mtp_matches_text_color'] ?? '000000', '#'),
      's-maincolor' => ltrim($data['mtp_matches_main_color'] ?? '173f75', '#'),
      's-padding' => $data['mtp_matches_table_padding'] ?? '2',
      's-innerpadding' => $data['mtp_matches_inner_padding'] ?? '5',
      's-bgcolor' => $combined_bg_color,
      's-bcolor' => ltrim($data['mtp_matches_border_color'] ?? 'bbbbbb', '#'),
      's-bsizeh' => $data['mtp_matches_bsizeh'] ?? '1',
      's-bsizev' => $data['mtp_matches_bsizev'] ?? '1',
      's-bsizeoh' => $data['mtp_matches_bsizeoh'] ?? '1',
      's-bsizeov' => $data['mtp_matches_bsizeov'] ?? '1',
      's-bbcolor' => ltrim($data['mtp_matches_head_bottom_border_color'] ?? 'bbbbbb', '#'),
      's-bbsize' => $data['mtp_matches_bbsize'] ?? '2',
      's-bgeven' => $combined_even_bg_color,
      's-bgodd' => $combined_odd_bg_color,
      's-bgover' => $combined_hover_bg_color,
      's-bghead' => $combined_head_bg_color,
      's-ehrsize' => $data['mtp_matches_ehrsize'] ?? '10',
      's-ehrtop' => $data['mtp_matches_ehrtop'] ?? '9',
      's-ehrbottom' => $data['mtp_matches_ehrbottom'] ?? '3',
      's-wrap' => $data['mtp_matches_wrap'] ?? 'false',
      'width' => $data['mtp_matches_width'] ?? '588',
      'height' => $data['mtp_matches_height'] ?? '3784',
    );
  }

  /**
   * Combine color and opacity
   */
  private function combine_color_opacity($hex_color, $opacity_percent) {
    // Remove # if present
    $hex_color = ltrim($hex_color, '#');

    // Convert opacity percentage to hex
    $opacity_hex = str_pad(dechex(round(($opacity_percent / 100) * 255)), 2, '0', STR_PAD_LEFT);

    return $hex_color . $opacity_hex;
  }
}
