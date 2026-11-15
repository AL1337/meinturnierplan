<?php
/**
 * Shortcodes [mtrn-table] and [mtrn-matches].
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Registers one shortcode per embed type; both are rendered the same way.
 */
class MTRN_Shortcodes {

  /** @var MTRN_Embed_Renderer */
  private $renderer;

  public function __construct(MTRN_Embed_Renderer $renderer) {
    $this->renderer = $renderer;

    foreach (MTRN_Options::types() as $type => $config) {
      add_shortcode($config['shortcode'], function ($atts) use ($type) {
        return $this->render($type, $atts);
      });
    }
  }

  /**
   * Render a shortcode. Attributes left out fall back to the settings saved on
   * post_id and then to the built-in defaults (see MTRN_Options::resolve()).
   *
   * @param string       $type 'table' or 'matches'.
   * @param array|string $atts Shortcode attributes as WordPress passes them.
   * @return string HTML
   */
  public function render($type, $atts) {
    $config = MTRN_Options::type($type);
    $atts = shortcode_atts(MTRN_Options::shortcode_defaults($type), (array) $atts, $config['shortcode']);
    $post_id = !empty($atts['post_id']) ? (int) $atts['post_id'] : 0;

    return $this->renderer->render($type, $post_id, $atts);
  }
}
