<?php
/**
 * Admin Utilities Class
 *
 * @package MeinTurnierplan
 * @since   1.0.0
 * @version 1.0.0
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
class MTRN_Admin_Utilities {

  /**
   * Render a group header for admin forms
   *
   * @param string $title The title text for the group header
   * @param string $css_class Optional. Additional CSS class for the header. Default 'mtrn-group-header'.
   */
  public static function render_group_header($title, $css_class = 'mtrn-group-header') {
    echo '<tr>';
    echo '<td class="mtrn-group-header-wrapper" colspan="2">';
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
   * Render a conditional checkbox field that shows/hides based on tournament JSON data
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param mixed $value The field value (1 for checked, 0 for unchecked)
   * @param string $description Optional. Field description text
   * @param string $tournament_id The tournament ID to check
   * @param string $json_field The JSON field to check (e.g., 'showCourts')
   */
  public static function render_conditional_checkbox_field($field_name, $label, $value, $description, $tournament_id, $json_field) {
    $field_row_id = $field_name . '_row';
    $show_field = false;

    // Check if the JSON field is true
    if (!empty($tournament_id)) {
      $json_value = self::fetch_tournament_option($tournament_id, $json_field);
      $show_field = ($json_value === true);
    }

    $row_classes = 'mtrn-conditional-field';
    if (!$show_field) {
      $row_classes .= ' mtrn-field-hidden';
    }

    echo '<tr id="' . esc_attr($field_row_id) . '" class="' . esc_attr($row_classes) . '" data-condition-field="' . esc_attr($json_field) . '" data-tournament-id="' . esc_attr($tournament_id) . '" data-show-field="' . esc_attr($show_field ? '1' : '0') . '">';
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
    echo '<input type="text" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" value="#' . esc_attr($value) . '" class="mtrn-color-picker" />';
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
    echo '<input type="text" id="' . esc_attr($color_field) . '" name="' . esc_attr($color_field) . '" value="#' . esc_attr($color_value) . '" class="mtrn-color-picker" style="width: 120px;" />';
    echo '<div style="display: flex; align-items: center; gap: 8px;">';
    echo '<label for="' . esc_attr($opacity_field) . '" style="margin: 0; font-weight: normal;">' . esc_html__('Opacity:', 'meinturnierplan') . '</label>';
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
    if (in_array($field, array('font_size', 'header_font_size', 'bsizeh', 'bsizev', 'bsizeoh', 'bsizeov', 'bbsize', 'table_padding', 'inner_padding', 'logo_size'))) {
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
   * Combine hex color and opacity percentage into 8-character hex
   *
   * @param string $hex_color The color value (with or without #)
   * @param int $opacity_percent The opacity value (0-100)
   * @return string The combined color value with opacity
   */
  public static function combine_color_opacity($hex_color, $opacity_percent) {
    // Remove # if present
    $hex_color = ltrim($hex_color, '#');

    // If color already has alpha (8 characters), return as is
    if (strlen($hex_color) == 8) {
      return $hex_color;
    }

    // If no opacity specified, default to fully opaque
    if ($opacity_percent === '' || $opacity_percent === null) {
      $opacity_percent = 100;
    }

    // Convert opacity percentage to hex
    $opacity_hex = str_pad(dechex(round(($opacity_percent / 100) * 255)), 2, '0', STR_PAD_LEFT);

    return $hex_color . $opacity_hex;
  }

  /**
   * Get background color with opacity from post meta
   *
   * @param int $post_id The post ID
   * @param string $color_meta_key The meta key for the color field
   * @return string The combined color value with opacity
   */
  public static function get_bg_color_with_opacity($post_id, $color_meta_key) {
    if (!$post_id) {
      return '00000000'; // Transparent default
    }

    $bg_color = get_post_meta($post_id, $color_meta_key, true);

    // Determine opacity meta key based on color meta key
    $opacity_meta_mapping = array(
      '_mtrn_bg_color' => '_mtrn_bg_opacity',
      '_mtrn_even_bg_color' => '_mtrn_even_bg_opacity',
      '_mtrn_odd_bg_color' => '_mtrn_odd_bg_opacity',
      '_mtrn_hover_bg_color' => '_mtrn_hover_bg_opacity',
      '_mtrn_head_bg_color' => '_mtrn_head_bg_opacity',
    );

    $opacity_meta_key = isset($opacity_meta_mapping[$color_meta_key]) ? $opacity_meta_mapping[$color_meta_key] : null;
    $bg_opacity = $opacity_meta_key ? get_post_meta($post_id, $opacity_meta_key, true) : null;

    // Set defaults
    if (empty($bg_color)) {
      $bg_color = '000000'; // Default color
    }

    if (empty($bg_opacity) && $bg_opacity !== '0') {
      $bg_opacity = $color_meta_key === '_mtrn_bg_color' ? 0 : 69; // Different defaults for different colors
    }

    return self::combine_color_opacity($bg_color, $bg_opacity);
  }

  /**
   * Get available language options for the plugin
   *
   * @return array Array of language code => language name pairs
   */
  public static function get_language_options() {
    return array(
      'en' => __('English', 'meinturnierplan'),
      'de' => __('Deutsch / German', 'meinturnierplan'),
      'es' => __('Español / Spanish', 'meinturnierplan'),
      'fr' => __('Français / French', 'meinturnierplan'),
      'hr' => __('Hrvatski / Croatian', 'meinturnierplan'),
      'it' => __('Italiano / Italian', 'meinturnierplan'),
      'pl' => __('Polski / Polish', 'meinturnierplan'),
      'sl' => __('Slovenščina / Slovenian', 'meinturnierplan'),
      'tr' => __('Türkçe / Turkish', 'meinturnierplan'),
    );
  }

  /**
   * Convert hex color with alpha to rgba
   *
   * @param string $hex The hex color value (with or without #)
   * @return string The converted color in rgba format or 'transparent'
   */
  public static function hex_to_rgba($hex) {
    // Remove # if present
    $hex = ltrim($hex, '#');

    // Handle 8-character hex (RRGGBBAA)
    if (strlen($hex) == 8) {
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
      $a = round(hexdec(substr($hex, 6, 2)) / 255, 2);
      return "rgba($r, $g, $b, $a)";
    }
    // Handle 6-character hex (RRGGBB)
    elseif (strlen($hex) == 6) {
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
      return "rgb($r, $g, $b)";
    }
    // Handle 3-character hex (RGB)
    elseif (strlen($hex) == 3) {
      $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
      $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
      $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
      return "rgb($r, $g, $b)";
    }

    return 'transparent';
  }

  /**
   * Get default language based on WordPress locale
   *
   * @return string The default language code
   */
  public static function get_default_language() {
    // First, try to get the current user's language preference
    $user_locale = '';
    if (is_user_logged_in()) {
      $user_id = get_current_user_id();
      $user_locale = get_user_meta($user_id, 'locale', true);
    }

    // Use user locale if available, otherwise fall back to site locale
    $wp_locale = !empty($user_locale) ? $user_locale : get_locale();

    // Define supported languages with their WordPress locale mappings
    $supported_languages = array(
      'en' => array('en_US', 'en_GB', 'en_CA', 'en_AU', 'en_NZ', 'en_ZA'),
      'de' => array('de_DE', 'de_AT', 'de_CH', 'de_DE_formal'),
      'es' => array('es_ES', 'es_MX', 'es_AR', 'es_CL', 'es_CO', 'es_PE', 'es_VE'),
      'fr' => array('fr_FR', 'fr_BE', 'fr_CA', 'fr_CH'),
      'hr' => array('hr', 'hr_HR'),
      'it' => array('it_IT'),
      'pl' => array('pl_PL'),
      'sl' => array('sl_SI'),
      'tr' => array('tr_TR'),
    );

    // Check if current locale matches any supported language
    foreach ($supported_languages as $lang_code => $locales) {
      if (in_array($wp_locale, $locales)) {
        return $lang_code;
      }
    }

    // Check for partial matches (e.g., 'de' from 'de_DE_formal')
    $wp_lang_code = substr($wp_locale, 0, 2);
    if (array_key_exists($wp_lang_code, $supported_languages)) {
      return $wp_lang_code;
    }

    // Default to English if no match found
    return 'en';
  }

  /**
   * Fetch tournament groups from external API
   *
   * @param string $tournament_id The tournament ID
   * @param bool $force_refresh Whether to force refresh the cache
   * @return array Array containing groups and hasFinalRound data
   */
  public static function fetch_tournament_groups($tournament_id, $force_refresh = false) {
    if (empty($tournament_id)) {
      return array('groups' => array(), 'hasFinalRound' => false);
    }

    $cache_key = 'mtrn_groups_' . $tournament_id;
    $cache_expiry = 15 * MINUTE_IN_SECONDS; // Cache for 15 minutes

    // Try to get cached data first (unless force refresh is requested)
    if (!$force_refresh) {
      $cached_data = get_transient($cache_key);
      if ($cached_data !== false) {
        // Handle backwards compatibility - if cached data is old format (just array of groups)
        if (is_array($cached_data) && !isset($cached_data['groups'])) {
          // Old format - convert to new format
          return array(
            'groups' => $cached_data,
            'hasFinalRound' => false
          );
        }
        return $cached_data;
      }
    } else {
      // Force refresh - clear the cache first
      delete_transient($cache_key);
    }

    // Use WordPress HTTP API to fetch the JSON
    $url = 'https://www.meinturnierplan.de/json/json.php?id=' . urlencode($tournament_id);
    $response = wp_remote_get($url, array(
      'timeout' => 10,
      'sslverify' => true
    ));

    // Check for errors
    if (is_wp_error($response)) {
      // Return cached data if available, even if expired
      $cached_data = get_transient($cache_key);
      if ($cached_data !== false) {
        // Handle backwards compatibility
        if (is_array($cached_data) && !isset($cached_data['groups'])) {
          return array(
            'groups' => $cached_data,
            'hasFinalRound' => false
          );
        }
        return $cached_data;
      }
      return array('groups' => array(), 'hasFinalRound' => false);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $groups = array();
    $has_final_round = false;

    // Check if groups exist and are not empty
    if (isset($data['groups']) && is_array($data['groups']) && !empty($data['groups'])) {
      $groups = $data['groups'];
    }

    // Check if finalRankTable exists and has valid final ranking data
    if (isset($data['finalRankTable']) && is_array($data['finalRankTable']) && count($data['finalRankTable']) > 0) {
      // Verify it contains valid ranking objects with rank and teamId
      $first_entry = $data['finalRankTable'][0];
      if (is_array($first_entry) && isset($first_entry['rank']) && isset($first_entry['teamId'])) {
        $has_final_round = true;
      }
    }

    // Cache the result (even if empty)
    $result = array(
      'groups' => $groups,
      'hasFinalRound' => $has_final_round
    );
    set_transient($cache_key, $result, $cache_expiry);

    return $result;
  }

  /**
   * Fetch tournament teams from external API
   *
   * @param string $tournament_id The tournament ID
   * @param bool $force_refresh Whether to force refresh the cache
   * @return array Array of teams
   */
  public static function fetch_tournament_teams($tournament_id, $force_refresh = false) {
    if (empty($tournament_id)) {
      return array();
    }

    $cache_key = 'mtrn_teams_' . $tournament_id;
    $cache_expiry = 15 * MINUTE_IN_SECONDS; // Cache for 15 minutes

    // Try to get cached data first (unless force refresh is requested)
    if (!$force_refresh) {
      $cached_data = get_transient($cache_key);
      if ($cached_data !== false) {
        return $cached_data;
      }
    } else {
      // Force refresh - clear the cache first
      delete_transient($cache_key);
    }

    // Use WordPress HTTP API to fetch the JSON
    $url = 'https://www.meinturnierplan.de/json/json.php?id=' . urlencode($tournament_id);
    $response = wp_remote_get($url, array(
      'timeout' => 10,
      'sslverify' => true
    ));

    // Check for errors
    if (is_wp_error($response)) {
      // Return cached data if available, even if expired
      $cached_data = get_transient($cache_key);
      if ($cached_data !== false) {
        return $cached_data;
      }
      return array();
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $teams = array();

    // Check if teams exist and are not empty
    if (isset($data['teams']) && is_array($data['teams']) && !empty($data['teams'])) {
      $teams = $data['teams'];
    }

    // Cache the result (even if empty)
    set_transient($cache_key, $teams, $cache_expiry);

    return $teams;
  }

  /**
   * Fetch tournament data to check for specific options like showCourts
   *
   * @param string $tournament_id The tournament ID
   * @param string $option_name The option to check (e.g., 'showCourts')
   * @return mixed The value of the option or null if not found
   */
  public static function fetch_tournament_option($tournament_id, $option_name) {
    if (empty($tournament_id) || empty($option_name)) {
      return null;
    }

    $cache_key = 'mtrn_data_' . $tournament_id;
    $cache_expiry = 15 * MINUTE_IN_SECONDS; // Cache for 15 minutes

    // Try to get cached data first
    $cached_data = get_transient($cache_key);
    if ($cached_data === false) {
      // Use WordPress HTTP API to fetch the JSON
      $url = 'https://www.meinturnierplan.de/json/json.php?id=' . urlencode($tournament_id);
      $response = wp_remote_get($url, array(
        'timeout' => 10,
        'sslverify' => true
      ));

      // Check for errors
      if (is_wp_error($response)) {
        return null;
      }

      $body = wp_remote_retrieve_body($response);
      $cached_data = json_decode($body, true);

      // Cache the result
      if ($cached_data) {
        set_transient($cache_key, $cached_data, $cache_expiry);
      }
    }

    // Return the specific option value if it exists
    if (is_array($cached_data) && isset($cached_data[$option_name])) {
      return $cached_data[$option_name];
    }

    return null;
  }

  /**
   * Render JavaScript utilities for admin forms
   *
   * This enqueues the admin utilities JavaScript file and localizes configuration data.
   *
   * @param array $config Optional configuration array with keys:
   *   - 'nonce_action': The nonce action name for AJAX calls (default: 'mtrn_preview_nonce')
   *   - 'ajax_actions': Array of AJAX action names (default: ['mtrn_get_groups', 'mtrn_refresh_groups'])
   *   - 'ajax_actions_teams': Array of AJAX action names for teams (default: ['mtrn_get_teams', 'mtrn_refresh_teams'])
   *   - 'field_prefix': Prefix for field IDs (default: 'mtrn_')
   */
  public static function render_admin_javascript_utilities($config = array()) {
    $defaults = array(
      'nonce_action' => 'mtrn_preview_nonce',
      'ajax_actions' => array('mtrn_get_groups', 'mtrn_refresh_groups'),
      'ajax_actions_teams' => array('mtrn_get_teams', 'mtrn_refresh_teams'),
      'field_prefix' => 'mtrn_'
    );
    $config = array_merge($defaults, $config);
    
    // Enqueue the admin utilities script
    wp_enqueue_script(
      'mtrn-admin-utilities',
      plugins_url('assets/js/admin-utilities.js', dirname(__FILE__)),
      array('jquery', 'wp-color-picker'),
      '1.0.0',
      true
    );
    
    // Localize script with configuration and i18n strings
    wp_localize_script('mtrn-admin-utilities', 'mtrnAdminUtilsConfig', array(
      'fieldPrefix' => $config['field_prefix'],
      'ajaxActions' => $config['ajax_actions'],
      'ajaxActionsTeams' => $config['ajax_actions_teams'],
      'nonce' => wp_create_nonce($config['nonce_action']),
      'i18n' => array(
        'noGroupsAvailable' => __('No groups available', 'meinturnierplan'),
        'refreshingGroups' => __('Refreshing groups...', 'meinturnierplan'),
        'loadingGroups' => __('Loading groups...', 'meinturnierplan'),
        'allMatches' => __('All Matches', 'meinturnierplan'),
        'finalRound' => __('Final Round', 'meinturnierplan'),
        'groupsRefreshed' => __('Groups refreshed successfully!', 'meinturnierplan'),
        'default' => __('Default', 'meinturnierplan'),
        'group' => __('Group', 'meinturnierplan'),
        'saved' => __('(saved)', 'meinturnierplan'),
        'noGroupsFound' => __('No groups found for this tournament.', 'meinturnierplan'),
        'finalRoundSaved' => __('Final Round (saved)', 'meinturnierplan'),
        'errorRefreshing' => __('Error refreshing groups. Please try again.', 'meinturnierplan'),
        'all' => __('All', 'meinturnierplan'),
        'refreshingParticipants' => __('Refreshing participants...', 'meinturnierplan'),
        'loadingParticipants' => __('Loading participants...', 'meinturnierplan'),
        'participantsRefreshed' => __('Participants refreshed successfully!', 'meinturnierplan'),
        'team' => __('Team', 'meinturnierplan'),
        'noParticipantsFound' => __('No participants found for this tournament.', 'meinturnierplan'),
        'errorRefreshingParticipants' => __('Error refreshing participants. Please try again.', 'meinturnierplan'),
      )
    ));
  }

  /**
   * Render shortcode generator interface
   *
   * This creates a reusable shortcode generator UI with copy functionality,
   * success messaging, and optional warning messages.
   *
   * @param string $shortcode The generated shortcode string
   * @param string $tournament_id Optional. Tournament ID for validation messaging
   * @param array $config Optional configuration array with keys:
   *   - 'field_id': ID for the textarea field (default: 'mtrn_shortcode_field')
   *   - 'copy_button_id': ID for the copy button (default: 'mtrn_copy_shortcode')
   *   - 'success_message_id': ID for success message (default: 'mtrn_copy_success')
   *   - 'field_prefix': Prefix for field selectors (default: 'mtrn_')
   *   - 'shortcode_updater_callback': Name of JavaScript function to call for live updates
   */
  public static function render_shortcode_generator($shortcode, $tournament_id = '', $config = array()) {
    $defaults = array(
      'field_id' => 'mtrn_shortcode_field',
      'copy_button_id' => 'mtrn_copy_shortcode',
      'success_message_id' => 'mtrn_copy_success',
      'field_prefix' => 'mtrn_',
      'shortcode_updater_callback' => 'updateShortcode'
    );
    $config = array_merge($defaults, $config);

    echo '<div class="mtrn-generated-shortcode-wrapper">';
    echo '<label class="mtrn-generated-shortcode__label" for="' . esc_attr($config['field_id']) . '">' . esc_html__('Generated Shortcode:', 'meinturnierplan') . '</label>';
    echo '<textarea class="mtrn-generated-shortcode__field" id="' . esc_attr($config['field_id']) . '" readonly>' . esc_textarea($shortcode) . '</textarea>';
    echo '</div>';

    echo '<button type="button" id="' . esc_attr($config['copy_button_id']) . '" class="mtrn-generated-shortcode__btn button button-secondary">';
    echo '<span class="mtrn-generated-shortcode__btn-icon dashicons dashicons-admin-page"></span>';
    echo esc_html__('Copy Shortcode', 'meinturnierplan');
    echo '</button>';

    echo '<div id="' . esc_attr($config['success_message_id']) . '" class="mtrn-generated-shortcode__copy-success" style="display: none;">';
    echo '<span class="mtrn-generated-shortcode__copy-success-icon dashicons dashicons-yes-alt"></span> ';
    echo esc_html__('Shortcode copied to clipboard!', 'meinturnierplan');
    echo '</div>';

    if (empty($tournament_id)) {
      echo '<div class="mtrn-generated-shortcode__message mtrn-generated-shortcode__message--warning">';
      echo '<strong>' . esc_html__('Note:', 'meinturnierplan') . '</strong> ';
      echo esc_html__('Enter a Tournament ID above to display live tournament data.', 'meinturnierplan');
      echo '</div>';
    }

    // Add reusable JavaScript for copy functionality and field listeners
    self::render_shortcode_javascript($config);
  }

  /**
   * Render JavaScript for shortcode generator functionality
   *
   * This enqueues the shortcode generator JavaScript file and localizes configuration.
   *
   * @param array $config Configuration array from render_shortcode_generator
   */
  public static function render_shortcode_javascript($config) {
    // Enqueue the shortcode generator script
    wp_enqueue_script(
      'mtrn-shortcode-generator',
      plugins_url('assets/js/shortcode-generator.js', dirname(__FILE__)),
      array('jquery'),
      '1.0.0',
      true
    );
    
    // Localize script with configuration
    wp_localize_script('mtrn-shortcode-generator', 'mtrnShortcodeConfig', array(
      'fieldId' => $config['field_id'],
      'copyButtonId' => $config['copy_button_id'],
      'successMessageId' => $config['success_message_id'],
      'fieldPrefix' => $config['field_prefix'],
      'updateCallback' => $config['shortcode_updater_callback']
    ));
  }

  /**
   * Render conditional group field for tournament selection
   *
   * @param array $meta_values Array containing tournament_id and group values
   * @param string $field_prefix Optional. Prefix for field names. Default 'mtrn_'.
   * @param string $context Optional. Context: 'matches' or 'tables'. Default 'tables'. Only 'matches' shows "All Matches" option.
   */
  public static function render_conditional_group_field($meta_values, $field_prefix = 'mtrn_', $context = 'tables') {
    $tournament_id = $meta_values['tournament_id'];
    $saved_group = $meta_values['group'];
    $groups = array();
    $has_final_round = false;
    $show_all_option = ($context === 'matches'); // Only show "All Matches" for matches context

    // Only fetch groups if tournament ID is provided
    if (!empty($tournament_id)) {
      $tournament_data = self::fetch_tournament_groups($tournament_id);
      $groups = $tournament_data['groups'];
      $has_final_round = $tournament_data['hasFinalRound'];
    }

    $group_field_id = $field_prefix . 'group';
    $refresh_button_id = $field_prefix . 'refresh_groups';
    $group_field_row_id = $field_prefix . 'group_field_row';
    $saved_value_field_id = $field_prefix . 'group_saved_value';

    // Always render the field, but populate it based on available groups
    $row_classes = '';
    if (empty($tournament_id)) {
      $row_classes = ' mtrn-field-hidden';
    }

    echo '<tr id="' . esc_attr($group_field_row_id) . '"' . (!empty($row_classes) ? ' class="' . esc_attr(trim($row_classes)) . '"' : '') . '>';
    echo '<th scope="row"><label for="' . esc_attr($group_field_id) . '">' . esc_html__('Group', 'meinturnierplan') . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 10px;">';
    echo '<select id="' . esc_attr($group_field_id) . '" name="' . esc_attr($group_field_id) . '" class="regular-text">';

    if (!empty($groups)) {
      // Add "All Matches" option first as default (only for matches context)
      if ($show_all_option) {
        $is_all_selected = empty($saved_group);
        echo '<option value=""' . selected($is_all_selected, true, false) . '>' . esc_html__('All Matches', 'meinturnierplan') . '</option>';
      }

      // Populate with actual groups
      foreach ($groups as $index => $group) {
        $group_number = $index + 1;
        $is_selected = false;

        if (!empty($saved_group)) {
          // Use saved selection
          $is_selected = ($saved_group == $group_number);
        } else if (!$show_all_option && $index == 0) {
          // Auto-select first group as default for tables (when no "All Matches" option)
          $is_selected = true;
        }

        /* translators: %s is the group display name */
        echo '<option value="' . esc_attr($group_number) . '"' . selected($is_selected, true, false) . '>' . esc_html(sprintf(__('Group %s', 'meinturnierplan'), $group['displayId'])) . '</option>';
      }

      // Add Final Round option if it exists
      if ($has_final_round) {
        $is_final_selected = (!empty($saved_group) && $saved_group == '90');
        echo '<option value="90"' . selected($is_final_selected, true, false) . '>' . esc_html__('Final Round', 'meinturnierplan') . '</option>';
      }
    } else if (!empty($saved_group) && !empty($tournament_id)) {
      // Add "All Matches" option first (only for matches context)
      if ($show_all_option) {
        echo '<option value="">' . esc_html__('All Matches', 'meinturnierplan') . '</option>';
      }

      // Show a placeholder for the saved group if groups haven't loaded yet
      if ($saved_group == '90') {
        echo '<option value="90" selected>' . esc_html__('Final Round (saved)', 'meinturnierplan') . '</option>';
      } else {
        /* translators: %s is the saved group name */
        echo '<option value="' . esc_attr($saved_group) . '" selected>' . esc_html(sprintf(__('Group %s (saved)', 'meinturnierplan'), $saved_group)) . '</option>';
      }
    } else {
      // For matches: Add "All Matches" option as default
      // For tables: Show "Default" option
      if ($show_all_option) {
        echo '<option value="" selected>' . esc_html__('All Matches', 'meinturnierplan') . '</option>';
      } else {
        echo '<option value="">' . esc_html__('Default', 'meinturnierplan') . '</option>';
      }

      // Check for Final Round only
      if ($has_final_round) {
        $is_final_selected = (!empty($saved_group) && $saved_group == '90');
        echo '<option value="90"' . selected($is_final_selected, true, false) . '>' . esc_html__('Final Round', 'meinturnierplan') . '</option>';
      }
    }

    echo '</select>';
    echo '<button type="button" id="' . esc_attr($refresh_button_id) . '" class="button button-secondary" title="' . esc_attr(__('Refresh Groups', 'meinturnierplan')) . '">';
    echo '<span class="dashicons dashicons-update-alt" style="vertical-align: middle;"></span>';
    echo '</button>';
    echo '</div>';

    // Static description that covers all scenarios
    echo '<p class="description">' . esc_html__('Select a group to display from this tournament. Click refresh to update groups from server. Please note that some tournaments do not have groups.', 'meinturnierplan') . '</p>';

    // Add hidden field to store the initially saved value for JavaScript
    echo '<input type="hidden" id="' . esc_attr($saved_value_field_id) . '" value="' . esc_attr($saved_group) . '" />';

    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render conditional participant field for tournament selection
   *
   * @param array $meta_values Array containing tournament_id and participant values
   * @param string $field_prefix Optional. Prefix for field names. Default 'mtrn_'.
   */
  public static function render_conditional_participant_field($meta_values, $field_prefix = 'mtrn_') {
    $tournament_id = $meta_values['tournament_id'];
    $saved_participant = isset($meta_values['participant']) ? $meta_values['participant'] : '-1';
    $teams = array();

    // Only fetch teams if tournament ID is provided
    if (!empty($tournament_id)) {
      $teams = self::fetch_tournament_teams($tournament_id);
    }

    $participant_field_id = $field_prefix . 'participant';
    $refresh_button_id = $field_prefix . 'refresh_participants';
    $participant_field_row_id = $field_prefix . 'participant_field_row';
    $saved_value_field_id = $field_prefix . 'participant_saved_value';

    // Always render the field (unlike Group, this is always displayed)
    echo '<tr id="' . esc_attr($participant_field_row_id) . '">';
    echo '<th scope="row"><label for="' . esc_attr($participant_field_id) . '">' . esc_html__('Participant', 'meinturnierplan') . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 10px;">';
    echo '<select id="' . esc_attr($participant_field_id) . '" name="' . esc_attr($participant_field_id) . '" class="regular-text">';

    // Add "All" option first as default
    $is_all_selected = ($saved_participant == '-1');
    echo '<option value="-1"' . selected($is_all_selected, true, false) . '>' . esc_html__('All', 'meinturnierplan') . '</option>';

    if (!empty($teams)) {
      // Populate with actual teams
      foreach ($teams as $team) {
        $team_id = isset($team['displayId']) ? $team['displayId'] : '';
        $team_name = isset($team['name']) ? $team['name'] : '';

        if (!empty($team_id) && !empty($team_name)) {
          $is_selected = ($saved_participant == $team_id);
          echo '<option value="' . esc_attr($team_id) . '"' . selected($is_selected, true, false) . '>' . esc_html($team_name) . '</option>';
        }
      }
    } else if (!empty($saved_participant) && $saved_participant != '-1' && !empty($tournament_id)) {
      // Show a placeholder for the saved participant if teams haven't loaded yet
      /* translators: %s is the saved team name */
      echo '<option value="' . esc_attr($saved_participant) . '" selected>' . esc_html(sprintf(__('Team %s (saved)', 'meinturnierplan'), $saved_participant)) . '</option>';
    }

    echo '</select>';
    echo '<button type="button" id="' . esc_attr($refresh_button_id) . '" class="button button-secondary" title="' . esc_attr(__('Refresh Participants', 'meinturnierplan')) . '">';
    echo '<span class="dashicons dashicons-update-alt" style="vertical-align: middle;"></span>';
    echo '</button>';
    echo '</div>';

    // Description
    echo '<p class="description">' . esc_html__('Select a participant (team) to filter matches. Select "All" to display matches for all participants. Click refresh to update participants from server.', 'meinturnierplan') . '</p>';

    // Add hidden field to store the initially saved value for JavaScript
    echo '<input type="hidden" id="' . esc_attr($saved_value_field_id) . '" value="' . esc_attr($saved_participant) . '" />';

    echo '</td>';
    echo '</tr>';
  }
}
