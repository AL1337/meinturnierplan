<?php
/**
 * Matches Renderer Class
 *
 * @package MeinTurnierplan
 * @since 0.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Matches Renderer Class
 */
class MTP_Matches_Renderer {

  /**
   * Constructor
   */
  public function __construct() {
    // Constructor can be used for any initialization if needed
  }

  /**
   * Render matches HTML
   */
  public function render_matches_html($matches_id, $atts = array()) {
    // Get tournament ID from attributes or post meta
    $tournament_id = '';
    if (!empty($atts['id'])) {
      $tournament_id = $atts['id'];
    } elseif (!empty($matches_id)) {
      $tournament_id = get_post_meta($matches_id, '_mtp_tournament_id', true);
    }

    // If no tournament ID, show empty static table
    if (empty($tournament_id)) {
      return $this->render_empty_matches($atts);
    }

    // Get width and height from shortcode attributes or use defaults for auto-sizing
    $width = !empty($atts['width']) ? intval($atts['width']) : 300;
    $height = !empty($atts['height']) ? intval($atts['height']) : 200;

    // Build URL parameters array
    $params = $this->build_url_params($tournament_id, $matches_id, $atts);

    // Build the iframe URL
    $iframe_url = 'https://www.meinturnierplan.de/displayMatches.php?' . $this->build_query_string($params);

    // Generate unique ID for this iframe instance
    $iframe_id = 'mtp-matches-' . $tournament_id . '-' . substr(md5(serialize($atts)), 0, 8);

    // Build the iframe HTML with auto-sizing styles and shortcode dimensions
    $iframe_html = sprintf(
      '<iframe id="%s" src="%s" width="%d" height="%d" style="overflow:hidden; min-width: 300px; min-height: 150px; width: %dpx; height: %dpx; border: none; display: block;" allowtransparency="true" frameborder="0">
        <p>%s <a href="https://www.meinturnierplan.de/showit.php?id=%s">%s</a></p>
      </iframe>',
      esc_attr($iframe_id),
      esc_url($iframe_url),
      $width,
      $height,
      $width,
      $height,
      __('Your browser does not support the tournament widget.', 'meinturnierplan'),
      esc_attr($tournament_id),
      __('Go to Tournament.', 'meinturnierplan')
    );

    return $iframe_html;
  }

