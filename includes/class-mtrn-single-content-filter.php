<?php
/**
 * Single post view: a tournament table or match list post shows its embed
 * above its own content.
 *
 * @package MeinTurnierplan
 * @since   0.3.1
 * @version 1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class MTRN_Single_Content_Filter {

  /** @var MTRN_Embed_Renderer */
  private $renderer;

  public function __construct(MTRN_Embed_Renderer $renderer) {
    $this->renderer = $renderer;
    add_filter('the_content', array($this, 'populate_single_cpt_content'));
  }

  /**
   * Prepend the embed to the content of a single table / match list post
   *
   * @param string $content The post content
   * @return string Modified content
   */
  public function populate_single_cpt_content($content) {
    $post_types = array_column(MTRN_Options::types(), 'post_type');

    if (!is_singular($post_types) || !in_the_loop() || !is_main_query()) {
      return $content;
    }

    global $post;
    $post_id = $post->ID;
    $type = MTRN_Options::type_for_post_type(get_post_type($post_id));

    if ($type === '' || (string) get_post_meta($post_id, '_mtrn_tournament_id', true) === '') {
      return $content;
    }

    return $this->renderer->render($type, $post_id, array()) . $content;
  }
}
