<?php
/**
 * Matches Renderer Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
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
      $tournament_id = get_post_meta($matches_id, '_mtp_matches_tournament_id', true);
    }

    // If no tournament ID, show empty static placeholder
    if (empty($tournament_id)) {
      return $this->render_empty_matches($atts);
    }

    // Get width from shortcode attribute or post meta
    $width = !empty($atts['width']) ? $atts['width'] : get_post_meta($matches_id, '_mtp_matches_width', true);
    if (empty($width)) {
      $width = '588'; // Default width for matches
    }

    // Get height from shortcode attribute or post meta
    $height = !empty($atts['height']) ? $atts['height'] : get_post_meta($matches_id, '_mtp_matches_height', true);
    if (empty($height)) {
      $height = '3784'; // Default height for matches
    }

    // Build URL parameters array
    $params = $this->build_url_params($tournament_id, $matches_id, $atts);

    // Build the iframe URL (pointing to displayMatches.php)
    $iframe_url = 'https://www.meinturnierplan.de/displayMatches.php?' . http_build_query($params);

    // Generate unique ID for this iframe instance
    $iframe_id = 'mtp-matches-' . $tournament_id . '-' . substr(md5(serialize($atts)), 0, 8);

    // Build the iframe HTML
    $iframe_html = '<iframe id="' . esc_attr($iframe_id) . '" src="' . esc_url($iframe_url) . '" style="overflow:hidden;" allowtransparency="true" frameborder="0" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '"><p>Your browser does not support the matches widget. <a href="https://www.meinturnierplan.de/showit.php?id=' . esc_attr($tournament_id) . '">Go to Tournament.</a></p></iframe>';

    return $iframe_html;
  }

  /**
   * Build URL parameters
   */
  private function build_url_params($tournament_id, $matches_id, $atts) {
    $params = array(
      'id' => $tournament_id
    );

    // Get the 's' array parameters
    $s_params = array();

    // Font and size parameters
    if (!empty($atts['s-size'])) {
      $s_params['size'] = $atts['s-size'];
    } elseif (!empty($matches_id)) {
      $s_params['size'] = get_post_meta($matches_id, '_mtp_matches_font_size', true) ?: '9';
    }

    if (!empty($atts['s-sizeheader'])) {
      $s_params['sizeheader'] = $atts['s-sizeheader'];
    } elseif (!empty($matches_id)) {
      $s_params['sizeheader'] = get_post_meta($matches_id, '_mtp_matches_header_font_size', true) ?: '10';
    }

    // Color parameters
    if (!empty($atts['s-color'])) {
      $s_params['color'] = $atts['s-color'];
    } elseif (!empty($matches_id)) {
      $s_params['color'] = get_post_meta($matches_id, '_mtp_matches_text_color', true) ?: '000000';
    }

    if (!empty($atts['s-maincolor'])) {
      $s_params['maincolor'] = $atts['s-maincolor'];
    } elseif (!empty($matches_id)) {
      $s_params['maincolor'] = get_post_meta($matches_id, '_mtp_matches_main_color', true) ?: '173f75';
    }

    // Padding parameters
    if (!empty($atts['s-padding'])) {
      $s_params['padding'] = $atts['s-padding'];
    } elseif (!empty($matches_id)) {
      $s_params['padding'] = get_post_meta($matches_id, '_mtp_matches_table_padding', true) ?: '2';
    }

    if (!empty($atts['s-innerpadding'])) {
      $s_params['innerpadding'] = $atts['s-innerpadding'];
    } elseif (!empty($matches_id)) {
      $s_params['innerpadding'] = get_post_meta($matches_id, '_mtp_matches_inner_padding', true) ?: '5';
    }

    // Background color
    if (!empty($atts['s-bgcolor'])) {
      $s_params['bgcolor'] = $atts['s-bgcolor'];
    } elseif (!empty($matches_id)) {
      $bg_color = get_post_meta($matches_id, '_mtp_matches_bg_color', true) ?: '000000';
      $bg_opacity = get_post_meta($matches_id, '_mtp_matches_bg_opacity', true) ?: '0';
      $s_params['bgcolor'] = $this->combine_color_opacity($bg_color, $bg_opacity);
    }

    // Border colors and sizes
    if (!empty($atts['s-bcolor'])) {
      $s_params['bcolor'] = $atts['s-bcolor'];
    } elseif (!empty($matches_id)) {
      $s_params['bcolor'] = get_post_meta($matches_id, '_mtp_matches_border_color', true) ?: 'bbbbbb';
    }

    if (!empty($atts['s-bsizeh'])) {
      $s_params['bsizeh'] = $atts['s-bsizeh'];
    } elseif (!empty($matches_id)) {
      $s_params['bsizeh'] = get_post_meta($matches_id, '_mtp_matches_bsizeh', true) ?: '1';
    }

    if (!empty($atts['s-bsizev'])) {
      $s_params['bsizev'] = $atts['s-bsizev'];
    } elseif (!empty($matches_id)) {
      $s_params['bsizev'] = get_post_meta($matches_id, '_mtp_matches_bsizev', true) ?: '1';
    }

    if (!empty($atts['s-bsizeoh'])) {
      $s_params['bsizeoh'] = $atts['s-bsizeoh'];
    } elseif (!empty($matches_id)) {
      $s_params['bsizeoh'] = get_post_meta($matches_id, '_mtp_matches_bsizeoh', true) ?: '1';
    }

    if (!empty($atts['s-bsizeov'])) {
      $s_params['bsizeov'] = $atts['s-bsizeov'];
    } elseif (!empty($matches_id)) {
      $s_params['bsizeov'] = get_post_meta($matches_id, '_mtp_matches_bsizeov', true) ?: '1';
    }

    if (!empty($atts['s-bbcolor'])) {
      $s_params['bbcolor'] = $atts['s-bbcolor'];
    } elseif (!empty($matches_id)) {
      $s_params['bbcolor'] = get_post_meta($matches_id, '_mtp_matches_head_bottom_border_color', true) ?: 'bbbbbb';
    }

    if (!empty($atts['s-bbsize'])) {
      $s_params['bbsize'] = $atts['s-bbsize'];
    } elseif (!empty($matches_id)) {
      $s_params['bbsize'] = get_post_meta($matches_id, '_mtp_matches_bbsize', true) ?: '2';
    }

    // Background colors for rows
    if (!empty($atts['s-bgeven'])) {
      $s_params['bgeven'] = $atts['s-bgeven'];
    } elseif (!empty($matches_id)) {
      $even_color = get_post_meta($matches_id, '_mtp_matches_even_bg_color', true) ?: 'f0f8ff';
      $even_opacity = get_post_meta($matches_id, '_mtp_matches_even_bg_opacity', true) ?: '69';
      $s_params['bgeven'] = $this->combine_color_opacity($even_color, $even_opacity);
    }

    if (!empty($atts['s-bgodd'])) {
      $s_params['bgodd'] = $atts['s-bgodd'];
    } elseif (!empty($matches_id)) {
      $odd_color = get_post_meta($matches_id, '_mtp_matches_odd_bg_color', true) ?: 'ffffff';
      $odd_opacity = get_post_meta($matches_id, '_mtp_matches_odd_bg_opacity', true) ?: '69';
      $s_params['bgodd'] = $this->combine_color_opacity($odd_color, $odd_opacity);
    }

    if (!empty($atts['s-bgover'])) {
      $s_params['bgover'] = $atts['s-bgover'];
    } elseif (!empty($matches_id)) {
      $hover_color = get_post_meta($matches_id, '_mtp_matches_hover_bg_color', true) ?: 'eeeeff';
      $hover_opacity = get_post_meta($matches_id, '_mtp_matches_hover_bg_opacity', true) ?: '69';
      $s_params['bgover'] = $this->combine_color_opacity($hover_color, $hover_opacity);
    }

    if (!empty($atts['s-bghead'])) {
      $s_params['bghead'] = $atts['s-bghead'];
    } elseif (!empty($matches_id)) {
      $head_color = get_post_meta($matches_id, '_mtp_matches_head_bg_color', true) ?: 'eeeeff';
      $head_opacity = get_post_meta($matches_id, '_mtp_matches_head_bg_opacity', true) ?: '100';
      $s_params['bghead'] = $this->combine_color_opacity($head_color, $head_opacity);
    }

    // Enhanced header parameters (specific to matches)
    if (!empty($atts['s-ehrsize'])) {
      $s_params['ehrsize'] = $atts['s-ehrsize'];
    } elseif (!empty($matches_id)) {
      $s_params['ehrsize'] = get_post_meta($matches_id, '_mtp_matches_ehrsize', true) ?: '10';
    }

    if (!empty($atts['s-ehrtop'])) {
      $s_params['ehrtop'] = $atts['s-ehrtop'];
    } elseif (!empty($matches_id)) {
      $s_params['ehrtop'] = get_post_meta($matches_id, '_mtp_matches_ehrtop', true) ?: '9';
    }

    if (!empty($atts['s-ehrbottom'])) {
      $s_params['ehrbottom'] = $atts['s-ehrbottom'];
    } elseif (!empty($matches_id)) {
      $s_params['ehrbottom'] = get_post_meta($matches_id, '_mtp_matches_ehrbottom', true) ?: '3';
    }

    // Wrap parameter (specific to matches)
    if (!empty($atts['s-wrap'])) {
      $s_params['wrap'] = $atts['s-wrap'];
    } elseif (!empty($matches_id)) {
      $s_params['wrap'] = get_post_meta($matches_id, '_mtp_matches_wrap', true) ?: 'false';
    }

    // Add the 's' parameter as an array
    $params['s'] = $s_params;

    // Add language parameter if specified
    if (!empty($atts['lang'])) {
      $params['setlang'] = $atts['lang'];
    } elseif (!empty($atts['setlang'])) {
      $params['setlang'] = $atts['setlang'];
    } elseif (!empty($matches_id)) {
      $params['setlang'] = get_post_meta($matches_id, '_mtp_matches_language', true) ?: 'en';
    }

    // Add group parameter if specified and not "all" (use 'gr' for iframe URL)
    if (isset($atts['group'])) {
      // Group was explicitly provided in attributes (from AJAX preview)
      if (!empty($atts['group']) && $atts['group'] !== 'all') {
        $params['gr'] = $atts['group'];
      }
      // If empty or "all", don't add gr parameter at all
    } elseif (!empty($matches_id)) {
      // Fall back to post meta only if no group attribute provided
      $group = get_post_meta($matches_id, '_mtp_matches_group', true);
      if (!empty($group) && $group !== 'all') {
        $params['gr'] = $group;
      }
    }

    // Add matches-specific parameters
    if (!empty($atts['spieltag'])) {
      $params['spieltag'] = $atts['spieltag'];
    }

    if (!empty($atts['onlyopen'])) {
      $params['onlyopen'] = $atts['onlyopen'];
    }

    if (!empty($atts['showlocation'])) {
      $params['showlocation'] = $atts['showlocation'];
    }

    // Add bm parameter if projector_presentation is enabled
    $projector_presentation = '';
    if (!empty($atts['bm'])) {
      $params['bm'] = $atts['bm'];
    } elseif ($matches_id) {
      $projector_presentation = get_post_meta($matches_id, '_mtp_matches_projector_presentation', true);
    }

    if (!empty($projector_presentation) && $projector_presentation === '1') {
      $params['bm'] = '1';
    }

    return $params;
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

  /**
   * Render empty static matches when no tournament ID is provided
   */
  private function render_empty_matches($atts = array()) {
    // Get width from shortcode attributes
    $width = !empty($atts['width']) ? $atts['width'] : '588';

    // Simple placeholder message
    $html = '<div style="width: ' . esc_attr($width) . 'px; padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-align: center; color: #6c757d; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
    $html .= '<div style="font-size: 14px; margin-bottom: 8px;">' . __('Tournament Matches', 'meinturnierplan-wp') . '</div>';
    $html .= '<div style="font-size: 12px; opacity: 0.8;">' . __('Enter a Tournament ID to display live matches data', 'meinturnierplan-wp') . '</div>';
    $html .= '</div>';

    return $html;
  }
}
