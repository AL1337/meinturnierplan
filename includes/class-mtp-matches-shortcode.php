<?php
/**
 * Matches Shortcode Handler Class
 *
 * @package MeinTurnierplan
 * @since   0.2.0
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Matches Shortcode Handler Class
 */
class MTP_Matches_Shortcode {

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
    add_shortcode('mtp-matches', array($this, 'shortcode_callback'));
  }

  /**
   * Shortcode callback
   */
  public function shortcode_callback($atts) {
    $atts = shortcode_atts(array(
      'id' => '',
      'post_id' => '', // Internal WordPress post ID (optional)
      'lang' => 'en',
      'group' => '',
      's-size' => '9',
      's-sizeheader' => '10',
      's-color' => '000000',
      's-maincolor' => '173f75',
      's-padding' => '2',
      's-innerpadding' => '5',
      's-bgcolor' => '00000000',
      's-logosize' => '20',
      's-bcolor' => 'bbbbbb',
      's-bsizeh' => '1',
      's-bsizev' => '1',
      's-bsizeoh' => '1',
      's-bsizeov' => '1',
      's-ehrsize' => '10',
      's-ehrtop' => '9',
      's-ehrbottom' => '3',
      's-bbcolor' => 'bbbbbb',
      's-bbsize' => '2',
      's-bgeven' => 'f0f8ffb0',
      's-bgodd' => 'ffffffb0',
      's-bgover' => 'eeeeffb0',
      's-bghead' => 'eeeeffff',
      'width' => '',
      'height' => '',
      'bm' => '0',
      'si' => '0',
      'sf' => '0',
      'st' => '0',
      'sg' => '0',
      'sr' => '0',
      'se' => '0',
      'sp' => '0',
      'sh' => '0',
      'gamenumbers' => '',
    ), $atts, 'mtp-matches');

    // Map lang to setlang for internal processing
    $atts['setlang'] = $atts['lang'];

    // Use post_id if provided for getting width from meta, otherwise use null
    $post_id = !empty($atts['post_id']) ? $atts['post_id'] : null;

    // Always render matches - empty if no ID, with data if ID provided
    return $this->matches_renderer->render_matches_html($post_id, $atts);
  }
}
