<?php
/**
 * Admin Utilities Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Admin Utilities Class
 *
 * Provides reusable utility functions for admin interfaces
 */
class MTP_Admin_Utilities {

  /**
   * Render a group header for admin forms
   *
   * @param string $title The title text for the group header
   * @param string $css_class Optional. Additional CSS class for the header. Default 'mtp-group-header'.
   */
  public static function render_group_header($title, $css_class = 'mtp-group-header') {
    echo '<tr>';
    echo '<td class="mtp-group-header-wrapper" colspan="2">';
    echo '<h4 class="' . esc_attr($css_class) . '">' . esc_html($title) . '</h4>';
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a text field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param string $value The field value
   * @param string $description Optional. Field description text
   * @param array $attributes Optional. Additional HTML attributes
   */
  public static function render_text_field($field_name, $label, $value, $description = '', $attributes = array()) {
    $default_attributes = array(
      'type' => 'text',
      'id' => $field_name,
      'name' => $field_name,
      'value' => $value,
      'class' => 'regular-text'
    );

    $attributes = array_merge($default_attributes, $attributes);

    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input';
    foreach ($attributes as $attr_name => $attr_value) {
      echo ' ' . esc_attr($attr_name) . '="' . esc_attr($attr_value) . '"';
    }
    echo ' />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a number field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param mixed $value The field value
   * @param string $description Optional. Field description text
   * @param int|null $min Optional. Minimum value
   * @param int|null $max Optional. Maximum value
   * @param int $step Optional. Step value. Default 1.
   */
  public static function render_number_field($field_name, $label, $value, $description = '', $min = null, $max = null, $step = 1) {
    $attributes = array(
      'type' => 'number',
      'step' => $step
    );

    if ($min !== null) {
      $attributes['min'] = $min;
    }
    if ($max !== null) {
      $attributes['max'] = $max;
    }

    self::render_text_field($field_name, $label, $value, $description, $attributes);
  }

  /**
   * Render a checkbox field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param mixed $value The field value (1 for checked, 0 for unchecked)
   * @param string $description Optional. Field description text
   */
  public static function render_checkbox_field($field_name, $label, $value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="checkbox" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" value="1"' . checked(1, $value, false) . ' />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a select field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param mixed $value The selected value
   * @param array $options Array of option value => label pairs
   * @param string $description Optional. Field description text
   */
  public static function render_select_field($field_name, $label, $value, $options, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<select id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" class="regular-text">';
    foreach ($options as $option_value => $option_label) {
      echo '<option value="' . esc_attr($option_value) . '"' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
    }
    echo '</select>';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a color field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param string $value The color value (without #)
   * @param string $description Optional. Field description text
   */
  public static function render_color_field($field_name, $label, $value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" value="#' . esc_attr($value) . '" class="mtp-color-picker" />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a color field with opacity slider for admin forms
   *
   * @param string $color_field The color field name/ID
   * @param string $opacity_field The opacity field name/ID
   * @param string $label The field label
   * @param string $color_value The color value (without #)
   * @param int $opacity_value The opacity value (0-100)
   * @param string $description Optional. Field description text
   */
  public static function render_color_opacity_field($color_field, $opacity_field, $label, $color_value, $opacity_value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($color_field) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 15px;">';
    echo '<input type="text" id="' . esc_attr($color_field) . '" name="' . esc_attr($color_field) . '" value="#' . esc_attr($color_value) . '" class="mtp-color-picker" style="width: 120px;" />';
    echo '<div style="display: flex; align-items: center; gap: 8px;">';
    echo '<label for="' . esc_attr($opacity_field) . '" style="margin: 0; font-weight: normal;">' . __('Opacity:', 'meinturnierplan') . '</label>';
    echo '<input type="range" id="' . esc_attr($opacity_field) . '" name="' . esc_attr($opacity_field) . '" value="' . esc_attr($opacity_value) . '" min="0" max="100" step="1" style="width: 100px;" />';
    echo '<span id="' . esc_attr($opacity_field) . '_value" style="min-width: 35px; font-size: 12px; color: #666;">' . esc_attr($opacity_value) . '%</span>';
    echo '</div>';
    echo '</div>';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Sanitize meta value based on field type
   *
   * @param string $field The field name/type
   * @param mixed $value The value to sanitize
   * @return mixed The sanitized value
   */
  public static function sanitize_meta_value($field, $value) {
    // Checkbox fields
    if (in_array($field, array('suppress_wins', 'suppress_logos', 'suppress_num_matches', 'projector_presentation', 'navigation_for_groups'))) {
      return $value === '1' ? '1' : '0';
    }

    // Color fields
    if (in_array($field, array('text_color', 'main_color', 'bg_color', 'border_color', 'head_bottom_border_color', 'even_bg_color', 'odd_bg_color', 'hover_bg_color', 'head_bg_color'))) {
      $color = sanitize_hex_color($value);
      return $color ? ltrim($color, '#') : '';
    }

    // Opacity fields
    if (strpos($field, '_opacity') !== false) {
      $opacity = absint($value);
      return max(0, min(100, $opacity));
    }

    // Number fields
    if (in_array($field, array('width', 'height', 'font_size', 'header_font_size', 'bsizeh', 'bsizev', 'bsizeoh', 'bsizeov', 'bbsize', 'table_padding', 'inner_padding', 'logo_size'))) {
      return absint($value);
    }

    // Language field - validate against allowed languages
    if ($field === 'language') {
      $allowed_languages = array('en', 'de', 'es', 'fr', 'hr', 'it', 'pl', 'sl', 'tr');
      return in_array($value, $allowed_languages) ? $value : 'en';
    }

    // Text fields
    return sanitize_text_field($value);
  }

  /**
   * Combine color and opacity into a single hex value
   *
   * @param string $color The color value (without #)
   * @param int $opacity The opacity value (0-100)
   * @return string The combined color value with opacity
   */
  public static function combine_color_opacity($color, $opacity) {
    if ($opacity !== '' && $opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      return $color . $opacity_hex;
    }
    return $color;
  }
}
