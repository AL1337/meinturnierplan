<?php
/**
 * Matches Admin Meta Boxes Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Matches Admin Meta Boxes Class
 */
class MTP_Matches_Admin_Meta_Boxes {

  /**
   * Matches renderer instance
   */
  private $matches_renderer;

  /**
   * Constructor
   */
  public function __construct($matches_renderer) {
    $this->matches_renderer = $matches_renderer;
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
      'mtp_matches_settings',
      __('Matches Settings & Preview', 'meinturnierplan-wp'),
      array($this, 'meta_box_callback'),
      'mtp_matches',
      'normal',
      'high'
    );

    add_meta_box(
      'mtp_matches_shortcode',
      __('Shortcode Generator', 'meinturnierplan-wp'),
      array($this, 'shortcode_meta_box_callback'),
      'mtp_matches',
      'side',
      'high'
    );
  }

  /**
   * Meta box callback
   */
  public function meta_box_callback($post) {
    // Add nonce for security
    wp_nonce_field('mtp_matches_meta_box', 'mtp_matches_meta_box_nonce');

    // Get current values with defaults
    $meta_values = $this->get_meta_values($post->ID);

    // Start two-column layout
    echo '<div class="mtp-admin-two-column-layout">';

    // Left column - Matches Settings
    echo '<div class="mtp-admin-column mtp-admin-column-left">';
    echo '<h3>' . __('Matches Settings', 'meinturnierplan-wp') . '</h3>';
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
      'width' => '588',
      'height' => '3784',
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
      'bsizeh' => '1',
      'bsizev' => '1',
      'bsizeoh' => '1',
      'bsizeov' => '1',
      'bbsize' => '2',
      'ehrsize' => '10',
      'ehrtop' => '9',
      'ehrbottom' => '3',
      'wrap' => 'false',
      'language' => $this->get_default_language(),
      'group' => 'all',
    );

    $meta_values = array();
    foreach ($defaults as $key => $default) {
      $meta_key = '_mtp_matches_' . $key;
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
    $this->render_text_field('tournament_id', __('Tournament ID', 'meinturnierplan-wp'), $meta_values['tournament_id'], __('Enter the tournament ID from meinturnierplan.de (e.g., 1752429520)', 'meinturnierplan-wp'));
    $this->render_select_field('language', __('Language', 'meinturnierplan-wp'), $meta_values['language'], $this->get_language_options(), __('Select the language for the matches display.', 'meinturnierplan-wp'));

    // Display Options Group
    $this->render_group_header(__('Display Options', 'meinturnierplan-wp'));
    $this->render_conditional_group_field($meta_values);

    // Dimensions Group
    $this->render_group_header(__('Dimensions', 'meinturnierplan-wp'));
    $this->render_number_field('width', __('Widget Width (px)', 'meinturnierplan-wp'), $meta_values['width'], __('Set the width of the matches widget in pixels.', 'meinturnierplan-wp'), 100, 2000);
    $this->render_number_field('height', __('Widget Height (px)', 'meinturnierplan-wp'), $meta_values['height'], __('Set the height of the matches widget in pixels.', 'meinturnierplan-wp'), 100, 5000);

    // Typography Group
    $this->render_group_header(__('Typography', 'meinturnierplan-wp'));
    $this->render_number_field('font_size', __('Content Font Size (pt)', 'meinturnierplan-wp'), $meta_values['font_size'], __('Set the font size of the matches content. 9pt is the default value.', 'meinturnierplan-wp'), 6, 24);
    $this->render_number_field('header_font_size', __('Header Font Size (pt)', 'meinturnierplan-wp'), $meta_values['header_font_size'], __('Set the font size of the matches headers. 10pt is the default value.', 'meinturnierplan-wp'), 6, 24);

    // Spacing & Layout Group
    $this->render_group_header(__('Spacing & Layout', 'meinturnierplan-wp'));
    $this->render_number_field('table_padding', __('Table Padding (px)', 'meinturnierplan-wp'), $meta_values['table_padding'], __('Set the padding around the matches table. 2px is the default value.', 'meinturnierplan-wp'), 0, 50);
    $this->render_number_field('inner_padding', __('Inner Padding (px)', 'meinturnierplan-wp'), $meta_values['inner_padding'], __('Set the padding inside the matches table cells. 5px is the default value.', 'meinturnierplan-wp'), 0, 20);
    $this->render_select_field('wrap', __('Text Wrapping', 'meinturnierplan-wp'), $meta_values['wrap'], array('false' => __('No Wrapping', 'meinturnierplan-wp'), 'true' => __('Allow Wrapping', 'meinturnierplan-wp')), __('Control text wrapping in the matches display.', 'meinturnierplan-wp'));

    // Match-specific Typography Group
    $this->render_group_header(__('Match Typography', 'meinturnierplan-wp'));
    $this->render_number_field('ehrsize', __('Enhanced Header Size (pt)', 'meinturnierplan-wp'), $meta_values['ehrsize'], __('Set the enhanced header size for matches. 10pt is the default value.', 'meinturnierplan-wp'), 6, 24);
    $this->render_number_field('ehrtop', __('Enhanced Header Top Spacing (px)', 'meinturnierplan-wp'), $meta_values['ehrtop'], __('Set the top spacing for enhanced headers. 9px is the default value.', 'meinturnierplan-wp'), 0, 50);
    $this->render_number_field('ehrbottom', __('Enhanced Header Bottom Spacing (px)', 'meinturnierplan-wp'), $meta_values['ehrbottom'], __('Set the bottom spacing for enhanced headers. 3px is the default value.', 'meinturnierplan-wp'), 0, 50);

    // Border Settings Group
    $this->render_group_header(__('Border Settings', 'meinturnierplan-wp'));
    $this->render_number_field('bsizeh', __('Border Vertical Size (px)', 'meinturnierplan-wp'), $meta_values['bsizeh'], __('Set the border vertical size of the matches table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bsizev', __('Border Horizontal Size (px)', 'meinturnierplan-wp'), $meta_values['bsizev'], __('Set the border horizontal size of the matches table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bsizeoh', __('Table Block Border Size (px)', 'meinturnierplan-wp'), $meta_values['bsizeoh'], __('Set the block border size of the matches table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bsizeov', __('Table Inline Border Size (px)', 'meinturnierplan-wp'), $meta_values['bsizeov'], __('Set the inline border size of the matches table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bbsize', __('Table Head Border Bottom Size (px)', 'meinturnierplan-wp'), $meta_values['bbsize'], __('Set the head border bottom size of the matches table. 2px is the default value.', 'meinturnierplan-wp'), 1, 10);

    // Colors Group
    $this->render_group_header(__('Colors', 'meinturnierplan-wp'));
    $this->render_color_field('text_color', __('Text Color', 'meinturnierplan-wp'), $meta_values['text_color'], __('Set the color of the matches text. Black (#000000) is the default value.', 'meinturnierplan-wp'));
    $this->render_color_field('main_color', __('Main Color', 'meinturnierplan-wp'), $meta_values['main_color'], __('Set the main color of the matches (headers, highlights). Blue (#173f75) is the default value.', 'meinturnierplan-wp'));
    $this->render_color_field('border_color', __('Border Color', 'meinturnierplan-wp'), $meta_values['border_color'], __('Set the border color of the matches table. Light gray (#bbbbbb) is the default value.', 'meinturnierplan-wp'));
    $this->render_color_field('head_bottom_border_color', __('Table Head Bottom Border Color', 'meinturnierplan-wp'), $meta_values['head_bottom_border_color'], __('Set the bottom border color of the table header. Light gray (#bbbbbb) is the default value.', 'meinturnierplan-wp'));

    // Background Colors Group
    $this->render_group_header(__('Background Colors', 'meinturnierplan-wp'));
    $this->render_color_opacity_field('bg_color', 'bg_opacity', __('Background Color', 'meinturnierplan-wp'), $meta_values['bg_color'], $meta_values['bg_opacity'], __('Set the background color and opacity of the matches table. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
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
    echo '<th scope="row"><label for="mtp_matches_' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="mtp_matches_' . esc_attr($field_name) . '" name="mtp_matches_' . esc_attr($field_name) . '" value="' . esc_attr($value) . '" class="regular-text" />';
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
    echo '<th scope="row"><label for="mtp_matches_' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_matches_' . esc_attr($field_name) . '" name="mtp_matches_' . esc_attr($field_name) . '" value="' . esc_attr($value) . '"';
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
   * Render select field
   */
  private function render_select_field($field_name, $label, $value, $options, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_matches_' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<select id="mtp_matches_' . esc_attr($field_name) . '" name="mtp_matches_' . esc_attr($field_name) . '">';
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
   * Render color field
   */
  private function render_color_field($field_name, $label, $value, $description = '') {
    // Ensure value has # prefix for color picker
    $display_value = '#' . ltrim($value, '#');

    echo '<tr>';
    echo '<th scope="row"><label for="mtp_matches_' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="mtp_matches_' . esc_attr($field_name) . '" name="mtp_matches_' . esc_attr($field_name) . '" value="' . esc_attr($display_value) . '" class="mtp-color-picker" />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render color with opacity field
   */
  private function render_color_opacity_field($color_field, $opacity_field, $label, $color_value, $opacity_value, $description = '') {
    // Ensure color value has # prefix for color picker
    $display_color = '#' . ltrim($color_value, '#');

    echo '<tr>';
    echo '<th scope="row">' . esc_html($label) . '</th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 10px;">';
    echo '<input type="text" id="mtp_matches_' . esc_attr($color_field) . '" name="mtp_matches_' . esc_attr($color_field) . '" value="' . esc_attr($display_color) . '" class="mtp-color-picker" style="width: 100px;" />';
    echo '<span style="font-size: 12px;">Opacity:</span>';
    echo '<input type="range" id="mtp_matches_' . esc_attr($opacity_field) . '" name="mtp_matches_' . esc_attr($opacity_field) . '" min="0" max="100" value="' . esc_attr($opacity_value) . '" style="width: 100px;" />';
    echo '<span id="mtp_matches_' . esc_attr($opacity_field) . '_value" style="font-size: 12px; min-width: 35px;">' . esc_html($opacity_value) . '%</span>';
    echo '</div>';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
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
    echo '<tr id="mtp_matches_group_field_row">';
    echo '<th scope="row"><label for="mtp_matches_group">' . esc_html(__('Group', 'meinturnierplan-wp')) . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 10px;">';
    echo '<select id="mtp_matches_group" name="mtp_matches_group" class="regular-text">';

    // Always add "All Matches" option first (default)
    $is_all_selected = empty($saved_group) || $saved_group === 'all';
    $all_selected = $is_all_selected ? ' selected' : '';
    echo '<option value="all"' . $all_selected . '>' . esc_html(__('All Matches', 'meinturnierplan-wp')) . '</option>';

    if (!empty($groups)) {
      // Populate with actual groups
      foreach ($groups as $index => $group) {
        $group_value = $index + 1; // Use 1-based indexing for URL parameter
        $is_selected = (!empty($saved_group) && $saved_group == $group_value);

        $selected = $is_selected ? ' selected' : '';
        echo '<option value="' . esc_attr($group_value) . '"' . $selected . '>' . esc_html(sprintf(__('Group %s', 'meinturnierplan-wp'), $group['displayId'])) . '</option>';
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
      } else if ($saved_group !== 'all') {
        echo '<option value="' . esc_attr($saved_group) . '" selected>' . esc_html(sprintf(__('Group %s (saved)', 'meinturnierplan-wp'), $saved_group)) . '</option>';
      }
    } else {
      // No groups available - "All Matches" is already added above as default
      // Check for Final Round only if needed
      if ($has_final_round) {
        $is_final_selected = (!empty($saved_group) && $saved_group == '90');
        $final_selected = $is_final_selected ? ' selected' : '';
        echo '<option value="90"' . $final_selected . '>' . esc_html(__('Final Round', 'meinturnierplan-wp')) . '</option>';
      }
    }

    echo '</select>';
    echo '<button type="button" id="mtp_matches_refresh_groups" class="button button-secondary" title="' . esc_attr(__('Refresh Groups', 'meinturnierplan-wp')) . '">';
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
    echo '<input type="hidden" id="mtp_matches_group_saved_value" value="' . esc_attr($saved_group) . '" />';
    echo '</td>';
    echo '</tr>';

    // Hide group field if no tournament ID
    if (empty($tournament_id)) {
      echo '<style>#mtp_matches_group_field_row { display: none; }</style>';
    }
  }

  /**
   * Fetch tournament groups from API
   */
  private function fetch_tournament_groups($tournament_id, $force_refresh = false) {
    if (empty($tournament_id)) {
      return array('groups' => array(), 'hasFinalRound' => false);
    }

    $cache_key = 'mtp_matches_groups_' . $tournament_id;
    $cache_duration = 300; // 5 minutes

    // Check cache first unless forcing refresh
    if (!$force_refresh) {
      $cached_data = get_transient($cache_key);
      if ($cached_data !== false) {
        return array(
          'groups' => $cached_data,
          'hasFinalRound' => get_transient($cache_key . '_final_round') === 'yes'
        );
      }
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
        return array(
          'groups' => $cached_data,
          'hasFinalRound' => get_transient($cache_key . '_final_round') === 'yes'
        );
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

    // Check if finalRankTable exists and is not empty
    if (isset($data['finalRankTable']) && is_array($data['finalRankTable']) && !empty($data['finalRankTable'])) {
      $has_final_round = true;
    }

    // Cache the result (even if empty)
    set_transient($cache_key, $groups, $cache_duration);
    set_transient($cache_key . '_final_round', $has_final_round ? 'yes' : 'no', $cache_duration);

    return array(
      'groups' => $groups,
      'hasFinalRound' => $has_final_round
    );
  }

  /**
   * Render preview section
   */
  private function render_preview_section($post, $meta_values) {
    echo '<h3>' . __('Live Preview', 'meinturnierplan-wp') . '</h3>';
    echo '<div id="mtp-matches-preview" style="border: 1px solid #ddd; padding: 10px; background: #f9f9f9; min-height: 200px;">';

    // Show placeholder message if no tournament ID
    if (empty($meta_values['tournament_id'])) {
      echo '<div style="text-align: center; color: #666; padding: 40px 0;">';
      echo __('Enter a Tournament ID to see live preview', 'meinturnierplan-wp');
      echo '</div>';
    } else {
      // Generate preview using the renderer
      $atts = $this->convert_meta_to_shortcode_atts($meta_values);
      echo $this->matches_renderer->render_matches_html($post->ID, $atts);
    }

    echo '</div>';
  }

  /**
   * Convert meta values to shortcode attributes
   */
  private function convert_meta_to_shortcode_atts($meta_values) {
    // Combine colors with opacity
    $combined_bg_color = $this->combine_color_opacity($meta_values['bg_color'], $meta_values['bg_opacity']);
    $combined_even_bg_color = $this->combine_color_opacity($meta_values['even_bg_color'], $meta_values['even_bg_opacity']);
    $combined_odd_bg_color = $this->combine_color_opacity($meta_values['odd_bg_color'], $meta_values['odd_bg_opacity']);
    $combined_hover_bg_color = $this->combine_color_opacity($meta_values['hover_bg_color'], $meta_values['hover_bg_opacity']);
    $combined_head_bg_color = $this->combine_color_opacity($meta_values['head_bg_color'], $meta_values['head_bg_opacity']);

    return array(
      'id' => $meta_values['tournament_id'],
      'lang' => $meta_values['language'],
      's-size' => $meta_values['font_size'],
      's-sizeheader' => $meta_values['header_font_size'],
      's-color' => $meta_values['text_color'],
      's-maincolor' => $meta_values['main_color'],
      's-padding' => $meta_values['table_padding'],
      's-innerpadding' => $meta_values['inner_padding'],
      's-bgcolor' => $combined_bg_color,
      's-bcolor' => $meta_values['border_color'],
      's-bsizeh' => $meta_values['bsizeh'],
      's-bsizev' => $meta_values['bsizev'],
      's-bsizeoh' => $meta_values['bsizeoh'],
      's-bsizeov' => $meta_values['bsizeov'],
      's-bbcolor' => $meta_values['head_bottom_border_color'],
      's-bbsize' => $meta_values['bbsize'],
      's-bgeven' => $combined_even_bg_color,
      's-bgodd' => $combined_odd_bg_color,
      's-bgover' => $combined_hover_bg_color,
      's-bghead' => $combined_head_bg_color,
      's-ehrsize' => $meta_values['ehrsize'],
      's-ehrtop' => $meta_values['ehrtop'],
      's-ehrbottom' => $meta_values['ehrbottom'],
      's-wrap' => $meta_values['wrap'],
      'width' => $meta_values['width'],
      'height' => $meta_values['height'],
    );
  }

  /**
   * Combine color and opacity
   */
  private function combine_color_opacity($hex_color, $opacity_percent) {
    // Remove # if present
    $hex_color = ltrim($hex_color, '#');

    // Convert opacity percentage to hex
    $opacity_hex = str_pad(dechex(round(($opacity_percent / 100) * 255)), 2, '0', STR_PAD_LEFT);

    return $hex_color . $opacity_hex;
  }

  /**
   * Get default language
   */
  private function get_default_language() {
    $locale = get_locale();
    return substr($locale, 0, 2);
  }

  /**
   * Get language options
   */
  private function get_language_options() {
    return array(
      'en' => __('English', 'meinturnierplan-wp'),
      'de' => __('German', 'meinturnierplan-wp'),
      'fr' => __('French', 'meinturnierplan-wp'),
      'es' => __('Spanish', 'meinturnierplan-wp'),
      'it' => __('Italian', 'meinturnierplan-wp'),
      'nl' => __('Dutch', 'meinturnierplan-wp'),
    );
  }

  /**
   * Add preview JavaScript
   */
  private function add_preview_javascript($post_id) {
    ?>
    <script>
    jQuery(document).ready(function($) {
      // Initialize color pickers
      if (typeof $.fn.wpColorPicker !== 'undefined') {
        $('.mtp-color-picker').wpColorPicker({
          change: function() {
            updatePreview();
          },
          clear: function() {
            updatePreview();
          }
        });
      }

      // Update opacity value display
      $("input[type='range'][id*='mtp_matches_']").on("input", function() {
        var value = $(this).val();
        $(this).next("span").text(value + "%");
        updatePreview();
      });

      // Tournament ID change handler - fetch groups when tournament ID changes
      $("#mtp_matches_tournament_id").on("input change", function() {
        var tournamentId = $(this).val();
        var groupField = $("#mtp_matches_group_field_row");

        if (tournamentId) {
          // Show group field
          groupField.show();
          // Fetch groups for this tournament
          fetchGroups(tournamentId, false);
        } else {
          // Hide group field
          groupField.hide();
        }

        updatePreview();
      });

      // Group refresh button handler
      $("#mtp_matches_refresh_groups").on("click", function() {
        var tournamentId = $("#mtp_matches_tournament_id").val();

        if (!tournamentId) {
          alert('<?php echo esc_js(__('Please enter a Tournament ID first.', 'meinturnierplan-wp')); ?>');
          return;
        }

        var button = $(this);
        var icon = button.find('.dashicons');

        // Show loading state
        button.prop('disabled', true);
        icon.addClass('rotating');

        fetchGroups(tournamentId, true, function() {
          // Reset button state
          button.prop('disabled', false);
          icon.removeClass('rotating');
        });
      });

      // Update preview when fields change
      $("input[id*='mtp_matches_'], select[id*='mtp_matches_']").on("input change", function() {
        // Skip tournament ID as it has its own handler
        if ($(this).attr('id') !== 'mtp_matches_tournament_id') {
          updatePreview();
        }
      });

      // Specifically handle group field changes to ensure preview and shortcode update
      $("#mtp_matches_group").on("change", function() {
        updatePreview();
      });

      function fetchGroups(tournamentId, forceRefresh, callback) {
        if (!tournamentId) return;

        var action = forceRefresh ? 'mtp_refresh_matches_groups' : 'mtp_get_matches_groups';

        var data = {
          action: action,
          tournament_id: tournamentId,
          force_refresh: forceRefresh,
          nonce: '<?php echo wp_create_nonce('mtp_preview_nonce'); ?>'
        };

        $.post(ajaxurl, data, function(response) {
          if (response.success) {
            updateGroupSelect(response.data.groups, response.data.hasFinalRound);

            if (forceRefresh && response.data.refreshed) {
              // Show temporary success message
              showRefreshMessage();
            }
          }

          if (callback) callback();
        });
      }

      function updateGroupSelect(groups, hasFinalRound) {
        var groupSelect = $("#mtp_matches_group");
        var savedValue = $("#mtp_matches_group_saved_value").val();
        var currentValue = groupSelect.val();

        // Clear current options
        groupSelect.empty();

        if (groups && groups.length > 0) {
          // Add group options
          groups.forEach(function(group, index) {
            var groupNumber = index + 1;
            var isSelected = false;

            if (savedValue) {
              isSelected = (savedValue == groupNumber);
            } else if (currentValue) {
              isSelected = (currentValue == groupNumber);
            } else if (index === 0) {
              isSelected = true; // Auto-select first group
            }

            var option = '<option value="' + groupNumber + '"' + (isSelected ? ' selected' : '') + '>' +
                        '<?php echo esc_js(__('Group', 'meinturnierplan-wp')); ?> ' + group.displayId + '</option>';
            groupSelect.append(option);
          });

          // Add Final Round option if it exists
          if (hasFinalRound) {
            var isFinalSelected = (savedValue == '90') || (currentValue == '90');
            var finalOption = '<option value="90"' + (isFinalSelected ? ' selected' : '') + '>' +
                             '<?php echo esc_js(__('Final Round', 'meinturnierplan-wp')); ?></option>';
            groupSelect.append(finalOption);
          }

          // Update description
          var description = groupSelect.closest('td').find('p.description');
          if (groups.length > 1) {
            description.text('<?php echo esc_js(__('Select a specific group to display. Click refresh to update groups from server.', 'meinturnierplan-wp')); ?>');
          } else {
            description.text('<?php echo esc_js(__('This tournament has only one group. Click refresh to update groups from server.', 'meinturnierplan-wp')); ?>');
          }
        } else {
          // No groups - add default option and handle final round
          if (hasFinalRound) {
            var isFinalSelected = (savedValue == '90') || (currentValue == '90');
            groupSelect.append('<option value="90"' + (isFinalSelected ? ' selected' : '') + '><?php echo esc_js(__('Final Round', 'meinturnierplan-wp')); ?></option>');
          } else {
            groupSelect.append('<option value=""><?php echo esc_js(__('Default', 'meinturnierplan-wp')); ?></option>');
          }

          // Update description
          var description = groupSelect.closest('td').find('p.description');
          description.text('<?php echo esc_js(__('No groups found for this tournament. Click refresh to update groups from server.', 'meinturnierplan-wp')); ?>');
        }

        // Trigger preview and shortcode updates after updating group options
        updatePreview();
        if (typeof updateShortcode === 'function') {
          updateShortcode();
        }
      }

      function showRefreshMessage() {
        var button = $("#mtp_matches_refresh_groups");
        var message = $('<div class="mtp-refresh-success" style="margin-left: 10px; color: #46b450; font-size: 12px;">✓ <?php echo esc_js(__('Refreshed', 'meinturnierplan-wp')); ?></div>');

        button.parent().append(message);

        setTimeout(function() {
          message.fadeOut(function() {
            message.remove();
          });
        }, 2000);
      }

      function updatePreview() {
        var tournamentId = $("#mtp_matches_tournament_id").val();

        if (!tournamentId) {
          $("#mtp-matches-preview").html('<div style="text-align: center; color: #666; padding: 40px 0;"><?php echo esc_js(__('Enter a Tournament ID to see live preview', 'meinturnierplan-wp')); ?></div>');
          return;
        }

        var data = {
          action: 'mtp_preview_matches',
          post_id: <?php echo intval($post_id); ?>,
          tournament_id: tournamentId,
          nonce: '<?php echo wp_create_nonce('mtp_preview_nonce'); ?>'
        };

        // Collect all form data
        $("input[id*='mtp_matches_'], select[id*='mtp_matches_']").each(function() {
          var name = $(this).attr('name');
          if (name) {
            data[name] = $(this).val();
          }
        });

        // Ensure color picker values are included (they might not be captured above)
        $(".mtp-color-picker").each(function() {
          var name = $(this).attr('name');
          if (name) {
            // For WordPress color picker, get the actual color value
            var colorValue = $(this).wpColorPicker('color');
            if (colorValue) {
              data[name] = colorValue;
            } else {
              data[name] = $(this).val();
            }
          }
        });

        // Ensure range/slider values are included
        $("input[type='range'][id*='mtp_matches_']").each(function() {
          var name = $(this).attr('name');
          if (name) {
            data[name] = $(this).val();
          }
        });

        $.post(ajaxurl, data, function(response) {
          if (response.success) {
            $("#mtp-matches-preview").html(response.data);
          }
        });
      }

      // Add CSS for rotating animation
      $('<style>.rotating { animation: rotate 1s linear infinite; } @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>').appendTo('head');
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

    $shortcode = '[mtp-matches id="' . esc_attr($meta_values['tournament_id']) . '" post_id="' . $post_id . '" lang="' . esc_attr($meta_values['language']) . '" s-size="' . esc_attr($meta_values['font_size']) . '" s-sizeheader="' . esc_attr($meta_values['header_font_size']) . '" s-color="' . esc_attr($meta_values['text_color']) . '" s-maincolor="' . esc_attr($meta_values['main_color']) . '" s-padding="' . esc_attr($meta_values['table_padding']) . '" s-innerpadding="' . esc_attr($meta_values['inner_padding']) . '" s-bgcolor="' . esc_attr($combined_bg_color). '" s-bcolor="' . esc_attr($meta_values['border_color']) . '" s-bbcolor="' . esc_attr($meta_values['head_bottom_border_color']) . '" s-bgeven="' . esc_attr($combined_even_bg_color) . '" s-bgodd="' . esc_attr($combined_odd_bg_color) . '" s-bgover="' . esc_attr($combined_hover_bg_color) . '" s-bghead="' . esc_attr($combined_head_bg_color) . '" s-bsizeh="' . esc_attr($meta_values['bsizeh']) . '" s-bsizev="' . esc_attr($meta_values['bsizev']) . '" s-bsizeoh="' . esc_attr($meta_values['bsizeoh']) . '" s-bsizeov="' . esc_attr($meta_values['bsizeov']) . '" s-bbsize="' . esc_attr($meta_values['bbsize']) . '" s-ehrsize="' . esc_attr($meta_values['ehrsize']) . '" s-ehrtop="' . esc_attr($meta_values['ehrtop']) . '" s-ehrbottom="' . esc_attr($meta_values['ehrbottom']) . '" s-wrap="' . esc_attr($meta_values['wrap']) . '"' . (!empty($meta_values['group']) ? ' group="' . esc_attr($meta_values['group']) . '"' : '') . ' width="' . esc_attr($meta_values['width']) . '" height="' . esc_attr($meta_values['height']) . '"]';

    return $shortcode;
  }

  /**
   * Render shortcode generator
   */
  private function render_shortcode_generator($shortcode, $tournament_id) {
    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="mtp_matches_shortcode_field" style="display: block; margin-bottom: 5px; font-weight: bold;">' . __('Generated Shortcode:', 'meinturnierplan-wp') . '</label>';
    echo '<textarea id="mtp_matches_shortcode_field" readonly style="width: 100%; height: 120px; font-family: monospace; font-size: 12px; background: #f1f1f1; border: 1px solid #ddd; padding: 8px;">' . esc_textarea($shortcode) . '</textarea>';
    echo '<button type="button" id="mtp_matches_copy_shortcode" class="button button-secondary" style="margin-top: 5px;">' . __('Copy to Clipboard', 'meinturnierplan-wp') . '</button>';
    echo '</div>';

    echo '<div id="mtp_matches_copy_success" style="display: none; margin-top: 5px; padding: 5px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 3px; font-size: 12px;">';
    echo __('Shortcode copied to clipboard!', 'meinturnierplan-wp');
    echo '</div>';

    if (empty($tournament_id)) {
      echo '<div style="margin-top: 10px; padding: 8px; background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; border-radius: 3px;">';
      echo '<strong>' . __('Note:', 'meinturnierplan-wp') . '</strong> ';
      echo __('Enter a Tournament ID above to display live matches data. Without an ID, a placeholder will be shown.', 'meinturnierplan-wp');
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
      // Initialize color pickers for shortcode generation
      if (typeof $.fn.wpColorPicker !== 'undefined') {
        $('.mtp-color-picker').wpColorPicker({
          change: function() {
            updateShortcode();
          },
          clear: function() {
            updateShortcode();
          }
        });
      }

      // Copy shortcode to clipboard
      $("#mtp_matches_copy_shortcode").on("click", function() {
        var shortcodeField = $("#mtp_matches_shortcode_field");
        shortcodeField.select();
        document.execCommand("copy");

        $("#mtp_matches_copy_success").fadeIn().delay(2000).fadeOut();
      });

      // Update shortcode when fields change
      $("input[id*='mtp_matches_'], .mtp-color-picker, select[id*='mtp_matches_']").on("input change", function() {
        updateShortcode();
      });

      // Specifically handle group field changes for shortcode generation
      $("#mtp_matches_group").on("change", function() {
        updateShortcode();
      });

      // Opacity sliders
      $("input[type='range'][id*='mtp_matches_']").on("input", function() {
        updateShortcode();
      });

      function updateShortcode() {
        var postId = <?php echo intval(get_the_ID()); ?>;
        var tournamentId = $("#mtp_matches_tournament_id").val() || "";
        var width = $("#mtp_matches_width").val() || "588";
        var height = $("#mtp_matches_height").val() || "3784";
        var fontSize = $("#mtp_matches_font_size").val() || "9";
        var headerFontSize = $("#mtp_matches_header_font_size").val() || "10";
        var textColor = $("#mtp_matches_text_color").val().replace("#", "") || "000000";
        var mainColor = $("#mtp_matches_main_color").val().replace("#", "") || "173f75";
        var tablePadding = $("#mtp_matches_table_padding").val() || "2";
        var innerPadding = $("#mtp_matches_inner_padding").val() || "5";
        var borderColor = $("#mtp_matches_border_color").val().replace("#", "") || "bbbbbb";
        var headBottomBorderColor = $("#mtp_matches_head_bottom_border_color").val().replace("#", "") || "bbbbbb";
        var bsizeh = $("#mtp_matches_bsizeh").val() || "1";
        var bsizev = $("#mtp_matches_bsizev").val() || "1";
        var bsizeoh = $("#mtp_matches_bsizeoh").val() || "1";
        var bsizeov = $("#mtp_matches_bsizeov").val() || "1";
        var bbsize = $("#mtp_matches_bbsize").val() || "2";
        var ehrsize = $("#mtp_matches_ehrsize").val() || "10";
        var ehrtop = $("#mtp_matches_ehrtop").val() || "9";
        var ehrbottom = $("#mtp_matches_ehrbottom").val() || "3";
        var wrap = $("#mtp_matches_wrap").val() || "false";
        var language = $("#mtp_matches_language").val() || "en";
        var group = $("#mtp_matches_group").val() || "";

        function opacityToHex(opacity) {
          return Math.round(opacity).toString(16).padStart(2, '0');
        }

        // Combine colors with opacity
        var bgColor = $("#mtp_matches_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_matches_bg_opacity").val() / 100) * 255));
        var evenBgColor = $("#mtp_matches_even_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_matches_even_bg_opacity").val() / 100) * 255));
        var oddBgColor = $("#mtp_matches_odd_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_matches_odd_bg_opacity").val() / 100) * 255));
        var hoverBgColor = $("#mtp_matches_hover_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_matches_hover_bg_opacity").val() / 100) * 255));
        var headBgColor = $("#mtp_matches_head_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_matches_head_bg_opacity").val() / 100) * 255));

        // Build complete shortcode
        var newShortcode = '[mtp-matches id="' + tournamentId + '" post_id="' + postId + '" lang="' + language + '"' +
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
                          ' s-bgodd="' + oddBgColor + '"' +
                          ' s-bgover="' + hoverBgColor + '"' +
                          ' s-bghead="' + headBgColor + '"' +
                          ' s-bsizeh="' + bsizeh + '"' +
                          ' s-bsizev="' + bsizev + '"' +
                          ' s-bsizeoh="' + bsizeoh + '"' +
                          ' s-bsizeov="' + bsizeov + '"' +
                          ' s-bbsize="' + bbsize + '"' +
                          ' s-ehrsize="' + ehrsize + '"' +
                          ' s-ehrtop="' + ehrtop + '"' +
                          ' s-ehrbottom="' + ehrbottom + '"' +
                          ' s-wrap="' + wrap + '"' +
                          (group ? ' group="' + group + '"' : '') +
                          ' width="' + width + '" height="' + height + '"]';

        $("#mtp_matches_shortcode_field").val(newShortcode);
      }
    });
    </script>
    <?php
  }

  /**
   * Save meta boxes
   */
  public function save_meta_boxes($post_id) {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    // Check if this is the right post type
    if (get_post_type($post_id) !== 'mtp_matches') {
      return;
    }

    // Check the user's permissions
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }

    // Check the nonce
    if (!isset($_POST['mtp_matches_meta_box_nonce']) || !wp_verify_nonce($_POST['mtp_matches_meta_box_nonce'], 'mtp_matches_meta_box')) {
      return;
    }

    // Save the data
    $fields = array(
      'tournament_id', 'width', 'height', 'font_size', 'header_font_size',
      'table_padding', 'inner_padding', 'text_color', 'main_color', 'bg_color', 'bg_opacity',
      'border_color', 'head_bottom_border_color', 'even_bg_color', 'even_bg_opacity',
      'odd_bg_color', 'odd_bg_opacity', 'hover_bg_color', 'hover_bg_opacity',
      'head_bg_color', 'head_bg_opacity', 'bsizeh', 'bsizev', 'bsizeoh', 'bsizeov', 'bbsize',
      'ehrsize', 'ehrtop', 'ehrbottom', 'wrap', 'language', 'group'
    );

    foreach ($fields as $field) {
      $meta_key = '_mtp_matches_' . $field;
      $value = isset($_POST['mtp_matches_' . $field]) ? sanitize_text_field($_POST['mtp_matches_' . $field]) : '';

      // Strip # prefix from color fields to maintain consistency
      if (strpos($field, 'color') !== false && !empty($value)) {
        $value = ltrim($value, '#');
      }

      update_post_meta($post_id, $meta_key, $value);
    }
  }
}
