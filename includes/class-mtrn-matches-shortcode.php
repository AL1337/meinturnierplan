<?php
/**
 * Matches Shortcode Handler Class
 *
 * @package MeinTurnierplan
 * @since   0.2.0
 * @version 1.3.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Matches Shortcode Handler Class
 */
class MTRN_Matches_Shortcode {

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
   * Initialize shortcode
   */
  public function init() {
    add_shortcode('mtrn-matches', array($this, 'shortcode_callback'));
  }

  /**
   * Shortcode callback
   */
  public function shortcode_callback($atts) {
    $atts = shortcode_atts(array(
      'id' => '',
      'post_id' => '', // Internal WordPress post ID (optional)
      'lang' => '', // Language code; empty falls back to the language saved on post_id, then to English
      'group' => '',
      // Styling and display options default to '' so that the renderer falls back
      // to the settings saved on post_id and then to its built-in defaults.
      's-size' => '',
      's-sizeheader' => '',
      's-color' => '',
      's-maincolor' => '',
      's-padding' => '',
      's-innerpadding' => '',
      's-bgcolor' => '',
      's-logosize' => '',
      's-bcolor' => '',
      's-bsizeh' => '',
      's-bsizev' => '',
      's-bsizeoh' => '',
      's-bsizeov' => '',
      's-ehrsize' => '',
      's-ehrtop' => '',
      's-ehrbottom' => '',
      's-bbcolor' => '',
      's-bbsize' => '',
      's-bgeven' => '',
      's-bgodd' => '',
      's-bgover' => '',
      's-bghead' => '',
      'width' => '',
      'height' => '',
      'bm' => '',
      'si' => '',
      'sf' => '',
      'st' => '',
      'sg' => '',
      'sr' => '',
      'se' => '',
      'sp' => '',
      'sh' => '',
      'gamenumbers' => '',
      'participant' => '', // Participant (team) number; empty or -1 shows all participants
      's-wrap' => '', // Wrap long names; also makes the embed horizontally scrollable on small screens. Empty falls back to the Responsive setting saved on post_id
    ), $atts, 'mtrn-matches');

    // Map lang to setlang for internal processing. An empty value lets the
    // renderer fall back to the language saved on the post (post_id).
    $atts['setlang'] = $atts['lang'];

    // Use post_id if provided for getting width from meta, otherwise use null
    $post_id = !empty($atts['post_id']) ? $atts['post_id'] : null;

    // Always render matches - empty if no ID, with data if ID provided
    return $this->matches_renderer->render_matches_html($post_id, $atts);
  }
}