  /**
   * Build URL parameters for the iframe
   */
  private function build_url_params($tournament_id, $matches_id, $atts) {
    $params = array();
    $params['id'] = $tournament_id;

    // Get styling parameters
    $styling_params = $this->get_styling_parameters($matches_id, $atts);

    // Map shortcode styling parameters to URL parameters
    foreach ($styling_params as $key => $value) {
      if (!empty($value)) {
        $params['s[' . $key . ']'] = $value;
      }
    }

    // Add wrap=false parameter
    $params['s[wrap]'] = 'false';

    // Add bm parameter if projector_presentation is enabled
    $projector_presentation = '';
    if (isset($atts['bm'])) {
      $projector_presentation = $atts['bm'];
    } elseif ($matches_id) {
      $projector_presentation = get_post_meta($matches_id, '_mtp_projector_presentation', true);
    }

    if (!empty($projector_presentation) && $projector_presentation === '1') {
      $params['bm'] = '';
    }

    // Add si parameter if enabled
    $si = '';
    if (isset($atts['si'])) {
      $si = $atts['si'];
    } elseif ($matches_id) {
      $si = get_post_meta($matches_id, '_mtp_si', true);
    }

    if (!empty($si) && $si === '1') {
      $params['si'] = '';
    }

    // Add sf parameter if enabled (Suppress Court)
    $sf = '';
    if (isset($atts['sf'])) {
      $sf = $atts['sf'];
    } elseif ($matches_id) {
      $sf = get_post_meta($matches_id, '_mtp_sf', true);
    }

    if (!empty($sf) && $sf === '1') {
      $params['sf'] = '';
    }

    // Add st parameter if enabled
    $st = '';
    if (isset($atts['st'])) {
      $st = $atts['st'];
    } elseif ($matches_id) {
      $st = get_post_meta($matches_id, '_mtp_st', true);
    }

    if (!empty($st) && $st === '1') {
      $params['st'] = '';
    }

    // Add sg parameter if enabled
    $sg = '';
    if (isset($atts['sg'])) {
      $sg = $atts['sg'];
    } elseif ($matches_id) {
      $sg = get_post_meta($matches_id, '_mtp_sg', true);
    }

    if (!empty($sg) && $sg === '1') {
      $params['sg'] = '';
    }

    // Add sr parameter if enabled (Suppress Referee)
    $sr = '';
    if (isset($atts['sr'])) {
      $sr = $atts['sr'];
    } elseif ($matches_id) {
      $sr = get_post_meta($matches_id, '_mtp_sr', true);
    }

    if (!empty($sr) && $sr === '1') {
      $params['sr'] = '';
    }

    // Add se parameter if enabled
    $se = '';
    if (isset($atts['se'])) {
      $se = $atts['se'];
    } elseif ($matches_id) {
      $se = get_post_meta($matches_id, '_mtp_se', true);
    }

    if (!empty($se) && $se === '1') {
      $params['se'] = '';
    }

    // Add sp parameter if enabled
    $sp = '';
    if (isset($atts['sp'])) {
      $sp = $atts['sp'];
    } elseif ($matches_id) {
      $sp = get_post_meta($matches_id, '_mtp_sp', true);
    }

    if (!empty($sp) && $sp === '1') {
      $params['sp'] = '';
    }

    // Add sh parameter if enabled
    $sh = '';
    if (isset($atts['sh'])) {
      $sh = $atts['sh'];
    } elseif ($matches_id) {
      $sh = get_post_meta($matches_id, '_mtp_sh', true);
    }

    if (!empty($sh) && $sh === '1') {
      $params['sh'] = '';
    }

    // Add setlang parameter if language is specified
    $language = '';
    if (!empty($atts['setlang'])) {
      $language = $atts['setlang'];
    } elseif ($matches_id) {
      $language = get_post_meta($matches_id, '_mtp_language', true);
    }

    if (!empty($language) && $language !== 'en') {
      $params['setlang'] = $language;
    }

    // Add gr parameter if group is specified (but NOT if it's empty - for "All Matches")
    $group = '';
    if (!empty($atts['group'])) {
      $group = $atts['group'];
    } elseif ($matches_id) {
      $group = get_post_meta($matches_id, '_mtp_group', true);
    }

    // Only add gr parameter if group is not empty (empty means "All Matches")
    if (!empty($group)) {
      $params['gr'] = $group;
    }

    // Add tm parameter if participant is specified and not "All" (-1)
    $participant = '';
    if (!empty($atts['participant'])) {
      $participant = $atts['participant'];
    } elseif ($matches_id) {
      $participant = get_post_meta($matches_id, '_mtp_participant', true);
    }

    // Only add tm parameter if participant is not empty and not "-1" (which means "All")
    if (!empty($participant) && $participant !== '-1') {
      $params['tm'] = $participant;
    }

    // Add mn parameter if match_number is specified
    $match_number = '';
    if (!empty($atts['match_number'])) {
      $match_number = $atts['match_number'];
    } elseif (!empty($atts['gamenumbers'])) {
      // Support gamenumbers attribute from shortcode
      $match_number = $atts['gamenumbers'];
    } elseif ($matches_id) {
      $match_number = get_post_meta($matches_id, '_mtp_match_number', true);
    }

    // Add mn parameter if match_number is not empty (handles both single number and range)
    if (!empty($match_number)) {
      $params['mn'] = $match_number;
    }

    return $params;
  }

