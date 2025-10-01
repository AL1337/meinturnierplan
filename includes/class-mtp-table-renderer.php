<?php
/**
 * Table Renderer Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Table Renderer Class
 */
class MTP_Table_Renderer {
  
  /**
   * Constructor
   */
  public function __construct() {
    // Constructor can be used for any initialization if needed
  }
  
  /**
   * Render table HTML
   */
  public function render_table_html($table_id, $atts = array()) {
    // Get tournament ID from attributes or post meta
    $tournament_id = '';
    if (!empty($atts['id'])) {
      $tournament_id = $atts['id'];
    } elseif (!empty($table_id)) {
      $tournament_id = get_post_meta($table_id, '_mtp_tournament_id', true);
    }
    
    // If no tournament ID, show empty static table
    if (empty($tournament_id)) {
      return $this->render_empty_table($atts);
    }
    
    // Get width from shortcode attribute or post meta
    $width = !empty($atts['width']) ? $atts['width'] : get_post_meta($table_id, '_mtp_table_width', true);
    if (empty($width)) {
      $width = '300'; // Default width
    }

    // Get height from shortcode attribute or post meta
    $height = !empty($atts['height']) ? $atts['height'] : get_post_meta($table_id, '_mtp_table_height', true);
    if (empty($height)) {
      $height = '152'; // Default height
    }
    
    // Build URL parameters array
    $params = $this->build_url_params($tournament_id, $table_id, $atts);
    
    // Build the iframe URL
    $iframe_url = 'https://www.meinturnierplan.de/displayTable.php?' . http_build_query($params);
    
    // Generate unique ID for this iframe instance
    $iframe_id = 'mtp-table-' . $tournament_id . '-' . substr(md5(serialize($atts)), 0, 8);
    
    // Build the iframe HTML
    $iframe_html = sprintf(
      '<iframe id="%s" src="%s" style="overflow:hidden;" allowtransparency="true" frameborder="0" width="%s" height="%s">
        <p>%s <a href="https://www.meinturnierplan.de/showit.php?id=%s">%s</a></p>
      </iframe>',
      esc_attr($iframe_id),
      esc_url($iframe_url),
      esc_attr($width),
      esc_attr($height),
      __('Your browser does not support the tournament widget.', 'meinturnierplan-wp'),
      esc_attr($tournament_id),
      __('Go to Tournament.', 'meinturnierplan-wp')
    );
    
    return $iframe_html;
  }
  
  /**
   * Build URL parameters for the iframe
   */
  private function build_url_params($tournament_id, $table_id, $atts) {
    $params = array();
    $params['id'] = $tournament_id;
    
    // Get styling parameters
    $styling_params = $this->get_styling_parameters($table_id, $atts);
    
    // Map shortcode styling parameters to URL parameters
    foreach ($styling_params as $key => $value) {
      if (!empty($value)) {
        $params['s[' . $key . ']'] = $value;
      }
    }
    
    // Add wrap=false parameter
    $params['s[wrap]'] = 'false';
    
    // Add sw parameter if suppress_wins is enabled
    $suppress_wins = '';
    if (!empty($atts['sw'])) {
      $suppress_wins = $atts['sw'];
    } elseif ($table_id) {
      $suppress_wins = get_post_meta($table_id, '_mtp_suppress_wins', true);
    }
    
    if (!empty($suppress_wins) && $suppress_wins === '1') {
      $params['sw'] = '1';
    }
    
    // Add sl parameter if suppress_logos is enabled
    $suppress_logos = '';
    if (!empty($atts['sl'])) {
      $suppress_logos = $atts['sl'];
    } elseif ($table_id) {
      $suppress_logos = get_post_meta($table_id, '_mtp_suppress_logos', true);
    }
    
    if (!empty($suppress_logos) && $suppress_logos === '1') {
      $params['sl'] = '1';
    }
    
    // Add sn parameter if suppress_num_matches is enabled
    $suppress_num_matches = '';
    if (!empty($atts['sn'])) {
      $suppress_num_matches = $atts['sn'];
    } elseif ($table_id) {
      $suppress_num_matches = get_post_meta($table_id, '_mtp_suppress_num_matches', true);
    }
    
    if (!empty($suppress_num_matches) && $suppress_num_matches === '1') {
      $params['sn'] = '1';
    }
    
    return $params;
  }
  
