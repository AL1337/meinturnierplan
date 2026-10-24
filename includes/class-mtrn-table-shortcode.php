<?php
/**
 * Table Shortcode Handler Class
 *
 * @package MeinTurnierplan
 * @since   0.1.0
 * @version 1.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Table Shortcode Handler Class
 */
class MTRN_Table_Shortcode {

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
   * Initialize shortcode
   */
  public function init() {
    add_shortcode('mtrn-table', array($this, 'shortcode_callback'));
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
      's-bbcolor' => '',
      's-bbsize' => '',
      's-bgeven' => '',
      's-bgodd' => '',
      's-bgover' => '',
      's-bghead' => '',
      'width' => '',
      'height' => '',
      'sw' => '', // Suppress wins, losses, etc.
      'sl' => '', // Suppress logos
      'sn' => '', // Suppress num matches
      'bm' => '', // Projector presentation
      'nav' => '', // Navigation for groups
      's-wrap' => '' // Wrap long names; also makes the embed horizontally scrollable on small screens. Empty falls back to the Responsive setting saved on post_id
    ), $atts, 'mtrn-table');

    // Map lang to setlang for internal processing. An empty value lets the
    // renderer fall back to the language saved on the post (post_id).
    $atts['setlang'] = $atts['lang'];

    // Use post_id if provided for getting width from meta, otherwise use null
    $post_id = !empty($atts['post_id']) ? $atts['post_id'] : null;

    // Always render table - empty if no ID, with data if ID provided
    return $this->table_renderer->render_table_html($post_id, $atts);
  }
}
