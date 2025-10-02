<?php
/**
 * Admin Meta Boxes Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Admin Meta Boxes Class
 */
class MTP_Admin_Meta_Boxes {
  
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
   * Initialize meta boxes
   */
  public function init() {
    add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
    add_action('save_post', array($this, 'save_meta_boxes'));
  }
  
  /**
   * Add meta boxes
   */
  public function add_meta_boxes() {
    add_meta_box(
      'mtp_table_settings',
      __('Table Settings & Preview', 'meinturnierplan-wp'),
      array($this, 'meta_box_callback'),
      'mtp_table',
      'normal',
      'high'
    );
    
    add_meta_box(
      'mtp_table_shortcode',
      __('Shortcode Generator', 'meinturnierplan-wp'),
      array($this, 'shortcode_meta_box_callback'),
      'mtp_table',
      'side',
      'high'
    );
  }
  
  /**
   * Meta box callback
   */
  public function meta_box_callback($post) {
    // Add nonce for security
    wp_nonce_field('mtp_table_meta_box', 'mtp_table_meta_box_nonce');
    
    // Get current values with defaults
    $meta_values = $this->get_meta_values($post->ID);
    
    // Start two-column layout
    echo '<div class="mtp-admin-two-column-layout">';
    
    // Left column - Table Settings
    echo '<div class="mtp-admin-column mtp-admin-column-left">';
    echo '<h3>' . __('Table Settings', 'meinturnierplan-wp') . '</h3>';
    $this->render_settings_form($meta_values);
    echo '</div>';
    
    // Right column - Preview
    echo '<div class="mtp-admin-column mtp-admin-column-right">';
    $this->render_preview_section($post, $meta_values);
    echo '</div>';
    
    // Clear floats
    echo '<div class="mtp-admin-clear"></div>';
    echo '</div>';
    
    // Add JavaScript for live preview
    $this->add_preview_javascript($post->ID);
  }
  
  /**
   * Get meta values with defaults
   */
  private function get_meta_values($post_id) {
    $defaults = array(
      'tournament_id' => '',
      'width' => '300',
      'height' => '152',
      'font_size' => '9',
      'header_font_size' => '10',
      'table_padding' => '2',
      'inner_padding' => '5',
      'text_color' => '000000',
      'main_color' => '173f75',
      'bg_color' => '000000',
      'bg_opacity' => '0',
      'border_color' => 'bbbbbb',
      'head_bottom_border_color' => 'bbbbbb',
      'even_bg_color' => 'f0f8ff',
      'even_bg_opacity' => '69',
      'odd_bg_color' => 'ffffff',
      'odd_bg_opacity' => '69',
      'hover_bg_color' => 'eeeeff',
      'hover_bg_opacity' => '69',
      'head_bg_color' => 'eeeeff',
      'head_bg_opacity' => '100',
      'logo_size' => '20',
      'bsizeh' => '1',
      'bsizev' => '1',
      'bsizeoh' => '1',
      'bsizeov' => '1',
      'bbsize' => '2',
      'suppress_wins' => '0',
      'suppress_logos' => '0',
      'suppress_num_matches' => '0',
      'projector_presentation' => '0',
      'navigation_for_groups' => '0',
      'language' => $this->get_default_language(),
      'group' => '',
    );
    
    $meta_values = array();
    foreach ($defaults as $key => $default) {
      $meta_key = '_mtp_' . $key;
      $value = get_post_meta($post_id, $meta_key, true);
      $meta_values[$key] = !empty($value) || $value === '0' ? $value : $default;
    }
    
    return $meta_values;
  }
  