  /**
   * Get styling parameters from post meta or attributes
   */
  private function get_styling_parameters($table_id, $atts) {
    $params = array();
    
    // Define parameter mapping and defaults
    $param_mapping = array(
      'size' => array('attr' => 's-size', 'meta' => '_mtp_font_size', 'default' => '9'),
      'sizeheader' => array('attr' => 's-sizeheader', 'meta' => '_mtp_header_font_size', 'default' => '10'),
      'color' => array('attr' => 's-color', 'meta' => '_mtp_text_color', 'default' => '000000'),
      'maincolor' => array('attr' => 's-maincolor', 'meta' => '_mtp_main_color', 'default' => '173f75'),
      'padding' => array('attr' => 's-padding', 'meta' => '_mtp_table_padding', 'default' => '2'),
      'innerpadding' => array('attr' => 's-innerpadding', 'meta' => '_mtp_inner_padding', 'default' => '5'),
      'bgcolor' => array('attr' => 's-bgcolor', 'meta' => '_mtp_bg_color', 'default' => '00000000'),
      'logosize' => array('attr' => 's-logosize', 'meta' => '_mtp_logo_size', 'default' => '20'),
      'bcolor' => array('attr' => 's-bcolor', 'meta' => '_mtp_border_color', 'default' => 'bbbbbb'),
      'bsizeh' => array('attr' => 's-bsizeh', 'meta' => '_mtp_bsizeh', 'default' => '1'),
      'bsizev' => array('attr' => 's-bsizev', 'meta' => '_mtp_bsizev', 'default' => '1'),
      'bsizeoh' => array('attr' => 's-bsizeoh', 'meta' => '_mtp_bsizeoh', 'default' => '1'),
      'bsizeov' => array('attr' => 's-bsizeov', 'meta' => '_mtp_bsizeov', 'default' => '1'),
      'bbcolor' => array('attr' => 's-bbcolor', 'meta' => '_mtp_head_bottom_border_color', 'default' => 'bbbbbb'),
      'bbsize' => array('attr' => 's-bbsize', 'meta' => '_mtp_bbsize', 'default' => '2'),
      'bgeven' => array('attr' => 's-bgeven', 'meta' => '_mtp_even_bg_color', 'default' => 'f0f8ffb0'),
      'bgodd' => array('attr' => 's-bgodd', 'meta' => '_mtp_odd_bg_color', 'default' => 'ffffffb0'),
      'bgover' => array('attr' => 's-bgover', 'meta' => '_mtp_hover_bg_color', 'default' => 'eeeeffb0'),
      'bghead' => array('attr' => 's-bghead', 'meta' => '_mtp_head_bg_color', 'default' => 'eeeeffff'),
    );
    
    foreach ($param_mapping as $url_param => $config) {
      $value = '';
      
      // Check if value is provided in shortcode attributes
      if (!empty($atts[$config['attr']])) {
        $value = $atts[$config['attr']];
      } elseif ($table_id) {
        // Get from post meta
        $meta_value = get_post_meta($table_id, $config['meta'], true);
        if (!empty($meta_value)) {
          $value = $meta_value;
        }
        
        // Handle special cases for colors with opacity
        if (in_array($url_param, array('bgcolor', 'bgeven', 'bgodd', 'bgover', 'bghead'))) {
          $value = $this->get_bg_color_with_opacity($table_id, $config['meta']);
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
   * Get background color with opacity from post meta
   */
  private function get_bg_color_with_opacity($post_id, $color_meta_key) {
    if (!$post_id) {
      return '00000000'; // Transparent default
    }
    
    $bg_color = get_post_meta($post_id, $color_meta_key, true);
    
    // Determine opacity meta key based on color meta key
    $opacity_meta_mapping = array(
      '_mtp_bg_color' => '_mtp_bg_opacity',
      '_mtp_even_bg_color' => '_mtp_even_bg_opacity',
      '_mtp_odd_bg_color' => '_mtp_odd_bg_opacity',
      '_mtp_hover_bg_color' => '_mtp_hover_bg_opacity',
      '_mtp_head_bg_color' => '_mtp_head_bg_opacity',
    );
    
    $opacity_meta_key = isset($opacity_meta_mapping[$color_meta_key]) ? $opacity_meta_mapping[$color_meta_key] : null;
    $bg_opacity = $opacity_meta_key ? get_post_meta($post_id, $opacity_meta_key, true) : null;
    
    // Set defaults
    if (empty($bg_color)) {
      $bg_color = '000000'; // Default color
    }
    
    if (empty($bg_opacity) && $bg_opacity !== '0') {
      $bg_opacity = $color_meta_key === '_mtp_bg_color' ? 0 : 69; // Different defaults for different colors
    }
    
    return $this->combine_color_opacity($bg_color, $bg_opacity);
  }
  
  /**
   * Combine hex color and opacity percentage into 8-character hex
   */
  private function combine_color_opacity($hex_color, $opacity_percent) {
    // Remove # if present
    $hex_color = ltrim($hex_color, '#');
    
    // If color already has alpha (8 characters), return as is
    if (strlen($hex_color) == 8) {
      return $hex_color;
    }
    
    // If no opacity specified, default to fully opaque
    if ($opacity_percent === '' || $opacity_percent === null) {
      $opacity_percent = 100;
    }
    
    // Convert opacity percentage to hex
    $opacity_hex = str_pad(dechex(round(($opacity_percent / 100) * 255)), 2, '0', STR_PAD_LEFT);
    
    return $hex_color . $opacity_hex;
  }
  
  /**
   * Render empty static table when no tournament ID is provided
   */
  private function render_empty_table($atts = array()) {
    // Get width from shortcode attributes
    $width = !empty($atts['width']) ? $atts['width'] : '300';
    
    // Simple placeholder message
    $html = '<div style="width: ' . esc_attr($width) . 'px; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-align: center; color: #6c757d; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
    $html .= '<strong>' . __('Tournament Table Preview', 'meinturnierplan-wp') . '</strong><br>';
    $html .= __('Enter a Tournament ID above to display live tournament data.', 'meinturnierplan-wp');
    $html .= '</div>';
    
    return $html;
  }
  
  /**
   * Convert hex color with alpha to rgba
   */
  public function hex_to_rgba($hex) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Handle 8-character hex (RRGGBBAA)
    if (strlen($hex) == 8) {
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
      $a = round(hexdec(substr($hex, 6, 2)) / 255, 2);
      return "rgba($r, $g, $b, $a)";
    }
    // Handle 6-character hex (RRGGBB)
    elseif (strlen($hex) == 6) {
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
      return "rgb($r, $g, $b)";
    }
    // Handle 3-character hex (RGB)
    elseif (strlen($hex) == 3) {
      $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
      $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
      $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
      return "rgb($r, $g, $b)";
    }
    
    return 'transparent';
  }
}
