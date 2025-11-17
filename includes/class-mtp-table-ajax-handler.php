<?php
/**
 * AJAX Table Handler Class
 *
 * @package MeinTurnierplan
 * @since   0.1.0
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * AJAX Table Handler Class
 */
class MTP_Table_Ajax_Handler {

  /**
   * Table renderer instance
   */
  private $table_renderer;

  /**
   * Constructor
   */
  public function __construct($table_renderer) {
    $this->table_renderer = $table_renderer;
    $this->init();
  }

  /**
   * Initialize AJAX handlers
   */
  public function init() {
    add_action('wp_ajax_mtp_preview_table', array($this, 'ajax_preview_table'));
    add_action('wp_ajax_mtp_get_groups', array($this, 'ajax_get_groups'));
    add_action('wp_ajax_mtp_refresh_groups', array($this, 'ajax_refresh_groups'));
  }

  /**
   * AJAX handler for table preview (existing one for admin)
   */
  public function ajax_preview_table() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mtp_preview_nonce')) {
      wp_die('Security check failed');
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $data = $this->sanitize_ajax_data($_POST);

    // Create attributes for rendering
    $atts = array(
      'id' => $data['tournament_id'],
      'width' => $data['width'] ? $data['width'] : '300',
      'height' => $data['height'] ? $data['height'] : '152',
      's-size' => $data['font_size'] ? $data['font_size'] : '9',
      's-sizeheader' => $data['header_font_size'] ? $data['header_font_size'] : '10',
      's-padding' => $data['table_padding'] ? $data['table_padding'] : '2',
      's-innerpadding' => $data['inner_padding'] ? $data['inner_padding'] : '5',
      's-color' => $data['text_color'] ? $data['text_color'] : '000000',
      's-maincolor' => $data['main_color'] ? $data['main_color'] : '173f75',
      's-bgcolor' => $data['bg_color'] ? $data['bg_color'] : '00000000',
      's-bcolor' => $data['border_color'] ? $data['border_color'] : 'bbbbbb',
      's-bbcolor' => $data['head_bottom_border_color'] ? $data['head_bottom_border_color'] : 'bbbbbb',
      's-bgeven' => $data['even_bg_color'] ? $data['even_bg_color'] : 'f0f8ffb0',
      's-bgodd' => $data['odd_bg_color'] ? $data['odd_bg_color'] : 'ffffffb0',
      's-bgover' => $data['hover_bg_color'] ? $data['hover_bg_color'] : 'eeeeffb0',
      's-bghead' => $data['head_bg_color'] ? $data['head_bg_color'] : 'eeeeffff',
      's-logosize' => $data['logo_size'] ? $data['logo_size'] : '20',
      's-bsizeh' => $data['bsizeh'] ? $data['bsizeh'] : '1',
      's-bsizev' => $data['bsizev'] ? $data['bsizev'] : '1',
      's-bsizeoh' => $data['bsizeoh'] ? $data['bsizeoh'] : '1',
      's-bsizeov' => $data['bsizeov'] ? $data['bsizeov'] : '1',
      's-bbsize' => $data['bbsize'] ? $data['bbsize'] : '2',
      'setlang' => $data['language'] ? $data['language'] : 'en'
    );

    // Add group parameter if specified
    if (!empty($data['group'])) {
      $atts['group'] = $data['group'];
    }

    // Add sw parameter (always send 0 or 1 to prevent post meta fallback)
    if (!empty($data['suppress_wins']) && $data['suppress_wins'] === '1') {
      $atts['sw'] = '1';
    } else {
      $atts['sw'] = '0';
    }

    // Add sl parameter (always send 0 or 1 to prevent post meta fallback)
    if (!empty($data['suppress_logos']) && $data['suppress_logos'] === '1') {
      $atts['sl'] = '1';
    } else {
      $atts['sl'] = '0';
    }

    // Add sn parameter (always send 0 or 1 to prevent post meta fallback)
    if (!empty($data['suppress_num_matches']) && $data['suppress_num_matches'] === '1') {
      $atts['sn'] = '1';
    } else {
      $atts['sn'] = '0';
    }

    // Add bm parameter (always send 0 or 1 to prevent post meta fallback)
    if (!empty($data['projector_presentation']) && $data['projector_presentation'] === '1') {
      $atts['bm'] = '1';
    } else {
      $atts['bm'] = '0';
    }

    // Add nav parameter (always send 0 or 1 to prevent post meta fallback)
    if (!empty($data['navigation_for_groups']) && $data['navigation_for_groups'] === '1') {
      $atts['nav'] = '1';
    } else {
      $atts['nav'] = '0';
    }

