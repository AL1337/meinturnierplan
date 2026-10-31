<?php
/**
 * Embed renderer: turns resolved options into the iframe markup for both the
 * tournament table and the match list.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Embed renderer
 */
class MTRN_Embed_Renderer {

  /**
   * Render an embed.
   *
   * @param string   $type    'table' or 'matches'.
   * @param int|null $post_id Post whose saved settings act as fallback.
   * @param array    $atts    Attributes (shortcode attributes or admin preview values).
   * @return string HTML
   */
  public function render($type, $post_id = 0, array $atts = array()) {
    $values = MTRN_Options::resolve($type, $atts, $post_id);

    if ($values['tournament_id'] === '') {
      return $this->render_placeholder($type);
    }

    $config = MTRN_Options::type($type);
    $base_url = MTRN_Admin_Utilities::get_api_base_url($values['language']);
    $iframe_url = $base_url . '/' . $config['endpoint'] . '?' . MTRN_Options::embed_query($type, $values);

    // Unique id per embed instance; the resize script matches on the prefix only
    $iframe_id = $config['id_prefix'] . $values['tournament_id'] . '-' . substr(md5(serialize($values)), 0, 8);

    $html = sprintf(
      '<iframe id="%s" class="mtrn-embed-frame" src="%s" width="%d" height="%d" allowtransparency="true" frameborder="0">
        <p>%s <a href="%s/showit.php?id=%s">%s</a></p>
      </iframe>',
      esc_attr($iframe_id),
      esc_url($iframe_url),
      $values['width'],
      $values['height'],
      __('Your browser does not support the tournament widget.', 'meinturnierplan'),
      esc_url($base_url),
      esc_attr($values['tournament_id']),
      __('Go to Tournament.', 'meinturnierplan')
    );

    // Responsive: the iframe keeps its content width (set via postMessage) and
    // the wrapper provides a single horizontal scroll on narrow screens.
    if ($values['responsive']) {
      $html = '<div class="mtrn-embed-responsive">' . $html . '</div>';
    }

    return $html;
  }

  /**
   * Placeholder shown while no tournament ID is set.
   */
  private function render_placeholder($type) {
    $config = MTRN_Options::type($type);

    $html = '<div class="mtrn-empty-preview">';
    $html .= '<svg class="mtrn-empty-preview__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" /></svg>';
    $html .= '<strong>' . $config['placeholder_title'] . '</strong>';
    $html .= $config['placeholder_text'];
    $html .= '</div>';

    return $html;
  }
}