  /**
   * Get styling parameters from post meta or attributes
   */
  private function get_styling_parameters($matches_id, $atts) {
    $params = array();

    // Define parameter mapping and defaults
    $param_mapping = array(
      'size' => array(
        'attr'    => 's-size',
        'meta'    => '_mtp_font_size',
        'default' => '9'
      ),
      'sizeheader' => array(
        'attr'    => 's-sizeheader',
        'meta'    => '_mtp_header_font_size',
        'default' => '10'
      ),
      'color' => array(
        'attr'    => 's-color',
        'meta'    => '_mtp_text_color',
        'default' => '000000'
      ),
      'maincolor' => array(
        'attr'    => 's-maincolor',
        'meta'    => '_mtp_main_color',
        'default' => '173f75'
      ),
      'padding' => array(
        'attr'    => 's-padding',
        'meta'    => '_mtp_table_padding',
        'default' => '2'
      ),
      'innerpadding' => array(
        'attr'    => 's-innerpadding',
        'meta'    => '_mtp_inner_padding',
        'default' => '5'
      ),
      'bgcolor' => array(
        'attr'    => 's-bgcolor',
        'meta'    => '_mtp_bg_color',
        'default' => '00000000'
      ),
      'bcolor' => array(
        'attr'    => 's-bcolor',
        'meta'    => '_mtp_border_color',
        'default' => 'bbbbbb'
      ),
      'bsizeh' => array(
        'attr'    => 's-bsizeh',
        'meta'    => '_mtp_bsizeh',
        'default' => '1'
      ),
      'bsizev' => array(
        'attr'    => 's-bsizev',
        'meta'    => '_mtp_bsizev',
        'default' => '1'
      ),
      'bsizeoh' => array(
        'attr'    => 's-bsizeoh',
        'meta'    => '_mtp_bsizeoh',
        'default' => '1'
      ),
      'bsizeov' => array(
        'attr'    => 's-bsizeov',
        'meta'    => '_mtp_bsizeov',
        'default' => '1'
      ),
      'bbcolor' => array(
        'attr'    => 's-bbcolor',
        'meta'    => '_mtp_head_bottom_border_color',
        'default' => 'bbbbbb'
      ),
      'bbsize' => array(
        'attr'    => 's-bbsize',
        'meta'    => '_mtp_bbsize',
        'default' => '2'
      ),
      'bgeven' => array(
        'attr'    => 's-bgeven',
        'meta'    => '_mtp_even_bg_color',
        'default' => 'f0f8ffb0'
      ),
      'bgodd' => array(
        'attr'    => 's-bgodd',
        'meta'    => '_mtp_odd_bg_color',
        'default' => 'ffffffb0'
      ),
      'bgover' => array(
        'attr'    => 's-bgover',
        'meta'    => '_mtp_hover_bg_color',
        'default' => 'eeeeffb0'
      ),
      'bghead' => array(
        'attr'    => 's-bghead',
        'meta'    => '_mtp_head_bg_color',
        'default' => 'eeeeffff'
      ),
      'ehrsize' => array(
        'attr'    => 's-ehrsize',
        'meta'    => '_mtp_ehrsize',
        'default' => '10'
      ),
      'ehrtop' => array(
        'attr'    => 's-ehrtop',
        'meta'    => '_mtp_ehrtop',
        'default' => '9'
      ),
      'ehrbottom' => array(
        'attr'    => 's-ehrbottom',
        'meta'    => '_mtp_ehrbottom',
        'default' => '3'
      ),
    );

    foreach ($param_mapping as $url_param => $config) {
      $value = '';

      // Check if value is provided in shortcode attributes
      if (!empty($atts[$config['attr']])) {
        $value = $atts[$config['attr']];
      } elseif ($matches_id) {
        // Get from post meta
        $meta_value = get_post_meta($matches_id, $config['meta'], true);
        if (!empty($meta_value)) {
          $value = $meta_value;
        }

        // Handle special cases for colors with opacity
        if (in_array($url_param, array('bgcolor', 'bgeven', 'bgodd', 'bgover', 'bghead'))) {
          $value = MTP_Admin_Utilities::get_bg_color_with_opacity($matches_id, $config['meta']);
        }
      }

      // Use default if no value found
      if (empty($value)) {
        $value = $config['default'];
      }

      $params[$url_param] = $value;
    }

    return $params;
  }

  /**
   * Render empty static table when no tournament ID is provided
   */
  private function render_empty_matches($atts = array()) {
    // Simple placeholder message with auto-sizing
    $html = '<div class="mtp-empty-preview">';
    $html .= '<svg class="mtp-empty-preview__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" /></svg>';
    $html .= '<strong>' . __('Matches Preview', 'meinturnierplan') . '</strong>';
    $html .= __('Enter a Tournament ID above to display live matches data.', 'meinturnierplan');
    $html .= '</div>';

    return $html;
  }

  /**
   * Build custom query string to handle parameters without values
   */
  private function build_query_string($params) {
    $query_parts = array();

    // Parameters that should appear without values when enabled
    $no_value_params = array('bm', 'si', 'sf', 'st', 'sg', 'sr', 'se', 'sp', 'sh');

    foreach ($params as $key => $value) {
      if (in_array($key, $no_value_params) && $value === '') {
        // Special case: just add parameter name without equals sign
        $query_parts[] = urlencode($key);
      } else {
        // Normal parameter with value
        $query_parts[] = urlencode($key) . '=' . urlencode($value);
      }
    }

    return implode('&', $query_parts);
  }
}
