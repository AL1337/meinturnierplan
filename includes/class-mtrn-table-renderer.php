<?php
/**
 * Table renderer: thin wrapper kept while the callers are migrated to
 * MTRN_Embed_Renderer::render('table', ...). Removed in a later step.
 *
 * @package MeinTurnierplan
 * @since   0.2.0
 * @version 1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class MTRN_Table_Renderer extends MTRN_Embed_Renderer {

  public function render_table_html($table_id, $atts = array()) {
    return $this->render('table', $table_id, (array) $atts);
  }
}
