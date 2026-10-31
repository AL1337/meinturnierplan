<?php
/**
 * Minimal WP_Widget stand-in so the plugin's widget classes can be loaded
 * and rendered without WordPress.
 */
class WP_Widget {

  public $id_base;
  public $name;
  public $widget_options;
  public $control_options;
  public $number = 2;

  public function __construct($id_base, $name, $widget_options = array(), $control_options = array()) {
    $this->id_base = $id_base;
    $this->name = $name;
    $this->widget_options = $widget_options;
    $this->control_options = $control_options;
  }

  public function get_field_id($field_name) {
    return 'widget-' . $this->id_base . '-' . $this->number . '-' . $field_name;
  }

  public function get_field_name($field_name) {
    return 'widget-' . $this->id_base . '[' . $this->number . '][' . $field_name . ']';
  }
}
