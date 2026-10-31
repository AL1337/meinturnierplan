<?php
/**
 * Test stand-in for the plugin singleton: the widgets fetch their renderer
 * from MTRN_Plugin::instance(). The real class wires up every component and
 * WordPress hook, which the unit tests do not need.
 */
class MTRN_Plugin {

  public $table_renderer;
  public $matches_renderer;

  private static $instance = null;

  public static function instance() {
    if (self::$instance === null) {
      self::$instance = new self();
      self::$instance->table_renderer = new MTRN_Table_Renderer();
      self::$instance->matches_renderer = new MTRN_Matches_Renderer();
    }
    return self::$instance;
  }
}
