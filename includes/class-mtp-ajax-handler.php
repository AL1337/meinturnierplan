<?php
/**
 * AJAX Handler Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * AJAX Handler Class
 */
class MTP_Ajax_Handler {
  
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
  }
  
  /**
   * AJAX handler for table preview (existing one for admin)
   */
  public function ajax_preview_table() {
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
      's-logosize' => $data['logo_size'] ? $data['logo_size'] : '20',
      's-bsizeh' => $data['bsizeh'] ? $data['bsizeh'] : '1',
      's-bsizev' => $data['bsizev'] ? $data['bsizev'] : '1',
      's-bsizeoh' => $data['bsizeoh'] ? $data['bsizeoh'] : '1',
      's-bsizeov' => $data['bsizeov'] ? $data['bsizeov'] : '1',
      's-bbsize' => $data['bbsize'] ? $data['bbsize'] : '2'
    );
    
    $html = $this->table_renderer->render_table_html($post_id, $atts);
    
    wp_send_json_success($html);
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
    );
  }
}