    $html = $this->table_renderer->render_table_html($post_id, $atts);

    wp_send_json_success($html);
  }

  /**
   * AJAX handler for fetching tournament groups
   */
  public function ajax_get_groups() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mtp_preview_nonce')) {
      wp_die('Security check failed');
    }

    $tournament_id = isset($_POST['tournament_id']) ? sanitize_text_field(wp_unslash($_POST['tournament_id'])) : '';
    $force_refresh = isset($_POST['force_refresh']) ? (bool)$_POST['force_refresh'] : false;

    if (empty($tournament_id)) {
      wp_send_json_success(array('groups' => array(), 'hasFinalRound' => false));
      return;
    }

    // Fetch groups from external API (with caching)
    $groups_data = MTP_Admin_Utilities::fetch_tournament_groups($tournament_id, $force_refresh);

    wp_send_json_success($groups_data);
  }

  /**
   * AJAX handler for refreshing tournament groups (force refresh)
   */
  public function ajax_refresh_groups() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mtp_preview_nonce')) {
      wp_die('Security check failed');
    }

    $tournament_id = isset($_POST['tournament_id']) ? sanitize_text_field(wp_unslash($_POST['tournament_id'])) : '';

    if (empty($tournament_id)) {
      wp_send_json_success(array('groups' => array(), 'hasFinalRound' => false));
      return;
    }

    // Force refresh groups from external API
    $groups_data = MTP_Admin_Utilities::fetch_tournament_groups($tournament_id, true);

    // Add refreshed flag to the response
    $groups_data['refreshed'] = true;
    wp_send_json_success($groups_data);
  }

  /**
   * Sanitize AJAX data
   */
  private function sanitize_ajax_data($data) {
    return array(
      'tournament_id' => sanitize_text_field($data['tournament_id']),
      'width' => sanitize_text_field($data['width']),
      'height' => sanitize_text_field($data['height']),
      'font_size' => sanitize_text_field($data['font_size']),
      'header_font_size' => sanitize_text_field($data['header_font_size']),
      'bsizeh' => sanitize_text_field($data['bsizeh']),
      'bsizev' => sanitize_text_field($data['bsizev']),
      'bsizeoh' => sanitize_text_field($data['bsizeoh']),
      'bsizeov' => sanitize_text_field($data['bsizeov']),
      'bbsize' => sanitize_text_field($data['bbsize']),
      'table_padding' => sanitize_text_field($data['table_padding']),
      'inner_padding' => sanitize_text_field($data['inner_padding']),
      'text_color' => sanitize_text_field($data['text_color']),
      'main_color' => sanitize_text_field($data['main_color']),
      'bg_color' => sanitize_text_field($data['bg_color']),
      'border_color' => isset($data['border_color']) ? sanitize_text_field($data['border_color']) : 'bbbbbb',
      'head_bottom_border_color' => isset($data['head_bottom_border_color']) ? sanitize_text_field($data['head_bottom_border_color']) : 'bbbbbb',
      'even_bg_color' => isset($data['even_bg_color']) ? sanitize_text_field($data['even_bg_color']) : 'f0f8ffb0',
      'odd_bg_color' => isset($data['odd_bg_color']) ? sanitize_text_field($data['odd_bg_color']) : 'ffffffb0',
      'hover_bg_color' => isset($data['hover_bg_color']) ? sanitize_text_field($data['hover_bg_color']) : 'eeeeffb0',
      'head_bg_color' => isset($data['head_bg_color']) ? sanitize_text_field($data['head_bg_color']) : 'eeeeffff',
      'logo_size' => sanitize_text_field($data['logo_size']),
      'suppress_wins' => isset($data['suppress_wins']) ? sanitize_text_field($data['suppress_wins']) : '0',
      'suppress_logos' => isset($data['suppress_logos']) ? sanitize_text_field($data['suppress_logos']) : '0',
      'suppress_num_matches' => isset($data['suppress_num_matches']) ? sanitize_text_field($data['suppress_num_matches']) : '0',
      'projector_presentation' => isset($data['projector_presentation']) ? sanitize_text_field($data['projector_presentation']) : '0',
      'navigation_for_groups' => isset($data['navigation_for_groups']) ? sanitize_text_field($data['navigation_for_groups']) : '0',
      'language' => isset($data['language']) ? sanitize_text_field($data['language']) : 'en',
      'group' => isset($data['group']) ? sanitize_text_field($data['group']) : '',
    );
  }
}