  /**
   * Render settings form
   */
  private function render_settings_form($meta_values) {
    echo '<table class="form-table">';
    
    // Basic Settings Group
    $this->render_group_header(__('Basic Settings', 'meinturnierplan-wp'));
    $this->render_text_field('tournament_id', __('Tournament ID', 'meinturnierplan-wp'), $meta_values['tournament_id'], __('Enter the tournament ID from meinturnierplan.de (e.g., 1753883027)', 'meinturnierplan-wp'));
    $this->render_select_field('language', __('Language', 'meinturnierplan-wp'), $meta_values['language'], $this->get_language_options(), __('Select the language for the tournament table display.', 'meinturnierplan-wp'));
    $this->render_conditional_group_field($meta_values);
    
    // Dimensions Group
    $this->render_group_header(__('Dimensions', 'meinturnierplan-wp'));
    $this->render_number_field('width', __('Table Width (px)', 'meinturnierplan-wp'), $meta_values['width'], __('Set the width of the tournament table in pixels.', 'meinturnierplan-wp'), 100, 2000);
    $this->render_number_field('height', __('Table Height (px)', 'meinturnierplan-wp'), $meta_values['height'], __('Set the height of the tournament table in pixels.', 'meinturnierplan-wp'), 100, 2000);

    // Display Options Group
    $this->render_group_header(__('Display Options', 'meinturnierplan-wp'));
    $this->render_checkbox_field('suppress_wins', __('Suppress Num Wins, Losses, etc.', 'meinturnierplan-wp'), $meta_values['suppress_wins'], __('Hide the number of wins, losses, and other statistical columns from the tournament table.', 'meinturnierplan-wp'));
    $this->render_checkbox_field('suppress_logos', __('Suppress Logos', 'meinturnierplan-wp'), $meta_values['suppress_logos'], __('Hide the logos from the tournament table.', 'meinturnierplan-wp'));
    $this->render_checkbox_field('suppress_num_matches', __('Suppress Num Matches', 'meinturnierplan-wp'), $meta_values['suppress_num_matches'], __('Hide the number of matches from the tournament table.', 'meinturnierplan-wp'));
    $this->render_checkbox_field('projector_presentation', __('Projector Presentation', 'meinturnierplan-wp'), $meta_values['projector_presentation'], __('Enable projector presentation mode for the tournament table.', 'meinturnierplan-wp'));
    $this->render_checkbox_field('navigation_for_groups', __('Navigation for Groups', 'meinturnierplan-wp'), $meta_values['navigation_for_groups'], __('Enable navigation for groups in the tournament table.', 'meinturnierplan-wp'));
    
    // Typography Group
    $this->render_group_header(__('Typography', 'meinturnierplan-wp'));
    $this->render_number_field('font_size', __('Content Font Size (pt)', 'meinturnierplan-wp'), $meta_values['font_size'], __('Set the font size of the tournament table content. 9pt is the default value.', 'meinturnierplan-wp'), 6, 24);
    $this->render_number_field('header_font_size', __('Header Font Size (pt)', 'meinturnierplan-wp'), $meta_values['header_font_size'], __('Set the font size of the tournament table headers. 10pt is the default value.', 'meinturnierplan-wp'), 6, 24);
    $this->render_number_field('logo_size', __('Logo Size (pt)', 'meinturnierplan-wp'), $meta_values['logo_size'], __('Set the font size of the tournament table logo. 20pt is the default value.', 'meinturnierplan-wp'), 6, 24);
    
    // Spacing & Layout Group
    $this->render_group_header(__('Spacing & Layout', 'meinturnierplan-wp'));
    $this->render_number_field('table_padding', __('Table Padding (px)', 'meinturnierplan-wp'), $meta_values['table_padding'], __('Set the padding around the tournament table. 2px is the default value.', 'meinturnierplan-wp'), 0, 50);
    $this->render_number_field('inner_padding', __('Inner Padding (px)', 'meinturnierplan-wp'), $meta_values['inner_padding'], __('Set the padding inside the tournament table cells. 5px is the default value.', 'meinturnierplan-wp'), 0, 20);
    
    // Border Settings Group
    $this->render_group_header(__('Border Settings', 'meinturnierplan-wp'));
    $this->render_number_field('bsizeh', __('Border Vertical Size (px)', 'meinturnierplan-wp'), $meta_values['bsizeh'], __('Set the border vertical size of the tournament table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bsizev', __('Border Horizontal Size (px)', 'meinturnierplan-wp'), $meta_values['bsizev'], __('Set the border horizontal size of the tournament table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bsizeoh', __('Table Block Border Size (px)', 'meinturnierplan-wp'), $meta_values['bsizeoh'], __('Set the block border size of the tournament table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bsizeov', __('Table Inline Border Size (px)', 'meinturnierplan-wp'), $meta_values['bsizeov'], __('Set the inline border size of the tournament table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bbsize', __('Table Head Border Bottom Size (px)', 'meinturnierplan-wp'), $meta_values['bbsize'], __('Set the head border bottom size of the tournament table. 2px is the default value.', 'meinturnierplan-wp'), 1, 10);
    
    // Colors Group
    $this->render_group_header(__('Colors', 'meinturnierplan-wp'));
    $this->render_color_field('text_color', __('Text Color', 'meinturnierplan-wp'), $meta_values['text_color'], __('Set the color of the tournament table text. Black (#000000) is the default value.', 'meinturnierplan-wp'));
    $this->render_color_field('main_color', __('Main Color', 'meinturnierplan-wp'), $meta_values['main_color'], __('Set the main color of the tournament table (headers, highlights). Blue (#173f75) is the default value.', 'meinturnierplan-wp'));
    $this->render_color_field('border_color', __('Border Color', 'meinturnierplan-wp'), $meta_values['border_color'], __('Set the border color of the tournament table. Light gray (#bbbbbb) is the default value.', 'meinturnierplan-wp'));
    $this->render_color_field('head_bottom_border_color', __('Table Head Bottom Border Color', 'meinturnierplan-wp'), $meta_values['head_bottom_border_color'], __('Set the bottom border color of the table header. Light gray (#bbbbbb) is the default value.', 'meinturnierplan-wp'));
    
    // Background Colors Group
    $this->render_group_header(__('Background Colors', 'meinturnierplan-wp'));
    $this->render_color_opacity_field('bg_color', 'bg_opacity', __('Background Color', 'meinturnierplan-wp'), $meta_values['bg_color'], $meta_values['bg_opacity'], __('Set the background color and opacity of the tournament table. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    $this->render_color_opacity_field('head_bg_color', 'head_bg_opacity', __('Head Background Color', 'meinturnierplan-wp'), $meta_values['head_bg_color'], $meta_values['head_bg_opacity'], __('Set the background color and opacity for table head. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    $this->render_color_opacity_field('even_bg_color', 'even_bg_opacity', __('Even Rows Background Color', 'meinturnierplan-wp'), $meta_values['even_bg_color'], $meta_values['even_bg_opacity'], __('Set the background color and opacity for even-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    $this->render_color_opacity_field('odd_bg_color', 'odd_bg_opacity', __('Odd Rows Background Color', 'meinturnierplan-wp'), $meta_values['odd_bg_color'], $meta_values['odd_bg_opacity'], __('Set the background color and opacity for odd-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    $this->render_color_opacity_field('hover_bg_color', 'hover_bg_opacity', __('Row Hover Background Color', 'meinturnierplan-wp'), $meta_values['hover_bg_color'], $meta_values['hover_bg_opacity'], __('Set the background color and opacity for table rows hover. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    
    echo '</table>';
  }
  
  /**
   * Render group header
   */
  private function render_group_header($title) {
    echo '<tr>';
    echo '<td colspan="2" style="padding: 0;">';
    echo '<h4 style="margin: 20px 0 10px 0; padding: 8px 0; font-size: 14px; font-weight: 600; color: #23282d; border-bottom: 1px solid #ddd;">' . esc_html($title) . '</h4>';
    echo '</td>';
    echo '</tr>';
  }
  
  /**
   * Render text field
   */
  private function render_text_field($field_name, $label, $value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="mtp_' . esc_attr($field_name) . '" name="mtp_' . esc_attr($field_name) . '" value="' . esc_attr($value) . '" class="regular-text" />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }
  
  /**
   * Render number field
   */
  private function render_number_field($field_name, $label, $value, $description = '', $min = null, $max = null, $step = 1) {
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_' . esc_attr($field_name) . '" name="mtp_' . esc_attr($field_name) . '" value="' . esc_attr($value) . '"';
    if ($min !== null) echo ' min="' . esc_attr($min) . '"';
    if ($max !== null) echo ' max="' . esc_attr($max) . '"';
    echo ' step="' . esc_attr($step) . '" />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }
  
  /**
   * Render color field
   */
  private function render_color_field($field_name, $label, $value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="mtp_' . esc_attr($field_name) . '" name="mtp_' . esc_attr($field_name) . '" value="#' . esc_attr($value) . '" class="mtp-color-picker" />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }
  
  /**
   * Render color field with opacity slider
   */
  private function render_color_opacity_field($color_field, $opacity_field, $label, $color_value, $opacity_value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_' . esc_attr($color_field) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 15px;">';
    echo '<input type="text" id="mtp_' . esc_attr($color_field) . '" name="mtp_' . esc_attr($color_field) . '" value="#' . esc_attr($color_value) . '" class="mtp-color-picker" style="width: 120px;" />';
    echo '<div style="display: flex; align-items: center; gap: 8px;">';
    echo '<label for="mtp_' . esc_attr($opacity_field) . '" style="margin: 0; font-weight: normal;">' . __('Opacity:', 'meinturnierplan-wp') . '</label>';
    echo '<input type="range" id="mtp_' . esc_attr($opacity_field) . '" name="mtp_' . esc_attr($opacity_field) . '" value="' . esc_attr($opacity_value) . '" min="0" max="100" step="1" style="width: 100px;" />';
    echo '<span id="mtp_' . esc_attr($opacity_field) . '_value" style="min-width: 35px; font-size: 12px; color: #666;">' . esc_attr($opacity_value) . '%</span>';
    echo '</div>';
    echo '</div>';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }
  
  /**
   * Render checkbox field
   */
  private function render_checkbox_field($field_name, $label, $value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="checkbox" id="mtp_' . esc_attr($field_name) . '" name="mtp_' . esc_attr($field_name) . '" value="1"' . checked(1, $value, false) . ' />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }
  
  /**
   * Render select field
   */
  private function render_select_field($field_name, $label, $value, $options, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<select id="mtp_' . esc_attr($field_name) . '" name="mtp_' . esc_attr($field_name) . '" class="regular-text">';
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
   * Get available language options
   */
  private function get_language_options() {
    return array(
      'en' => __('English', 'meinturnierplan-wp'),
      'de' => __('Deutsch / German', 'meinturnierplan-wp'),
      'es' => __('Español / Spanish', 'meinturnierplan-wp'),
      'fr' => __('Français / French', 'meinturnierplan-wp'),
      'hr' => __('Hrvatski / Croatian', 'meinturnierplan-wp'),
      'it' => __('Italiano / Italian', 'meinturnierplan-wp'),
      'pl' => __('Polski / Polish', 'meinturnierplan-wp'),
      'sl' => __('Slovenščina / Slovenian', 'meinturnierplan-wp'),
      'tr' => __('Türkçe / Turkish', 'meinturnierplan-wp'),
    );
  }
  
  /**
   * Get default language based on WordPress locale
   */
  private function get_default_language() {
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
   * Render conditional group field
   */
  private function render_conditional_group_field($meta_values) {
    $tournament_id = $meta_values['tournament_id'];
    $saved_group = $meta_values['group'];
    $groups = array();
    $has_final_round = false;
    
    // Only fetch groups if tournament ID is provided
    if (!empty($tournament_id)) {
      $tournament_data = $this->fetch_tournament_groups($tournament_id);
      $groups = $tournament_data['groups'];
      $has_final_round = $tournament_data['hasFinalRound'];
    }
    
    // Always render the field, but populate it based on available groups
    echo '<tr id="mtp_group_field_row">';
    echo '<th scope="row"><label for="mtp_group">' . esc_html(__('Group', 'meinturnierplan-wp')) . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 10px;">';
    echo '<select id="mtp_group" name="mtp_group" class="regular-text">';
    
    if (!empty($groups)) {
      // Populate with actual groups - never show "All Groups" option
      foreach ($groups as $index => $group) {
        $group_number = $index + 1;
        $is_selected = false;
        
        if (!empty($saved_group)) {
          // Use saved selection
          $is_selected = ($saved_group == $group_number);
        } else if ($index == 0) {
          // Auto-select first group as default (both single and multiple group cases)
          $is_selected = true;
        }
        
        $selected = $is_selected ? ' selected' : '';
        echo '<option value="' . esc_attr($group_number) . '"' . $selected . '>' . esc_html(sprintf(__('Group %s', 'meinturnierplan-wp'), $group['displayId'])) . '</option>';
      }
      
      // Add Final Round option if it exists
      if ($has_final_round) {
        $is_final_selected = (!empty($saved_group) && $saved_group == '90');
        $final_selected = $is_final_selected ? ' selected' : '';
        echo '<option value="90"' . $final_selected . '>' . esc_html(__('Final Round', 'meinturnierplan-wp')) . '</option>';
      }
    } else if (!empty($saved_group) && !empty($tournament_id)) {
      // Show a placeholder for the saved group if groups haven't loaded yet
      if ($saved_group == '90') {
        echo '<option value="90" selected>' . esc_html(__('Final Round (saved)', 'meinturnierplan-wp')) . '</option>';
      } else {
        echo '<option value="' . esc_attr($saved_group) . '" selected>' . esc_html(sprintf(__('Group %s (saved)', 'meinturnierplan-wp'), $saved_group)) . '</option>';
      }
    } else {
      // No groups available - check for Final Round only
      if ($has_final_round) {
        $is_final_selected = (!empty($saved_group) && $saved_group == '90');
        $final_selected = $is_final_selected ? ' selected' : '';
        echo '<option value="90"' . $final_selected . '>' . esc_html(__('Final Round', 'meinturnierplan-wp')) . '</option>';
      } else {
        // No groups and no final round - show default option
        echo '<option value="">' . esc_html(__('Default', 'meinturnierplan-wp')) . '</option>';
      }
    }
    
    echo '</select>';
    echo '<button type="button" id="mtp_refresh_groups" class="button button-secondary" title="' . esc_attr(__('Refresh Groups', 'meinturnierplan-wp')) . '">';
    echo '<span class="dashicons dashicons-update-alt" style="vertical-align: middle;"></span>';
    echo '</button>';
    echo '</div>';
    
    // Update description based on group availability
    if (!empty($groups) && count($groups) > 1) {
      echo '<p class="description">' . esc_html(__('Select a specific group to display. Click refresh to update groups from server.', 'meinturnierplan-wp')) . '</p>';
    } else if (!empty($groups) && count($groups) == 1) {
      echo '<p class="description">' . esc_html(__('This tournament has only one group. Click refresh to update groups from server.', 'meinturnierplan-wp')) . '</p>';
    } else {
      echo '<p class="description">' . esc_html(__('No groups found for this tournament. Click refresh to update groups from server.', 'meinturnierplan-wp')) . '</p>';
    }
    
    // Add hidden field to store the initially saved value for JavaScript
    echo '<input type="hidden" id="mtp_group_saved_value" value="' . esc_attr($saved_group) . '" />';
    
    echo '</td>';
    echo '</tr>';
    
    // Show/hide the field based on whether we have a tournament ID
    if (empty($tournament_id)) {
      echo '<style>#mtp_group_field_row { display: none; }</style>';
    }
  }
  
  /**
   * Fetch tournament groups from external API
   */
  private function fetch_tournament_groups($tournament_id, $force_refresh = false) {
    if (empty($tournament_id)) {
      return array('groups' => array(), 'hasFinalRound' => false);
    }
    
    $cache_key = 'mtp_groups_' . $tournament_id;
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
    $url = 'https://tournej.com/json/json.php?id=' . urlencode($tournament_id);
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
   * Render preview section
   */
  private function render_preview_section($post, $meta_values) {
    echo '<h3>' . __('Preview', 'meinturnierplan-wp') . '</h3>';
    echo '<div id="mtp-table-preview" style="border: 1px solid #ddd; padding: 10px; background: #f9f9fa;">';
    
    // Create attributes for preview
    $atts = $this->build_preview_attributes($meta_values);
    echo $this->table_renderer->render_table_html($post->ID, $atts);
    
    echo '</div>';
  }
  
  /**
   * Build preview attributes from meta values
   */
  private function build_preview_attributes($meta_values) {
    // Combine colors with opacity
    $combined_bg_color = $this->combine_color_opacity($meta_values['bg_color'], $meta_values['bg_opacity']);
    $combined_even_bg_color = $this->combine_color_opacity($meta_values['even_bg_color'], $meta_values['even_bg_opacity']);
    $combined_odd_bg_color = $this->combine_color_opacity($meta_values['odd_bg_color'], $meta_values['odd_bg_opacity']);
    $combined_hover_bg_color = $this->combine_color_opacity($meta_values['hover_bg_color'], $meta_values['hover_bg_opacity']);
    $combined_head_bg_color = $this->combine_color_opacity($meta_values['head_bg_color'], $meta_values['head_bg_opacity']);
    
    $atts_array = array(
      'id' => $meta_values['tournament_id'],
      'width' => $meta_values['width'],
      'height' => $meta_values['height'],
      's-size' => $meta_values['font_size'],
      's-sizeheader' => $meta_values['header_font_size'],
      's-padding' => $meta_values['table_padding'],
      's-innerpadding' => $meta_values['inner_padding'],
      's-color' => $meta_values['text_color'],
      's-maincolor' => $meta_values['main_color'],
      's-bgcolor' => $combined_bg_color,
      's-logosize' => $meta_values['logo_size'],
      's-bcolor' => $meta_values['border_color'],
      's-bbcolor' => $meta_values['head_bottom_border_color'],
      's-bgeven' => $combined_even_bg_color,
      's-bgodd' => $combined_odd_bg_color,
      's-bgover' => $combined_hover_bg_color,
      's-bghead' => $combined_head_bg_color,
      's-bsizeh' => $meta_values['bsizeh'],
      's-bsizev' => $meta_values['bsizev'],
      's-bsizeoh' => $meta_values['bsizeoh'],
      's-bsizeov' => $meta_values['bsizeov'],
      's-bbsize' => $meta_values['bbsize'],
      'setlang' => $meta_values['language']
    );
    
    // Add group parameter if specified
    if (!empty($meta_values['group'])) {
      $atts_array['group'] = $meta_values['group'];
    }
    
    // Add sw parameter if suppress_wins is enabled
    if (!empty($meta_values['suppress_wins']) && $meta_values['suppress_wins'] === '1') {
      $atts_array['sw'] = '1';
    }
    
    // Add sl parameter if suppress_logos is enabled
    if (!empty($meta_values['suppress_logos']) && $meta_values['suppress_logos'] === '1') {
      $atts_array['sl'] = '1';
    }
    
    // Add sn parameter if suppress_num_matches is enabled
    if (!empty($meta_values['suppress_num_matches']) && $meta_values['suppress_num_matches'] === '1') {
      $atts_array['sn'] = '1';
    }
    
    // Add bm parameter if projector_presentation is enabled
    if (!empty($meta_values['projector_presentation']) && $meta_values['projector_presentation'] === '1') {
      $atts_array['bm'] = '1';
    }
    
    // Add nav parameter if navigation_for_groups is enabled
    if (!empty($meta_values['navigation_for_groups']) && $meta_values['navigation_for_groups'] === '1') {
      $atts_array['nav'] = '1';
    }
    
    return $atts_array;
  }
  
  /**
   * Combine color and opacity
   */
  private function combine_color_opacity($color, $opacity) {
    if ($opacity !== '' && $opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      return $color . $opacity_hex;
    }
    return $color;
  }
  
  /**
   * Add preview JavaScript
   */
  private function add_preview_javascript($post_id) {
    $field_list = array(
      'mtp_tournament_id', 'mtp_width', 'mtp_height', 'mtp_font_size', 'mtp_header_font_size',
      'mtp_bsizeh', 'mtp_bsizev', 'mtp_bsizeoh', 'mtp_bsizeov', 'mtp_bbsize',
      'mtp_table_padding', 'mtp_inner_padding', 'mtp_text_color', 'mtp_main_color',
      'mtp_bg_color', 'mtp_logo_size', 'mtp_bg_opacity', 'mtp_border_color',
      'mtp_head_bottom_border_color', 'mtp_even_bg_color', 'mtp_even_bg_opacity',
      'mtp_odd_bg_color', 'mtp_odd_bg_opacity', 'mtp_hover_bg_color', 'mtp_hover_bg_opacity',
      'mtp_head_bg_color', 'mtp_head_bg_opacity', 'mtp_suppress_wins', 'mtp_suppress_logos', 'mtp_suppress_num_matches', 'mtp_projector_presentation', 'mtp_navigation_for_groups', 'mtp_language', 'mtp_group'
    );
    ?>
    <style>
    .dashicons-update-alt-rotating {
      animation: rotation 1s infinite linear;
    }
    
    @keyframes rotation {
      from {
        transform: rotate(0deg);
      }
      to {
        transform: rotate(359deg);
      }
    }
    </style>
    <script>
    jQuery(document).ready(function($) {
      // Initialize color picker
      $(".mtp-color-picker").wpColorPicker({
        change: function(event, ui) {
          $("#mtp_tournament_id").trigger("input");
        },
        clear: function() {
          $("#mtp_tournament_id").trigger("input");
        }
      });
      
      // Handle opacity sliders
      $("input[type='range']").on("input", function() {
        var fieldId = $(this).attr('id');
        var opacity = $(this).val();
        $("#" + fieldId + "_value").text(opacity + "%");
        $("#mtp_tournament_id").trigger("input");
      });
      
      // Handle checkbox changes
      $("input[type='checkbox'][id^='mtp_']").on("change", function() {
        $("#mtp_tournament_id").trigger("input");
      });
      
      // Handle select dropdown changes (specifically for language field)
      $("select[id^='mtp_']").on("change", function() {
        $("#mtp_tournament_id").trigger("input");
      });
      
      // Handle tournament ID changes to load groups dynamically
      var tournamentIdChangeTimeout;
      $("#mtp_tournament_id").on("input", function() {
        var tournamentId = $(this).val();
        
        // Clear previous timeout to avoid multiple calls
        clearTimeout(tournamentIdChangeTimeout);
        
        // Only load groups after user stops typing for 500ms
        tournamentIdChangeTimeout = setTimeout(function() {
          loadGroupsForTournament(tournamentId);
        }, 500);
      });
      
      // Function to load groups for a tournament
      function loadGroupsForTournament(tournamentId, preserveSelection, forceRefresh) {
        var $groupRow = $("#mtp_group_field_row");
        var $groupSelect = $("#mtp_group, #mtp_group_select");
        var $refreshButton = $("#mtp_refresh_groups");
        var currentSelection = preserveSelection !== false ? $groupSelect.val() : '';
        var savedValue = $("#mtp_group_saved_value").val(); // Get the initially saved value
        
        // Use saved value if no current selection and this isn't a forced refresh
        if (!currentSelection && savedValue && !forceRefresh) {
          currentSelection = savedValue;
        }
        
        if (!tournamentId) {
          // Hide group field if no tournament ID
          $groupRow.hide();
          $groupSelect.prop("disabled", true).empty().append('<option value="">No groups available</option>');
          $refreshButton.prop("disabled", true);
          return;
        }
        
        // Show the group row
        $groupRow.show();
        
        // Show loading state
        $groupSelect.prop("disabled", true);
        $refreshButton.prop("disabled", true);
        
        if (forceRefresh) {
          $groupSelect.empty().append('<option value="">Refreshing groups...</option>');
          $refreshButton.find('.dashicons').addClass('dashicons-update-alt-rotating');
        } else {
          $groupSelect.empty().append('<option value="">Loading groups...</option>');
        }
        
        // Choose the appropriate AJAX action
        var ajaxAction = forceRefresh ? "mtp_refresh_groups" : "mtp_get_groups";
        
        // Make AJAX call to get groups
        $.post(ajaxurl, {
          action: ajaxAction,
          tournament_id: tournamentId,
          force_refresh: forceRefresh,
          nonce: "<?php echo wp_create_nonce('mtp_preview_nonce'); ?>"
        }, function(response) {
          // Remove loading animation
          $refreshButton.find('.dashicons').removeClass('dashicons-update-alt-rotating');
          
          if (response.success && response.data.groups.length > 0) {
            // Populate group dropdown
            $groupSelect.prop("disabled", false).empty();
            
            $.each(response.data.groups, function(index, group) {
              var groupNumber = index + 1;
              var groupLabel = "Group " + group.displayId;
              var isSelected = false;
              
              // Auto-select first group as default if no previous selection
              if (!currentSelection && index === 0) {
                isSelected = true;
              } else if (currentSelection && currentSelection == groupNumber) {
                isSelected = true;
              }
              
              $groupSelect.append('<option value="' + groupNumber + '"' + (isSelected ? ' selected' : '') + '>' + groupLabel + '</option>');
            });
            
            // Add Final Round option if it exists
            if (response.data.hasFinalRound) {
              var finalRoundSelected = (currentSelection && currentSelection == '90') ? ' selected' : '';
              $groupSelect.append('<option value="90"' + finalRoundSelected + '>Final Round</option>');
            }
            
            // If we had a previous selection but it's not in the new list, select the first available option
            if (currentSelection && $groupSelect.find('option[value="' + currentSelection + '"]').length === 0) {
              $groupSelect.find('option:first').prop('selected', true);
            }
            
            // Enable refresh button
            $refreshButton.prop("disabled", false);
            
            // Show success message for manual refresh
            if (forceRefresh && response.data.refreshed) {
              showTemporaryMessage("Groups refreshed successfully!", "success");
            }
          } else {
            // No groups found, but check for Final Round
            $groupSelect.prop("disabled", false).empty();
            
            if (response.data.hasFinalRound) {
              // If only Final Round is available
              var finalRoundSelected = (currentSelection && currentSelection == '90') ? ' selected' : '';
              $groupSelect.append('<option value="90"' + finalRoundSelected + '>Final Round</option>');
            } else {
              // No groups and no final round
              $groupSelect.append('<option value="">Default</option>');
            }
            
            // If there was a saved value, show it as an option even if groups aren't available
            if (currentSelection && currentSelection !== '' && currentSelection !== '90' && !response.data.hasFinalRound) {
              $groupSelect.append('<option value="' + currentSelection + '" selected>Group ' + currentSelection + ' (saved)</option>');
            }
            
            $refreshButton.prop("disabled", false);
            
            // Show message for manual refresh with no groups
            if (forceRefresh) {
              showTemporaryMessage("No groups found for this tournament.", "info");
            }
          }
        }).fail(function() {
          // Remove loading animation on error
          $refreshButton.find('.dashicons').removeClass('dashicons-update-alt-rotating');
          
          // On error, restore the field with saved value if available
          $groupSelect.prop("disabled", false).empty();
          $groupSelect.append('<option value="">Default</option>');
          
          if (currentSelection && currentSelection !== '') {
            var label = currentSelection == '90' ? 'Final Round (saved)' : 'Group ' + currentSelection + ' (saved)';
            $groupSelect.append('<option value="' + currentSelection + '" selected>' + label + '</option>');
          }
          
          $refreshButton.prop("disabled", false);
          
          if (forceRefresh) {
            showTemporaryMessage("Error refreshing groups. Please try again.", "error");
          }
        });
      }
      
      // Function to show temporary messages
      function showTemporaryMessage(message, type) {
        var messageClass = type === 'success' ? 'notice-success' : type === 'error' ? 'notice-error' : 'notice-info';
        var $message = $('<div class="notice ' + messageClass + ' is-dismissible" style="margin: 10px 0;"><p>' + message + '</p></div>');
        $("#mtp_group_field_row").after($message);
        
        // Auto-dismiss after 3 seconds
        setTimeout(function() {
          $message.fadeOut(function() {
            $message.remove();
          });
        }, 3000);
      }
      
      // Handle refresh button click
      $(document).on("click", "#mtp_refresh_groups", function(e) {
        e.preventDefault();
        var tournamentId = $("#mtp_tournament_id").val();
        if (tournamentId) {
          loadGroupsForTournament(tournamentId, true, true); // Preserve selection, force refresh
        }
      });
      
      // Load groups on page load if tournament ID exists
      var initialTournamentId = $("#mtp_tournament_id").val();
      if (initialTournamentId) {
        loadGroupsForTournament(initialTournamentId, false); // Don't preserve selection on initial load
      }
      
      $("#<?php echo implode(', #', $field_list); ?>").on("input change", function() {
        // Don't reload groups when other fields change, only when tournament ID changes
        if (this.id === 'mtp_tournament_id') {
          return; // Tournament ID changes are handled separately above
        }
        // Get all field values
        var data = {
          post_id: <?php echo intval($post_id); ?>,
          tournament_id: $("#mtp_tournament_id").val(),
          width: $("#mtp_width").val(),
          height: $("#mtp_height").val(),
          font_size: $("#mtp_font_size").val(),
          header_font_size: $("#mtp_header_font_size").val(),
          bsizeh: $("#mtp_bsizeh").val(),
          bsizev: $("#mtp_bsizev").val(),
          bsizeoh: $("#mtp_bsizeoh").val(),
          bsizeov: $("#mtp_bsizeov").val(),
          bbsize: $("#mtp_bbsize").val(),
          table_padding: $("#mtp_table_padding").val(),
          inner_padding: $("#mtp_inner_padding").val(),
          text_color: $("#mtp_text_color").val().replace("#", ""),
          main_color: $("#mtp_main_color").val().replace("#", ""),
          bg_color: $("#mtp_bg_color").val().replace("#", ""),
          logo_size: $("#mtp_logo_size").val(),
          bg_opacity: $("#mtp_bg_opacity").val(),
          border_color: $("#mtp_border_color").val().replace("#", ""),
          head_bottom_border_color: $("#mtp_head_bottom_border_color").val().replace("#", ""),
          even_bg_color: $("#mtp_even_bg_color").val().replace("#", ""),
          even_bg_opacity: $("#mtp_even_bg_opacity").val(),
          odd_bg_color: $("#mtp_odd_bg_color").val().replace("#", ""),
          odd_bg_opacity: $("#mtp_odd_bg_opacity").val(),
          hover_bg_color: $("#mtp_hover_bg_color").val().replace("#", ""),
          hover_bg_opacity: $("#mtp_hover_bg_opacity").val(),
          head_bg_color: $("#mtp_head_bg_color").val().replace("#", ""),
          head_bg_opacity: $("#mtp_head_bg_opacity").val(),
          suppress_wins: $("#mtp_suppress_wins").is(":checked") ? "1" : "0",
          suppress_logos: $("#mtp_suppress_logos").is(":checked") ? "1" : "0",
          suppress_num_matches: $("#mtp_suppress_num_matches").is(":checked") ? "1" : "0",
          projector_presentation: $("#mtp_projector_presentation").is(":checked") ? "1" : "0",
          navigation_for_groups: $("#mtp_navigation_for_groups").is(":checked") ? "1" : "0",
          language: $("#mtp_language").val(),
          group: $("#mtp_group").val(),
          action: "mtp_preview_table",
          nonce: "<?php echo wp_create_nonce('mtp_preview_nonce'); ?>"
        };

        // Convert opacity to hex and combine with colors
        data.bg_color = data.bg_color + Math.round((data.bg_opacity / 100) * 255).toString(16).padStart(2, "0");
        data.even_bg_color = data.even_bg_color + Math.round((data.even_bg_opacity / 100) * 255).toString(16).padStart(2, "0");
        data.odd_bg_color = data.odd_bg_color + Math.round((data.odd_bg_opacity / 100) * 255).toString(16).padStart(2, "0");
        data.hover_bg_color = data.hover_bg_color + Math.round((data.hover_bg_opacity / 100) * 255).toString(16).padStart(2, "0");
        data.head_bg_color = data.head_bg_color + Math.round((data.head_bg_opacity / 100) * 255).toString(16).padStart(2, "0");

        $.post(ajaxurl, data, function(response) {
          if (response.success) {
            $("#mtp-table-preview").html(response.data);
          }
        });
      });
    });
    </script>
    <?php
  }
  
  /**
   * Shortcode meta box callback
   */
  public function shortcode_meta_box_callback($post) {
    $meta_values = $this->get_meta_values($post->ID);
    $shortcode = $this->generate_shortcode($post->ID, $meta_values);
    
    $this->render_shortcode_generator($shortcode, $meta_values['tournament_id']);
  }
  
  /**
   * Generate shortcode
   */
  private function generate_shortcode($post_id, $meta_values) {
    // Combine colors with opacity
    $combined_bg_color = $this->combine_color_opacity($meta_values['bg_color'], $meta_values['bg_opacity']);
    $combined_even_bg_color = $this->combine_color_opacity($meta_values['even_bg_color'], $meta_values['even_bg_opacity']);
    $combined_odd_bg_color = $this->combine_color_opacity($meta_values['odd_bg_color'], $meta_values['odd_bg_opacity']);
    $combined_hover_bg_color = $this->combine_color_opacity($meta_values['hover_bg_color'], $meta_values['hover_bg_opacity']);
    $combined_head_bg_color = $this->combine_color_opacity($meta_values['head_bg_color'], $meta_values['head_bg_opacity']);
    
    $shortcode = '[mtp-table id="' . esc_attr($meta_values['tournament_id']) . '" post_id="' . $post_id . '" lang="' . esc_attr($meta_values['language']) . '" s-size="' . esc_attr($meta_values['font_size']) . '" s-sizeheader="' . esc_attr($meta_values['header_font_size']) . '" s-color="' . esc_attr($meta_values['text_color']) . '" s-maincolor="' . esc_attr($meta_values['main_color']) . '" s-padding="' . esc_attr($meta_values['table_padding']) . '" s-innerpadding="' . esc_attr($meta_values['inner_padding']) . '" s-bgcolor="' . esc_attr($combined_bg_color). '" s-bcolor="' . esc_attr($meta_values['border_color']) . '" s-bbcolor="' . esc_attr($meta_values['head_bottom_border_color']) . '" s-bgeven="' . esc_attr($combined_even_bg_color) . '" s-logosize="' . esc_attr($meta_values['logo_size']) . '" s-bsizeh="' . esc_attr($meta_values['bsizeh']) . '" s-bsizev="' . esc_attr($meta_values['bsizev']) . '" s-bsizeoh="' . esc_attr($meta_values['bsizeoh']) . '" s-bsizeov="' . esc_attr($meta_values['bsizeov']) . '" s-bbsize="' . esc_attr($meta_values['bbsize']) . '" s-bgodd="' . esc_attr($combined_odd_bg_color) . '" s-bgover="' . esc_attr($combined_hover_bg_color) . '" s-bghead="' . esc_attr($combined_head_bg_color) . '" width="' . esc_attr($meta_values['width']) . '" height="' . esc_attr($meta_values['height']) . '"';
    
    // Add group parameter if specified
    if (!empty($meta_values['group'])) {
      $shortcode .= ' group="' . esc_attr($meta_values['group']) . '"';
    }
    
    // Add sw parameter if suppress_wins is enabled
    if (!empty($meta_values['suppress_wins']) && $meta_values['suppress_wins'] === '1') {
      $shortcode .= ' sw="1"';
    }
    
    // Add sl parameter if suppress_logos is enabled
    if (!empty($meta_values['suppress_logos']) && $meta_values['suppress_logos'] === '1') {
      $shortcode .= ' sl="1"';
    }
    
    // Add sn parameter if suppress_num_matches is enabled
    if (!empty($meta_values['suppress_num_matches']) && $meta_values['suppress_num_matches'] === '1') {
      $shortcode .= ' sn="1"';
    }
    
    // Add bm parameter if projector_presentation is enabled
    if (!empty($meta_values['projector_presentation']) && $meta_values['projector_presentation'] === '1') {
      $shortcode .= ' bm="1"';
    }
    
    // Add nav parameter if navigation_for_groups is enabled
    if (!empty($meta_values['navigation_for_groups']) && $meta_values['navigation_for_groups'] === '1') {
      $shortcode .= ' nav="1"';
    }
    
    $shortcode .= ']';
    
    return $shortcode;
  }
  
  /**
   * Render shortcode generator
   */
  private function render_shortcode_generator($shortcode, $tournament_id) {
    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="mtp_shortcode_field" style="display: block; margin-bottom: 5px; font-weight: bold;">' . __('Generated Shortcode:', 'meinturnierplan-wp') . '</label>';
    echo '<textarea id="mtp_shortcode_field" readonly style="width: 100%; height: 80px; font-family: monospace; font-size: 12px; padding: 8px; border: 1px solid #ddd; background: #f9f9f9;">' . esc_textarea($shortcode) . '</textarea>';
    echo '</div>';
    
    echo '<button type="button" id="mtp_copy_shortcode" class="button button-secondary" style="width: 100%; margin-bottom: 10px;">';
    echo '<span class="dashicons dashicons-admin-page" style="vertical-align: middle; margin-right: 5px;"></span>';
    echo __('Copy Shortcode', 'meinturnierplan-wp');
    echo '</button>';
    
    echo '<div id="mtp_copy_success" style="display: none; color: #46b450; margin-top: 8px; text-align: center;">';
    echo '<span class="dashicons dashicons-yes-alt" style="vertical-align: middle;"></span> ';
    echo __('Shortcode copied to clipboard!', 'meinturnierplan-wp');
    echo '</div>';
    
    if (empty($tournament_id)) {
      echo '<div style="margin-top: 10px; padding: 8px; background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; border-radius: 3px;">';
      echo '<strong>' . __('Note:', 'meinturnierplan-wp') . '</strong> ';
      echo __('Enter a Tournament ID above to display live tournament data. Without an ID, a placeholder will be shown.', 'meinturnierplan-wp');
      echo '</div>';
    }
    
    // Add JavaScript for copy functionality and live update
    $this->add_shortcode_javascript();
  }
  
  /**
   * Add shortcode JavaScript
   */
  private function add_shortcode_javascript() {
    ?>
    <script>
    jQuery(document).ready(function($) {
      // Copy shortcode to clipboard
      $("#mtp_copy_shortcode").on("click", function() {
        var shortcodeField = $("#mtp_shortcode_field");
        shortcodeField.select();
        document.execCommand("copy");
        
        $("#mtp_copy_success").fadeIn().delay(2000).fadeOut();
      });
      
      // Update shortcode when fields change
      $("input[id^='mtp_'], .mtp-color-picker, select[id^='mtp_']").on("input change", function() {
        updateShortcode();
      });
      
      // Opacity sliders
      $("input[type='range'][id^='mtp_']").on("input", function() {
        updateShortcode();
      });
      
      function updateShortcode() {
        var postId = <?php echo intval(get_the_ID()); ?>;
        var tournamentId = $("#mtp_tournament_id").val() || "";
        var width = $("#mtp_width").val() || "300";
        var height = $("#mtp_height").val() || "152";
        var fontSize = $("#mtp_font_size").val() || "9";
        var headerFontSize = $("#mtp_header_font_size").val() || "10";
        var textColor = $("#mtp_text_color").val().replace("#", "") || "000000";
        var mainColor = $("#mtp_main_color").val().replace("#", "") || "173f75";
        var tablePadding = $("#mtp_table_padding").val() || "2";
        var innerPadding = $("#mtp_inner_padding").val() || "5";
        var logoSize = $("#mtp_logo_size").val() || "20";
        var borderColor = $("#mtp_border_color").val().replace("#", "") || "bbbbbb";
        var headBottomBorderColor = $("#mtp_head_bottom_border_color").val().replace("#", "") || "bbbbbb";
        var bsizeh = $("#mtp_bsizeh").val() || "1";
        var bsizev = $("#mtp_bsizev").val() || "1";
        var bsizeoh = $("#mtp_bsizeoh").val() || "1";
        var bsizeov = $("#mtp_bsizeov").val() || "1";
        var bbsize = $("#mtp_bbsize").val() || "2";
        var language = $("#mtp_language").val() || "en";
        var group = $("#mtp_group").val() || "";
        
        // Combine colors with opacity (convert opacity percentage to hex)
        var bgColor = $("#mtp_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_bg_opacity").val() / 100) * 255));
        var evenBgColor = $("#mtp_even_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_even_bg_opacity").val() / 100) * 255));
        var oddBgColor = $("#mtp_odd_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_odd_bg_opacity").val() / 100) * 255));
        var hoverBgColor = $("#mtp_hover_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_hover_bg_opacity").val() / 100) * 255));
        var headBgColor = $("#mtp_head_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_head_bg_opacity").val() / 100) * 255));
        
        // Build complete shortcode
        var newShortcode = '[mtp-table id="' + tournamentId + '" post_id="' + postId + '" lang="' + language + '"' +
                          ' s-size="' + fontSize + '"' +
                          ' s-sizeheader="' + headerFontSize + '"' +
                          ' s-color="' + textColor + '"' +
                          ' s-maincolor="' + mainColor + '"' +
                          ' s-padding="' + tablePadding + '"' +
                          ' s-innerpadding="' + innerPadding + '"' +
                          ' s-bgcolor="' + bgColor + '"' +
                          ' s-bcolor="' + borderColor + '"' +
                          ' s-bbcolor="' + headBottomBorderColor + '"' +
                          ' s-bgeven="' + evenBgColor + '"' +
                          ' s-logosize="' + logoSize + '"' +
                          ' s-bsizeh="' + bsizeh + '"' +
                          ' s-bsizev="' + bsizev + '"' +
                          ' s-bsizeoh="' + bsizeoh + '"' +
                          ' s-bsizeov="' + bsizeov + '"' +
                          ' s-bbsize="' + bbsize + '"' +
                          ' s-bgodd="' + oddBgColor + '"' +
                          ' s-bgover="' + hoverBgColor + '"' +
                          ' s-bghead="' + headBgColor + '"' +
                          ' width="' + width + '"' +
                          ' height="' + height + '"';
        
        // Add sw parameter if suppress_wins checkbox is checked
        if ($("#mtp_suppress_wins").is(":checked")) {
          newShortcode += ' sw="1"';
        }
        
        // Add sl parameter if suppress_logos checkbox is checked
        if ($("#mtp_suppress_logos").is(":checked")) {
          newShortcode += ' sl="1"';
        }
        
        // Add sn parameter if suppress_num_matches checkbox is checked
        if ($("#mtp_suppress_num_matches").is(":checked")) {
          newShortcode += ' sn="1"';
        }
        
        // Add bm parameter if projector_presentation checkbox is checked
        if ($("#mtp_projector_presentation").is(":checked")) {
          newShortcode += ' bm="1"';
        }
        
        // Add nav parameter if navigation_for_groups checkbox is checked
        if ($("#mtp_navigation_for_groups").is(":checked")) {
          newShortcode += ' nav="1"';
        }
        
        // Add group parameter if selected
        if (group) {
          newShortcode += ' group="' + group + '"';
        }
        
        newShortcode += ']';
        
        $("#mtp_shortcode_field").val(newShortcode);
      }
      
      // Convert decimal opacity to hex (match PHP behavior)
      function opacityToHex(opacity) {
        var hex = Math.round(opacity).toString(16);
        return hex.length === 1 ? "0" + hex : hex;
      }
    });
    </script>
    <?php
  }
  
  /**
   * Save meta box data
   */
  public function save_meta_boxes($post_id) {
    // Check if nonce is valid
    if (!isset($_POST['mtp_table_meta_box_nonce']) || !wp_verify_nonce($_POST['mtp_table_meta_box_nonce'], 'mtp_table_meta_box')) {
      return;
    }
    
    // Check if user has permission
    if (isset($_POST['post_type']) && 'mtp_table' == $_POST['post_type']) {
      if (!current_user_can('edit_page', $post_id)) {
        return;
      }
    } else {
      if (!current_user_can('edit_post', $post_id)) {
        return;
      }
    }
    
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }
    
    // Save all meta fields
    $meta_fields = array(
      'tournament_id', 'width', 'height', 'font_size', 'header_font_size',
      'bsizeh', 'bsizev', 'bsizeoh', 'bsizeov', 'bbsize', 'table_padding',
      'inner_padding', 'text_color', 'main_color', 'bg_color', 'bg_opacity',
      'border_color', 'head_bottom_border_color', 'even_bg_color', 'even_bg_opacity',
      'odd_bg_color', 'odd_bg_opacity', 'hover_bg_color', 'hover_bg_opacity',
      'head_bg_color', 'head_bg_opacity', 'logo_size', 'suppress_wins', 'suppress_logos', 'suppress_num_matches', 'projector_presentation', 'navigation_for_groups', 'language', 'group'
    );
    
    foreach ($meta_fields as $field) {
      $post_field = 'mtp_' . $field;
      $meta_key = '_mtp_' . $field;
      
      if (in_array($field, array('suppress_wins', 'suppress_logos', 'suppress_num_matches', 'projector_presentation', 'navigation_for_groups'))) {
        // Handle checkbox: if not checked, it won't be in $_POST
        $value = isset($_POST[$post_field]) ? '1' : '0';
        update_post_meta($post_id, $meta_key, $value);
      } elseif (isset($_POST[$post_field])) {
        $value = $this->sanitize_meta_value($field, $_POST[$post_field]);
        update_post_meta($post_id, $meta_key, $value);
      }
    }
  }
  
  /**
   * Sanitize meta value based on field type
   */
  private function sanitize_meta_value($field, $value) {
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
}
