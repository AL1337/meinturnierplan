<?php
/**
 * Matches renderer: thin wrapper kept while the callers are migrated to
 * MTRN_Embed_Renderer::render('matches', ...). Removed in a later step.
 *
 * @package MeinTurnierplan
 * @since   0.2.0
 * @version 1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class MTRN_Matches_Renderer extends MTRN_Embed_Renderer {

  public function render_matches_html($matches_id, $atts = array()) {
    return $this->render('matches', $matches_id, (array) $atts);
  }
}
