<?php
/**
 * Shortcode Handler Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Shortcode Handler Class
 */
class MTP_Shortcode {
  
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
    add_shortcode('mtp-table', array($this, 'shortcode_callback'));
  }
  
  /**
   * Shortcode callback
   */
  public function shortcode_callback($atts) {
    $atts = shortcode_atts(array(
      'id' => '',
      'post_id' => '', // Internal WordPress post ID (optional)
      'lang' => 'en',
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
      's-bbcolor' => 'bbbbbb',
      's-bbsize' => '2',
      's-bgeven' => 'f0f8ffb0',
      's-bgodd' => 'ffffffb0',
      's-bgover' => 'eeeeffb0',
      's-bghead' => 'eeeeffff',
      'width' => '',
      'height' => '',
      'sw' => '0', // Suppress wins, losses, etc.
      'sl' => '0', // Suppress logos
      'sn' => '0'  // Suppress num matches
    ), $atts, 'mtp-table');
    
    // Use post_id if provided for getting width from meta, otherwise use null
    $post_id = !empty($atts['post_id']) ? $atts['post_id'] : null;
    
    // Always render table - empty if no ID, with data if ID provided
    return $this->table_renderer->render_table_html($post_id, $atts);
  }
}
