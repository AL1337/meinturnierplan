<?php
/**
 * AJAX Matches Handler Class
 *
 * @package MeinTurnierplan
 * @since 0.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * AJAX Matches Handler Class
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
    add_action('wp_ajax_mtp_get_matches_groups', array($this, 'ajax_get_matches_groups'));
    add_action('wp_ajax_mtp_refresh_matches_groups', array($this, 'ajax_refresh_matches_groups'));
    add_action('wp_ajax_mtp_get_matches_teams', array($this, 'ajax_get_matches_teams'));
    add_action('wp_ajax_mtp_refresh_matches_teams', array($this, 'ajax_refresh_matches_teams'));
  }

  /**
   * AJAX handler for matches preview (existing one for admin)
   */
  public function ajax_preview_matches() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mtp_preview_nonce')) {
      wp_die('Security check failed');
    }

    $post_id = absint($_POST['post_id']);
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
      's-bsizeh' => $data['bsizeh'] ? $data['bsizeh'] : '1',
      's-bsizev' => $data['bsizev'] ? $data['bsizev'] : '1',
      's-bsizeoh' => $data['bsizeoh'] ? $data['bsizeoh'] : '1',
      's-bsizeov' => $data['bsizeov'] ? $data['bsizeov'] : '1',
      's-bbsize' => $data['bbsize'] ? $data['bbsize'] : '2',
      's-ehrsize' => $data['ehrsize'] ? $data['ehrsize'] : '10',
      's-ehrtop' => $data['ehrtop'] ? $data['ehrtop'] : '9',
      's-ehrbottom' => $data['ehrbottom'] ? $data['ehrbottom'] : '3',
      'setlang' => $data['language'] ? $data['language'] : 'en'
    );

    // Add group parameter if specified
    if (!empty($data['group'])) {
      $atts['group'] = $data['group'];
    }

    // Add participant parameter if specified and not default "All"
    if (!empty($data['participant']) && $data['participant'] !== '-1') {
      $atts['participant'] = $data['participant'];
    }

    // Add match_number parameter if specified
    if (!empty($data['match_number'])) {
      $atts['match_number'] = $data['match_number'];
    }

    // Add bm parameter if projector_presentation is enabled
    if (!empty($data['projector_presentation']) && $data['projector_presentation'] === '1') {
      $atts['bm'] = '1';
    }

    // Add si parameter if si is enabled
    if (!empty($data['si']) && $data['si'] === '1') {
      $atts['si'] = '1';
    }

    // Add sf parameter if sf is enabled
    if (!empty($data['sf']) && $data['sf'] === '1') {
      $atts['sf'] = '1';
    }

    // Add st parameter if st is enabled
    if (!empty($data['st']) && $data['st'] === '1') {
      $atts['st'] = '1';
    }

    // Add sg parameter if sg is enabled
    if (!empty($data['sg']) && $data['sg'] === '1') {
      $atts['sg'] = '1';
    }

    // Add se parameter if se is enabled
    if (!empty($data['se']) && $data['se'] === '1') {
      $atts['se'] = '1';
    }

    // Add sp parameter if sp is enabled
    if (!empty($data['sp']) && $data['sp'] === '1') {
      $atts['sp'] = '1';
    }

    // Add sh parameter if sh is enabled
    if (!empty($data['sh']) && $data['sh'] === '1') {
      $atts['sh'] = '1';
    }

    $html = $this->matches_renderer->render_matches_html($post_id, $atts);

    wp_send_json_success($html);
  }

  /**
   * AJAX handler for fetching tournament groups
   */
  public function ajax_get_matches_groups() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mtp_preview_nonce')) {
      wp_die('Security check failed');
    }

    $tournament_id = sanitize_text_field($_POST['tournament_id']);
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
  public function ajax_refresh_matches_groups() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mtp_preview_nonce')) {
      wp_die('Security check failed');
    }

    $tournament_id = sanitize_text_field($_POST['tournament_id']);

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
   * AJAX handler for fetching tournament teams
   */
  public function ajax_get_matches_teams() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mtp_preview_nonce')) {
      wp_die('Security check failed');
    }

    $tournament_id = sanitize_text_field($_POST['tournament_id']);
    $force_refresh = isset($_POST['force_refresh']) ? (bool)$_POST['force_refresh'] : false;

    if (empty($tournament_id)) {
      wp_send_json_success(array('teams' => array()));
      return;
    }

    // Fetch teams from external API (with caching)
    $teams = MTP_Admin_Utilities::fetch_tournament_teams($tournament_id, $force_refresh);

    wp_send_json_success(array('teams' => $teams));
  }

  /**
   * AJAX handler for refreshing tournament teams (force refresh)
   */
  public function ajax_refresh_matches_teams() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mtp_preview_nonce')) {
      wp_die('Security check failed');
    }

    $tournament_id = sanitize_text_field($_POST['tournament_id']);

    if (empty($tournament_id)) {
      wp_send_json_success(array('teams' => array()));
      return;
    }

    // Force refresh teams from external API
    $teams = MTP_Admin_Utilities::fetch_tournament_teams($tournament_id, true);

    // Add refreshed flag to the response
    wp_send_json_success(array('teams' => $teams, 'refreshed' => true));
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
      'ehrsize' => isset($data['ehrsize']) ? sanitize_text_field($data['ehrsize']) : '10',
      'ehrtop' => isset($data['ehrtop']) ? sanitize_text_field($data['ehrtop']) : '9',
      'ehrbottom' => isset($data['ehrbottom']) ? sanitize_text_field($data['ehrbottom']) : '3',
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
      'projector_presentation' => isset($data['projector_presentation']) ? sanitize_text_field($data['projector_presentation']) : '0',
      'si' => isset($data['si']) ? sanitize_text_field($data['si']) : '0',
      'sf' => isset($data['sf']) ? sanitize_text_field($data['sf']) : '0',
      'st' => isset($data['st']) ? sanitize_text_field($data['st']) : '0',
      'sg' => isset($data['sg']) ? sanitize_text_field($data['sg']) : '0',
      'se' => isset($data['se']) ? sanitize_text_field($data['se']) : '0',
      'sp' => isset($data['sp']) ? sanitize_text_field($data['sp']) : '0',
      'sh' => isset($data['sh']) ? sanitize_text_field($data['sh']) : '0',
      'language' => isset($data['language']) ? sanitize_text_field($data['language']) : 'en',
      'group' => isset($data['group']) ? sanitize_text_field($data['group']) : '',
      'participant' => isset($data['participant']) ? sanitize_text_field($data['participant']) : '-1',
      'match_number' => isset($data['match_number']) ? sanitize_text_field($data['match_number']) : '',
    );
  }
}
