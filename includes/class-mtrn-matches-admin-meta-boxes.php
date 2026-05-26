<?php
/**
 * Admin Matches Meta Boxes Class
 *
 * @package MeinTurnierplan
 * @since   0.2.0
 * @version 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Admin Matches Meta Boxes Class
 */
class MTRN_Admin_Matches_Meta_Boxes {

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
      'mtrn_matches_settings',
      __('Matches Settings & Preview', 'meinturnierplan'),
      array($this, 'meta_box_callback'),
      'mtrn_match_list',
      'normal',
      'high'
    );

    add_meta_box(
      'mtrn_matches_shortcode',
      __('Shortcode Generator', 'meinturnierplan'),
      array($this, 'shortcode_meta_box_callback'),
      'mtrn_match_list',
      'side',
      'high'
    );
  }

  /**
   * Meta box callback
   */
  public function meta_box_callback($post) {
    // Add nonce for security
    wp_nonce_field('mtrn_matches_meta_box', 'mtrn_matches_meta_box_nonce');

    // Get current values with defaults
    $meta_values = $this->get_meta_values($post->ID);

    // Start two-column layout
    echo '<div class="mtrn-admin-two-column-layout">';

    // Left column - Matches Settings
    echo '<div class="mtrn-admin-column mtrn-admin-column-left">';
    echo '<h3>' . esc_html__('Matches Settings', 'meinturnierplan') . '</h3>';
    $this->render_settings_form($meta_values);
    echo '</div>';

    // Right column - Preview
    echo '<div class="mtrn-admin-column mtrn-admin-column-right">';
    $this->render_preview_section($post, $meta_values);
    echo '</div>';

    // End two-column layout
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
      'bsizeh' => '1',
      'bsizev' => '1',
      'bsizeoh' => '1',
      'bsizeov' => '1',
      'bbsize' => '2',
      'ehrsize' => '10',
      'ehrtop' => '9',
      'ehrbottom' => '3',
      'projector_presentation' => '0',
      'si' => '0',
      'sf' => '0',
      'st' => '0',
      'sg' => '0',
      'sr' => '0',
      'se' => '0',
      'sp' => '0',
      'sh' => '0',
      'language' => MTRN_Admin_Utilities::get_default_language(),
      'group' => '',
      'participant' => '-1',
      'match_number' => '',
    );

    $meta_values = array();
    foreach ($defaults as $key => $default) {
      $meta_key = '_mtrn_' . $key;
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
    MTRN_Admin_Utilities::render_group_header(__('Basic Settings', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_text_field('mtrn_tournament_id', __('Tournament ID', 'meinturnierplan'), $meta_values['tournament_id'], __('Enter the tournament ID from your language domain (e.g., 1753883027)', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_select_field('mtrn_language', __('Language', 'meinturnierplan'), $meta_values['language'], MTRN_Admin_Utilities::get_language_options(), __('Select the language for the tournament table display.', 'meinturnierplan'));

    // Note: Width and height are now automatically determined by the iframe content via postMessage

    // Display Options Group
    MTRN_Admin_Utilities::render_group_header(
      __('Display Options', 'meinturnierplan')
    );
    MTRN_Admin_Utilities::render_conditional_group_field($meta_values, 'mtrn_', 'matches');
    MTRN_Admin_Utilities::render_conditional_participant_field($meta_values, 'mtrn_');
    MTRN_Admin_Utilities::render_text_field('mtrn_match_number', __('Match number (from-to)', 'meinturnierplan'), $meta_values['match_number'], __('Enter a single match number (e.g., "8") or a range (e.g., "2-7"). Leave empty to display all matches.', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_checkbox_field(
      'mtrn_projector_presentation',
      __('Projector Presentation', 'meinturnierplan'),
      $meta_values['projector_presentation'],
      __('Enable projector presentation mode for the matches table.', 'meinturnierplan')
    );
    MTRN_Admin_Utilities::render_checkbox_field(
      'mtrn_si',
      __('Suppress Match Number', 'meinturnierplan'),
      $meta_values['si'],
      __('Enable suppression of match numbers in the matches table.', 'meinturnierplan')
    );
    MTRN_Admin_Utilities::render_conditional_checkbox_field(
      'mtrn_sf',
      __('Suppress Court', 'meinturnierplan'),
      $meta_values['sf'],
      __('Enable suppression of court information in the matches table.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'showCourts',
      $meta_values['language']
    );
    MTRN_Admin_Utilities::render_conditional_checkbox_field(
      'mtrn_sg',
      __('Suppress Group', 'meinturnierplan'),
      $meta_values['sg'],
      __('Enable suppression of group information in the matches table.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'showGroups',
      $meta_values['language']
    );
    MTRN_Admin_Utilities::render_conditional_checkbox_field(
      'mtrn_sr',
      __('Suppress Referee', 'meinturnierplan'),
      $meta_values['sr'],
      __('Enable suppression of referee information in the matches table.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'showReferees',
      $meta_values['language']
    );
    MTRN_Admin_Utilities::render_checkbox_field(
      'mtrn_st',
      __('Suppress Times', 'meinturnierplan'),
      $meta_values['st'],
      __('Enable suppression of match times in the matches table.', 'meinturnierplan')
    );
    MTRN_Admin_Utilities::render_conditional_checkbox_field(
      'mtrn_se',
      __('Suppress Extra Time', 'meinturnierplan'),
      $meta_values['se'],
      __('Enable suppression of extra time information in the final matches.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'finalMatches',
      $meta_values['language']
    );
    MTRN_Admin_Utilities::render_conditional_checkbox_field(
      'mtrn_sp',
      __('Suppress Penalties', 'meinturnierplan'),
      $meta_values['sp'],
      __('Enable suppression of penalty information in the final matches.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'finalMatches',
      $meta_values['language']
    );
    MTRN_Admin_Utilities::render_conditional_checkbox_field(
      'mtrn_sh',
      __('Suppress Headlines in Final Matches', 'meinturnierplan'),
      $meta_values['sh'],
      __('Enable suppression of headlines in the final matches.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'finalMatches',
      $meta_values['language']
    );

    // Typography Group
    MTRN_Admin_Utilities::render_group_header(__('Typography', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_number_field('mtrn_font_size', __('Content Font Size (pt)', 'meinturnierplan'), $meta_values['font_size'], __('Set the font size of the matches table content. 9pt is the default value.', 'meinturnierplan'), 6, 24);
    MTRN_Admin_Utilities::render_number_field('mtrn_header_font_size', __('Header Font Size (pt)', 'meinturnierplan'), $meta_values['header_font_size'], __('Set the font size of the matches table headers. 10pt is the default value.', 'meinturnierplan'), 6, 24);
    MTRN_Admin_Utilities::render_number_field('mtrn_ehrsize', __('Headlines Font Size (pt)', 'meinturnierplan'), $meta_values['ehrsize'], __('Set the font size of the matches table headlines. 10pt is the default value.', 'meinturnierplan'), 6, 24);

    // Spacing & Layout Group
    MTRN_Admin_Utilities::render_group_header(__('Spacing & Layout', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_number_field('mtrn_table_padding', __('Table Padding (px)', 'meinturnierplan'), $meta_values['table_padding'], __('Set the padding around the matches table. 2px is the default value.', 'meinturnierplan'), 0, 50);
    MTRN_Admin_Utilities::render_number_field('mtrn_inner_padding', __('Inner Padding (px)', 'meinturnierplan'), $meta_values['inner_padding'], __('Set the padding inside the matches table cells. 5px is the default value.', 'meinturnierplan'), 0, 20);
    MTRN_Admin_Utilities::render_number_field('mtrn_ehrtop', __('Headlines Top Padding (px)', 'meinturnierplan'), $meta_values['ehrtop'], __('Set the top padding of the headlines. 9px is the default value.', 'meinturnierplan'), 0, 20);
    MTRN_Admin_Utilities::render_number_field('mtrn_ehrbottom', __('Headlines Bottom Padding (px)', 'meinturnierplan'), $meta_values['ehrbottom'], __('Set the bottom padding of the headlines. 3px is the default value.', 'meinturnierplan'), 0, 20);

    // Border Settings Group
    MTRN_Admin_Utilities::render_group_header(__('Border Settings', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_number_field('mtrn_bsizeh', __('Border Horizontal Size (px)', 'meinturnierplan'), $meta_values['bsizeh'], __('Set the border horizontal size of the matches table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTRN_Admin_Utilities::render_number_field('mtrn_bsizev', __('Border Vertical Size (px)', 'meinturnierplan'), $meta_values['bsizev'], __('Set the border vertical size of the matches table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTRN_Admin_Utilities::render_number_field('mtrn_bsizeoh', __('Table Top and Bottom Border Size (px)', 'meinturnierplan'), $meta_values['bsizeoh'], __('Set the top and bottom border size of the matches table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTRN_Admin_Utilities::render_number_field('mtrn_bsizeov', __('Table Left and Right Border Size (px)', 'meinturnierplan'), $meta_values['bsizeov'], __('Set the left and right border size of the matches table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTRN_Admin_Utilities::render_number_field('mtrn_bbsize', __('Table Head Border Bottom Size (px)', 'meinturnierplan'), $meta_values['bbsize'], __('Set the head border bottom size of the matches table. 2px is the default value.', 'meinturnierplan'), 1, 10);

    // Colors Group
    MTRN_Admin_Utilities::render_group_header(__('Colors', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_color_field('mtrn_text_color', __('Text Color', 'meinturnierplan'), $meta_values['text_color'], __('Set the color of the tournament table text. Black (#000000) is the default value.', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_color_field('mtrn_main_color', __('Link & Navigation Color', 'meinturnierplan'), $meta_values['main_color'], __('Set the color for links (e.g., "Show Full Tournament") and navigation arrows between groups. Blue (#173f75) is the default value.', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_color_field('mtrn_border_color', __('Border Color', 'meinturnierplan'), $meta_values['border_color'], __('Set the border color of the tournament table. Light gray (#bbbbbb) is the default value.', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_color_field('mtrn_head_bottom_border_color', __('Table Head Bottom Border Color', 'meinturnierplan'), $meta_values['head_bottom_border_color'], __('Set the bottom border color of the table header. Light gray (#bbbbbb) is the default value.', 'meinturnierplan'));

    // Background Colors Group
    MTRN_Admin_Utilities::render_group_header(__('Background Colors', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_color_opacity_field('mtrn_bg_color', 'mtrn_bg_opacity', __('Background Color', 'meinturnierplan'), $meta_values['bg_color'], $meta_values['bg_opacity'], __('Set the background color and opacity of the tournament table. Use opacity 0% for transparent background.', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_color_opacity_field('mtrn_head_bg_color', 'mtrn_head_bg_opacity', __('Head Background Color', 'meinturnierplan'), $meta_values['head_bg_color'], $meta_values['head_bg_opacity'], __('Set the background color and opacity for table head. Use opacity 0% for transparent background.', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_color_opacity_field('mtrn_even_bg_color', 'mtrn_even_bg_opacity', __('Even Rows Background Color', 'meinturnierplan'), $meta_values['even_bg_color'], $meta_values['even_bg_opacity'], __('Set the background color and opacity for even-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_color_opacity_field('mtrn_odd_bg_color', 'mtrn_odd_bg_opacity', __('Odd Rows Background Color', 'meinturnierplan'), $meta_values['odd_bg_color'], $meta_values['odd_bg_opacity'], __('Set the background color and opacity for odd-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan'));
    MTRN_Admin_Utilities::render_color_opacity_field('mtrn_hover_bg_color', 'mtrn_hover_bg_opacity', __('Row Hover Background Color', 'meinturnierplan'), $meta_values['hover_bg_color'], $meta_values['hover_bg_opacity'], __('Set the background color and opacity for table rows hover. Use opacity 0% for transparent background.', 'meinturnierplan'));

    echo '</table>';

    // Hidden fields for width and height (updated by JavaScript when iframe dimensions change)
    echo '<input type="hidden" id="mtrn_width" name="mtrn_width" value="' . esc_attr($meta_values['width']) . '" />';
    echo '<input type="hidden" id="mtrn_height" name="mtrn_height" value="' . esc_attr($meta_values['height']) . '" />';
  }



  /**
   * Render preview section
   */
  private function render_preview_section($post, $meta_values) {
    echo '<h3>' . esc_html__('Preview', 'meinturnierplan') . '</h3>';
    echo '<div id="mtrn-preview">';

    // Create attributes for preview
    $atts = $this->build_preview_attributes($meta_values);
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is already escaped in the renderer class
    echo $this->matches_renderer->render_matches_html($post->ID, $atts);

    echo '</div>';
  }

  /**
   * Build preview attributes from meta values
   */
  private function build_preview_attributes($meta_values) {
    // Check tournament features if tournament ID is provided
    $tournament_id = $meta_values['tournament_id'];
    $show_courts = false;
    $show_groups = false;
    $show_referees = false;
    $has_final_matches = false;

    if (!empty($tournament_id)) {
      $show_courts = MTRN_Admin_Utilities::fetch_tournament_option($tournament_id, 'showCourts', $meta_values['language']) === true;
      $show_groups = MTRN_Admin_Utilities::fetch_tournament_option($tournament_id, 'showGroups', $meta_values['language']) === true;
      $show_referees = MTRN_Admin_Utilities::fetch_tournament_option($tournament_id, 'showReferees', $meta_values['language']) === true;

      // Check if finalMatches exists (not null and not undefined)
      $final_matches = MTRN_Admin_Utilities::fetch_tournament_option($tournament_id, 'finalMatches', $meta_values['language']);
      $has_final_matches = ($final_matches !== null);
    }

    // Combine colors with opacity
    $combined_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['bg_color'], $meta_values['bg_opacity']);
    $combined_even_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['even_bg_color'], $meta_values['even_bg_opacity']);
    $combined_odd_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['odd_bg_color'], $meta_values['odd_bg_opacity']);
    $combined_hover_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['hover_bg_color'], $meta_values['hover_bg_opacity']);
    $combined_head_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['head_bg_color'], $meta_values['head_bg_opacity']);

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
      's-ehrsize' => $meta_values['ehrsize'],
      's-ehrtop' => $meta_values['ehrtop'],
      's-ehrbottom' => $meta_values['ehrbottom'],
      'setlang' => $meta_values['language']
    );

    // Add group parameter if specified
    if (!empty($meta_values['group'])) {
      $atts_array['group'] = $meta_values['group'];
    }

    // Add participant parameter if specified and not default "All"
    if (!empty($meta_values['participant']) && $meta_values['participant'] !== '-1') {
      $atts_array['participant'] = $meta_values['participant'];
    }

    // Add match_number parameter if specified
    if (!empty($meta_values['match_number'])) {
      $atts_array['match_number'] = $meta_values['match_number'];
    }

    // Add bm parameter if projector_presentation is enabled
    if (!empty($meta_values['projector_presentation']) && $meta_values['projector_presentation'] === '1') {
      $atts_array['bm'] = '1';
    }

    // Add si parameter if si is enabled
    if (!empty($meta_values['si']) && $meta_values['si'] === '1') {
      $atts_array['si'] = '1';
    }

    // Add sf parameter if sf is enabled (Suppress Court) AND tournament supports courts
    if ($show_courts && !empty($meta_values['sf']) && $meta_values['sf'] === '1') {
      $atts_array['sf'] = '1';
    }

    // Add st parameter if st is enabled
    if (!empty($meta_values['st']) && $meta_values['st'] === '1') {
      $atts_array['st'] = '1';
    }

    // Add sg parameter if sg is enabled (Suppress Group) AND tournament supports groups
    if ($show_groups && !empty($meta_values['sg']) && $meta_values['sg'] === '1') {
      $atts_array['sg'] = '1';
    }

    // Add sr parameter if sr is enabled (Suppress Referee) AND tournament supports referees
    if ($show_referees && !empty($meta_values['sr']) && $meta_values['sr'] === '1') {
      $atts_array['sr'] = '1';
    }

    // Add se parameter if se is enabled (Suppress Extra Time) AND tournament has final matches
    if ($has_final_matches && !empty($meta_values['se']) && $meta_values['se'] === '1') {
      $atts_array['se'] = '1';
    }

    // Add sp parameter if sp is enabled (Suppress Penalties) AND tournament has final matches
    if ($has_final_matches && !empty($meta_values['sp']) && $meta_values['sp'] === '1') {
      $atts_array['sp'] = '1';
    }

    // Add sh parameter if sh is enabled (Suppress Headlines in Final Matches) AND tournament has final matches
    if ($has_final_matches && !empty($meta_values['sh']) && $meta_values['sh'] === '1') {
      $atts_array['sh'] = '1';
    }

    return $atts_array;
  }

  /**
   * Add preview JavaScript
   */
  private function add_preview_javascript($post_id) {
    // Include reusable admin JavaScript utilities
    MTRN_Admin_Utilities::render_admin_javascript_utilities(array(
      'ajax_actions' => array('mtrn_get_matches_groups', 'mtrn_refresh_matches_groups'),
      'ajax_actions_teams' => array('mtrn_get_matches_teams', 'mtrn_refresh_matches_teams')
    ));

    // Enqueue preview JavaScript file
    wp_enqueue_script(
      'mtrn-admin-matches-preview',
      plugins_url('assets/js/admin-matches-preview.js', dirname(__FILE__)),
      array('jquery', 'mtrn-admin-utilities'),
      '1.0.0',
      true
    );

    // Localize script with configuration
    wp_localize_script(
      'mtrn-admin-matches-preview',
      'mtrnMatchesPreviewConfig',
      array(
        'postId' => intval($post_id),
        'checkOptionNonce' => wp_create_nonce('mtrn_check_option_nonce'),
        'previewNonce' => wp_create_nonce('mtrn_preview_nonce')
      )
    );
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
    $combined_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['bg_color'], $meta_values['bg_opacity']);
    $combined_even_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['even_bg_color'], $meta_values['even_bg_opacity']);
    $combined_odd_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['odd_bg_color'], $meta_values['odd_bg_opacity']);
    $combined_hover_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['hover_bg_color'], $meta_values['hover_bg_opacity']);
    $combined_head_bg_color = MTRN_Admin_Utilities::combine_color_opacity($meta_values['head_bg_color'], $meta_values['head_bg_opacity']);

    $shortcode = '[mtrn-matches id="' . esc_attr($meta_values['tournament_id']) . '" post_id="' . $post_id . '" lang="' . esc_attr($meta_values['language']) . '" s-size="' . esc_attr($meta_values['font_size']) . '" s-sizeheader="' . esc_attr($meta_values['header_font_size']) . '" s-color="' . esc_attr($meta_values['text_color']) . '" s-maincolor="' . esc_attr($meta_values['main_color']) . '" s-padding="' . esc_attr($meta_values['table_padding']) . '" s-innerpadding="' . esc_attr($meta_values['inner_padding']) . '" s-bgcolor="' . esc_attr($combined_bg_color). '" s-bcolor="' . esc_attr($meta_values['border_color']) . '" s-bbcolor="' . esc_attr($meta_values['head_bottom_border_color']) . '" s-bgeven="' . esc_attr($combined_even_bg_color) . '" s-bsizeh="' . esc_attr($meta_values['bsizeh']) . '" s-bsizev="' . esc_attr($meta_values['bsizev']) . '" s-bsizeoh="' . esc_attr($meta_values['bsizeoh']) . '" s-bsizeov="' . esc_attr($meta_values['bsizeov']) . '" s-bbsize="' . esc_attr($meta_values['bbsize']) . '" s-ehrsize="' . esc_attr($meta_values['ehrsize']) . '" s-ehrtop="' . esc_attr($meta_values['ehrtop']) . '" s-ehrbottom="' . esc_attr($meta_values['ehrbottom']). '" s-bgodd="' . esc_attr($combined_odd_bg_color) . '" s-bgover="' . esc_attr($combined_hover_bg_color) . '" s-bghead="' . esc_attr($combined_head_bg_color) . '"';

    // Add group parameter if specified
    if (!empty($meta_values['group'])) {
      $shortcode .= ' group="' . esc_attr($meta_values['group']) . '"';
    }

    // Add participant parameter if specified and not default "All"
    if (!empty($meta_values['participant']) && $meta_values['participant'] !== '-1') {
      $shortcode .= ' participant="' . esc_attr($meta_values['participant']) . '"';
    }

    // Add gamenumbers parameter if match_number is specified
    if (!empty($meta_values['match_number'])) {
      $shortcode .= ' gamenumbers="' . esc_attr($meta_values['match_number']) . '"';
    }

    // Add bm parameter if projector_presentation is enabled
    if (!empty($meta_values['projector_presentation']) && $meta_values['projector_presentation'] === '1') {
      $shortcode .= ' bm="1"';
    }

    // Add si parameter if si is enabled
    if (!empty($meta_values['si']) && $meta_values['si'] === '1') {
      $shortcode .= ' si="1"';
    }

    // Add sf parameter if sf is enabled (Suppress Court)
    if (!empty($meta_values['sf']) && $meta_values['sf'] === '1') {
      $shortcode .= ' sf="1"';
    }

    // Add st parameter if st is enabled
    if (!empty($meta_values['st']) && $meta_values['st'] === '1') {
      $shortcode .= ' st="1"';
    }

    // Add sg parameter if sg is enabled
    if (!empty($meta_values['sg']) && $meta_values['sg'] === '1') {
      $shortcode .= ' sg="1"';
    }

    // Add sr parameter if sr is enabled (Suppress Referee)
    if (!empty($meta_values['sr']) && $meta_values['sr'] === '1') {
      $shortcode .= ' sr="1"';
    }

    // Add se parameter if se is enabled
    if (!empty($meta_values['se']) && $meta_values['se'] === '1') {
      $shortcode .= ' se="1"';
    }

    // Add sp parameter if sp is enabled
    if (!empty($meta_values['sp']) && $meta_values['sp'] === '1') {
      $shortcode .= ' sp="1"';
    }

    // Add sh parameter if sh is enabled
    if (!empty($meta_values['sh']) && $meta_values['sh'] === '1') {
      $shortcode .= ' sh="1"';
    }

    // Add width and height parameters for iframe sizing
    $shortcode .= ' width="' . esc_attr($meta_values['width']) . '" height="' . esc_attr($meta_values['height']) . '"';

    $shortcode .= ']';

    return $shortcode;
  }

  /**
   * Render shortcode generator
   */
  private function render_shortcode_generator($shortcode, $tournament_id) {
    // First define the update function, then use shared utilities
    $meta_values = $this->get_meta_values(get_the_ID());
    $this->add_shortcode_update_javascript($meta_values);

    // Use shared utilities for the shortcode generator UI - but disable automatic field listeners
    // since we need to ensure updateShortcode is defined first
    $config = array(
      'shortcode_updater_callback' => 'updateShortcode' // This will be checked after our function is defined
    );
    MTRN_Admin_Utilities::render_shortcode_generator($shortcode, $tournament_id, $config);
  }  /**
   * Add tournament table specific shortcode update JavaScript
   */
  private function add_shortcode_update_javascript($meta_values) {
    // Enqueue external JavaScript file
    wp_enqueue_script(
      'mtrn-admin-matches-meta-boxes',
      plugins_url('assets/js/admin-matches-meta-boxes.js', dirname(__FILE__)),
      array('jquery'),
      '1.0.0',
      true
    );

    // Localize script with configuration
    wp_localize_script(
      'mtrn-admin-matches-meta-boxes',
      'mtrnMatchesMetaBoxConfig',
      array(
        'postId' => intval(get_the_ID()),
        'defaultWidth' => esc_js($meta_values['width']),
        'defaultHeight' => esc_js($meta_values['height'])
      )
    );
  }

  /**
   * Save meta box data
   */
  public function save_meta_boxes($post_id) {
    // Check if nonce is valid
    if (!isset($_POST['mtrn_matches_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mtrn_matches_meta_box_nonce'])), 'mtrn_matches_meta_box')) {
      return;
    }

    // Check if user has permission
    if (isset($_POST['post_type']) && 'mtrn_match_list' == $_POST['post_type']) {
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
      'tournament_id',
      'width',
      'height',
      'font_size',
      'header_font_size',
      'bsizeh',
      'bsizev',
      'bsizeoh',
      'bsizeov',
      'bbsize',
      'ehrsize',
      'ehrtop',
      'ehrbottom',
      'table_padding',
      'inner_padding',
      'text_color',
      'main_color',
      'bg_color',
      'bg_opacity',
      'border_color',
      'head_bottom_border_color',
      'even_bg_color',
      'even_bg_opacity',
      'odd_bg_color',
      'odd_bg_opacity',
      'hover_bg_color',
      'hover_bg_opacity',
      'head_bg_color',
      'head_bg_opacity',
      'projector_presentation',
      'si',
      'sf',
      'st',
      'sg',
      'sr',
      'se',
      'sp',
      'sh',
      'language',
      'group',
      'participant',
      'match_number'
    );

    foreach ($meta_fields as $field) {
      $post_field = 'mtrn_' . $field;
      $meta_key = '_mtrn_' . $field;

      if (in_array($field, array('projector_presentation', 'si', 'sf', 'st', 'sg', 'sr', 'se', 'sp', 'sh'))) {
        // Handle checkbox: if not checked, it won't be in $_POST
        $value = isset($_POST[$post_field]) ? '1' : '0';
        update_post_meta($post_id, $meta_key, $value);
      } elseif (isset($_POST[$post_field])) {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitization handled by sanitize_meta_value method
        $value = $this->sanitize_meta_value($field, wp_unslash($_POST[$post_field]));
        update_post_meta($post_id, $meta_key, $value);
      }
    }
  }

  /**
   * Sanitize meta value based on field type
   */
  private function sanitize_meta_value($field, $value) {
    return MTRN_Admin_Utilities::sanitize_meta_value($field, $value);
  }
}
