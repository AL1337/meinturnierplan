<?php
/**
 * Admin Matches Meta Boxes Class
 *
 * @package MeinTurnierplan
 * @since 0.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Admin Matches Meta Boxes Class
 */
class MTP_Admin_Matches_Meta_Boxes {

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
      __('Matches Settings & Preview', 'meinturnierplan'),
      array($this, 'meta_box_callback'),
      'mtp_match_list',
      'normal',
      'high'
    );

    add_meta_box(
      'mtp_matches_shortcode',
      __('Shortcode Generator', 'meinturnierplan'),
      array($this, 'shortcode_meta_box_callback'),
      'mtp_match_list',
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
    echo '<h3>' . __('Matches Settings', 'meinturnierplan') . '</h3>';
    $this->render_settings_form($meta_values);
    echo '</div>';

    // Right column - Preview
    echo '<div class="mtp-admin-column mtp-admin-column-right">';
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
      'language' => MTP_Admin_Utilities::get_default_language(),
      'group' => '',
      'participant' => '-1',
      'match_number' => '',
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
    MTP_Admin_Utilities::render_group_header(__('Basic Settings', 'meinturnierplan'));
    MTP_Admin_Utilities::render_text_field('mtp_tournament_id', __('Tournament ID', 'meinturnierplan'), $meta_values['tournament_id'], __('Enter the tournament ID from meinturnierplan.de (e.g., 1753883027)', 'meinturnierplan'));
    MTP_Admin_Utilities::render_select_field('mtp_language', __('Language', 'meinturnierplan'), $meta_values['language'], MTP_Admin_Utilities::get_language_options(), __('Select the language for the tournament table display.', 'meinturnierplan'));

    // Note: Width and height are now automatically determined by the iframe content via postMessage

    // Display Options Group
    MTP_Admin_Utilities::render_group_header(
      __('Display Options', 'meinturnierplan')
    );
    MTP_Admin_Utilities::render_conditional_group_field($meta_values, 'mtp_', 'matches');
    MTP_Admin_Utilities::render_conditional_participant_field($meta_values, 'mtp_');
    MTP_Admin_Utilities::render_text_field('mtp_match_number', __('Match number (from-to)', 'meinturnierplan'), $meta_values['match_number'], __('Enter a single match number (e.g., "8") or a range (e.g., "2-7"). Leave empty to display all matches.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_checkbox_field(
      'mtp_projector_presentation',
      __('Projector Presentation', 'meinturnierplan'),
      $meta_values['projector_presentation'],
      __('Enable projector presentation mode for the matches table.', 'meinturnierplan')
    );
    MTP_Admin_Utilities::render_checkbox_field(
      'mtp_si',
      __('Suppress Match Number', 'meinturnierplan'),
      $meta_values['si'],
      __('Enable suppression of match numbers in the matches table.', 'meinturnierplan')
    );
    MTP_Admin_Utilities::render_conditional_checkbox_field(
      'mtp_sf',
      __('Suppress Court', 'meinturnierplan'),
      $meta_values['sf'],
      __('Enable suppression of court information in the matches table.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'showCourts'
    );
    MTP_Admin_Utilities::render_conditional_checkbox_field(
      'mtp_sg',
      __('Suppress Group', 'meinturnierplan'),
      $meta_values['sg'],
      __('Enable suppression of group information in the matches table.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'showGroups'
    );
    MTP_Admin_Utilities::render_conditional_checkbox_field(
      'mtp_sr',
      __('Suppress Referee', 'meinturnierplan'),
      $meta_values['sr'],
      __('Enable suppression of referee information in the matches table.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'showReferees'
    );
    MTP_Admin_Utilities::render_checkbox_field(
      'mtp_st',
      __('Suppress Times', 'meinturnierplan'),
      $meta_values['st'],
      __('Enable suppression of match times in the matches table.', 'meinturnierplan')
    );
    MTP_Admin_Utilities::render_conditional_checkbox_field(
      'mtp_se',
      __('Suppress Extra Time', 'meinturnierplan'),
      $meta_values['se'],
      __('Enable suppression of extra time information in the final matches.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'finalMatches'
    );
    MTP_Admin_Utilities::render_conditional_checkbox_field(
      'mtp_sp',
      __('Suppress Penalties', 'meinturnierplan'),
      $meta_values['sp'],
      __('Enable suppression of penalty information in the final matches.', 'meinturnierplan'),
      $meta_values['tournament_id'],
      'finalMatches'
    );

    // Typography Group
    MTP_Admin_Utilities::render_group_header(__('Typography', 'meinturnierplan'));
    MTP_Admin_Utilities::render_number_field('mtp_font_size', __('Content Font Size (pt)', 'meinturnierplan'), $meta_values['font_size'], __('Set the font size of the matches table content. 9pt is the default value.', 'meinturnierplan'), 6, 24);
    MTP_Admin_Utilities::render_number_field('mtp_header_font_size', __('Header Font Size (pt)', 'meinturnierplan'), $meta_values['header_font_size'], __('Set the font size of the matches table headers. 10pt is the default value.', 'meinturnierplan'), 6, 24);
    MTP_Admin_Utilities::render_number_field('mtp_ehrsize', __('Headlines Font Size (pt)', 'meinturnierplan'), $meta_values['ehrsize'], __('Set the font size of the matches table headlines. 10pt is the default value.', 'meinturnierplan'), 6, 24);

    // Spacing & Layout Group
    MTP_Admin_Utilities::render_group_header(__('Spacing & Layout', 'meinturnierplan'));
    MTP_Admin_Utilities::render_number_field('mtp_table_padding', __('Table Padding (px)', 'meinturnierplan'), $meta_values['table_padding'], __('Set the padding around the matches table. 2px is the default value.', 'meinturnierplan'), 0, 50);
    MTP_Admin_Utilities::render_number_field('mtp_inner_padding', __('Inner Padding (px)', 'meinturnierplan'), $meta_values['inner_padding'], __('Set the padding inside the matches table cells. 5px is the default value.', 'meinturnierplan'), 0, 20);
    MTP_Admin_Utilities::render_number_field('mtp_ehrtop', __('Headlines Top Padding (px)', 'meinturnierplan'), $meta_values['ehrtop'], __('Set the top padding of the headlines. 9px is the default value.', 'meinturnierplan'), 0, 20);
    MTP_Admin_Utilities::render_number_field('mtp_ehrbottom', __('Headlines Bottom Padding (px)', 'meinturnierplan'), $meta_values['ehrbottom'], __('Set the bottom padding of the headlines. 3px is the default value.', 'meinturnierplan'), 0, 20);

    // Border Settings Group
    MTP_Admin_Utilities::render_group_header(__('Border Settings', 'meinturnierplan'));
    MTP_Admin_Utilities::render_number_field('mtp_bsizeh', __('Border Vertical Size (px)', 'meinturnierplan'), $meta_values['bsizeh'], __('Set the border vertical size of the matches table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTP_Admin_Utilities::render_number_field('mtp_bsizev', __('Border Horizontal Size (px)', 'meinturnierplan'), $meta_values['bsizev'], __('Set the border horizontal size of the matches table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTP_Admin_Utilities::render_number_field('mtp_bsizeoh', __('Table Block Border Size (px)', 'meinturnierplan'), $meta_values['bsizeoh'], __('Set the block border size of the matches table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTP_Admin_Utilities::render_number_field('mtp_bsizeov', __('Table Inline Border Size (px)', 'meinturnierplan'), $meta_values['bsizeov'], __('Set the inline border size of the matches table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTP_Admin_Utilities::render_number_field('mtp_bbsize', __('Table Head Border Bottom Size (px)', 'meinturnierplan'), $meta_values['bbsize'], __('Set the head border bottom size of the matches table. 2px is the default value.', 'meinturnierplan'), 1, 10);

    // Colors Group
    MTP_Admin_Utilities::render_group_header(__('Colors', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_field('mtp_text_color', __('Text Color', 'meinturnierplan'), $meta_values['text_color'], __('Set the color of the tournament table text. Black (#000000) is the default value.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_field('mtp_main_color', __('Main Color', 'meinturnierplan'), $meta_values['main_color'], __('Set the main color of the tournament table (headers, highlights). Blue (#173f75) is the default value.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_field('mtp_border_color', __('Border Color', 'meinturnierplan'), $meta_values['border_color'], __('Set the border color of the tournament table. Light gray (#bbbbbb) is the default value.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_field('mtp_head_bottom_border_color', __('Table Head Bottom Border Color', 'meinturnierplan'), $meta_values['head_bottom_border_color'], __('Set the bottom border color of the table header. Light gray (#bbbbbb) is the default value.', 'meinturnierplan'));

    // Background Colors Group
    MTP_Admin_Utilities::render_group_header(__('Background Colors', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_opacity_field('mtp_bg_color', 'mtp_bg_opacity', __('Background Color', 'meinturnierplan'), $meta_values['bg_color'], $meta_values['bg_opacity'], __('Set the background color and opacity of the tournament table. Use opacity 0% for transparent background.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_opacity_field('mtp_head_bg_color', 'mtp_head_bg_opacity', __('Head Background Color', 'meinturnierplan'), $meta_values['head_bg_color'], $meta_values['head_bg_opacity'], __('Set the background color and opacity for table head. Use opacity 0% for transparent background.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_opacity_field('mtp_even_bg_color', 'mtp_even_bg_opacity', __('Even Rows Background Color', 'meinturnierplan'), $meta_values['even_bg_color'], $meta_values['even_bg_opacity'], __('Set the background color and opacity for even-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_opacity_field('mtp_odd_bg_color', 'mtp_odd_bg_opacity', __('Odd Rows Background Color', 'meinturnierplan'), $meta_values['odd_bg_color'], $meta_values['odd_bg_opacity'], __('Set the background color and opacity for odd-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_opacity_field('mtp_hover_bg_color', 'mtp_hover_bg_opacity', __('Row Hover Background Color', 'meinturnierplan'), $meta_values['hover_bg_color'], $meta_values['hover_bg_opacity'], __('Set the background color and opacity for table rows hover. Use opacity 0% for transparent background.', 'meinturnierplan'));

    echo '</table>';

    // Hidden fields for width and height (updated by JavaScript when iframe dimensions change)
    echo '<input type="hidden" id="mtp_width" name="mtp_width" value="' . esc_attr($meta_values['width']) . '" />';
    echo '<input type="hidden" id="mtp_height" name="mtp_height" value="' . esc_attr($meta_values['height']) . '" />';
  }



  /**
   * Render preview section
   */
  private function render_preview_section($post, $meta_values) {
    echo '<h3>' . __('Preview', 'meinturnierplan') . '</h3>';
    echo '<div id="mtp-preview">';

    // Create attributes for preview
    $atts = $this->build_preview_attributes($meta_values);
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
      $show_courts = MTP_Admin_Utilities::fetch_tournament_option($tournament_id, 'showCourts') === true;
      $show_groups = MTP_Admin_Utilities::fetch_tournament_option($tournament_id, 'showGroups') === true;
      $show_referees = MTP_Admin_Utilities::fetch_tournament_option($tournament_id, 'showReferees') === true;

      // Check if finalMatches exists (not null and not undefined)
      $final_matches = MTP_Admin_Utilities::fetch_tournament_option($tournament_id, 'finalMatches');
      $has_final_matches = ($final_matches !== null);
    }

    // Combine colors with opacity
    $combined_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['bg_color'], $meta_values['bg_opacity']);
    $combined_even_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['even_bg_color'], $meta_values['even_bg_opacity']);
    $combined_odd_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['odd_bg_color'], $meta_values['odd_bg_opacity']);
    $combined_hover_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['hover_bg_color'], $meta_values['hover_bg_opacity']);
    $combined_head_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['head_bg_color'], $meta_values['head_bg_opacity']);

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

    // Add sh parameter if sh is enabled
    if (!empty($meta_values['sh']) && $meta_values['sh'] === '1') {
      $atts_array['sh'] = '1';
    }

    return $atts_array;
  }

  /**
   * Add preview JavaScript
   */
  private function add_preview_javascript($post_id) {
    // Include reusable admin JavaScript utilities
    MTP_Admin_Utilities::render_admin_javascript_utilities(array(
      'ajax_actions' => array('mtp_get_matches_groups', 'mtp_refresh_matches_groups'),
      'ajax_actions_teams' => array('mtp_get_matches_teams', 'mtp_refresh_matches_teams')
    ));
    ?>
    <script>
    jQuery(document).ready(function($) {
      // Initialize reusable utilities with preview update callback
      MTPAdminUtils.initColorPickers(updatePreview);
      MTPAdminUtils.initOpacitySliders(updatePreview);
      MTPAdminUtils.initFormFieldListeners('mtp_', updatePreview);

      // Initialize tournament ID field with group and team loading
      MTPAdminUtils.initTournamentIdField('#mtp_tournament_id', updatePreview, function(tournamentId) {
        MTPAdminUtils.loadTournamentGroups(tournamentId, {context: 'matches'});
        MTPAdminUtils.loadTournamentTeams(tournamentId);
        // Check tournament data for conditional fields
        checkConditionalFields(tournamentId);
      });

      // Function to check tournament data and show/hide conditional fields
      function checkConditionalFields(tournamentId) {
        if (!tournamentId) {
          // Hide all conditional fields if no tournament ID
          $('#mtp_sf_row').hide();
          $('#mtp_sg_row').hide();
          $('#mtp_sr_row').hide();
          $('#mtp_se_row').hide();
          $('#mtp_sp_row').hide();
          return;
        }

        // Fetch tournament data for showCourts via WordPress AJAX to avoid CORS issues
        $.ajax({
          url: ajaxurl,
          type: 'POST',
          data: {
            action: 'mtp_check_tournament_option',
            tournament_id: tournamentId,
            option_name: 'showCourts',
            nonce: '<?php echo wp_create_nonce('mtp_check_option_nonce'); ?>'
          },
          success: function(response) {
            if (response.success && response.data) {
              var showCourtsValue = response.data.value;

              // Show/hide Suppress Court field based on showCourts
              if (showCourtsValue === true) {
                $('#mtp_sf_row').show();
              } else {
                $('#mtp_sf_row').hide();
              }
            } else {
              $('#mtp_sf_row').hide();
            }
          },
          error: function(xhr, status, error) {
            // On error, hide the field
            $('#mtp_sf_row').hide();
          }
        });

        // Fetch tournament data for showGroups via WordPress AJAX to avoid CORS issues
        $.ajax({
          url: ajaxurl,
          type: 'POST',
          data: {
            action: 'mtp_check_tournament_option',
            tournament_id: tournamentId,
            option_name: 'showGroups',
            nonce: '<?php echo wp_create_nonce('mtp_check_option_nonce'); ?>'
          },
          success: function(response) {
            if (response.success && response.data) {
              var showGroupsValue = response.data.value;

              // Show/hide Suppress Group field based on showGroups
              if (showGroupsValue === true) {
                $('#mtp_sg_row').show();
              } else {
                $('#mtp_sg_row').hide();
              }
            } else {
              $('#mtp_sg_row').hide();
            }
          },
          error: function(xhr, status, error) {
            // On error, hide the field
            $('#mtp_sg_row').hide();
          }
        });

        // Fetch tournament data for showReferees via WordPress AJAX to avoid CORS issues
        $.ajax({
          url: ajaxurl,
          type: 'POST',
          data: {
            action: 'mtp_check_tournament_option',
            tournament_id: tournamentId,
            option_name: 'showReferees',
            nonce: '<?php echo wp_create_nonce('mtp_check_option_nonce'); ?>'
          },
          success: function(response) {
            if (response.success && response.data) {
              var showRefereesValue = response.data.value;

              // Show/hide Suppress Referee field based on showReferees
              if (showRefereesValue === true) {
                $('#mtp_sr_row').show();
              } else {
                $('#mtp_sr_row').hide();
              }
            } else {
              $('#mtp_sr_row').hide();
            }
          },
          error: function(xhr, status, error) {
            // On error, hide the field
            $('#mtp_sr_row').hide();
          }
        });

        // Fetch tournament data for finalMatches via WordPress AJAX to avoid CORS issues
        $.ajax({
          url: ajaxurl,
          type: 'POST',
          data: {
            action: 'mtp_check_tournament_option',
            tournament_id: tournamentId,
            option_name: 'finalMatches',
            nonce: '<?php echo wp_create_nonce('mtp_check_option_nonce'); ?>'
          },
          success: function(response) {
            if (response.success && response.data) {
              var finalMatchesValue = response.data.value;

              // Show Suppress Extra Time and Suppress Penalties fields if finalMatches exists (array or not empty)
              if (finalMatchesValue !== null && finalMatchesValue !== undefined) {
                $('#mtp_se_row').show();
                $('#mtp_sp_row').show();
              } else {
                $('#mtp_se_row').hide();
                $('#mtp_sp_row').hide();
              }
            } else {
              $('#mtp_se_row').hide();
              $('#mtp_sp_row').hide();
            }
          },
          error: function(xhr, status, error) {
            // On error, hide the fields
            $('#mtp_se_row').hide();
            $('#mtp_sp_row').hide();
          }
        });
      }

      // Initialize group refresh button
      MTPAdminUtils.initGroupRefreshButton('#mtp_refresh_groups', '#mtp_tournament_id', function(tournamentId, options) {
        options = options || {};
        options.context = 'matches';
        MTPAdminUtils.loadTournamentGroups(tournamentId, options);
      });

      // Initialize participant refresh button
      MTPAdminUtils.initParticipantRefreshButton('#mtp_refresh_participants', '#mtp_tournament_id', function(tournamentId, options) {
        MTPAdminUtils.loadTournamentTeams(tournamentId, options);
      });

      // Load groups and teams on page load if tournament ID exists
      var initialTournamentId = $("#mtp_tournament_id").val();

      if (initialTournamentId) {
        MTPAdminUtils.loadTournamentGroups(initialTournamentId, {preserveSelection: false, context: 'matches'});
        MTPAdminUtils.loadTournamentTeams(initialTournamentId, {preserveSelection: false});
        checkConditionalFields(initialTournamentId);
      } else {
        // Hide conditional fields if no tournament ID on load
        $('#mtp_sf_row').hide();
        $('#mtp_sg_row').hide();
        $('#mtp_sr_row').hide();
        $('#mtp_se_row').hide();
        $('#mtp_sp_row').hide();
      }

      // Additional explicit listeners for checkboxes to ensure they work even when dynamically shown/hidden
      // Use event delegation to handle dynamically shown fields
      $(document).on('change', 'input[type="checkbox"][id^="mtp_"]', function() {
        updatePreview();
      });

      // Function to update preview
      function updatePreview() {
        // Get all field values
        var data = {
          post_id: <?php echo intval($post_id); ?>,
          tournament_id: $("#mtp_tournament_id").val(),
          font_size: $("#mtp_font_size").val(),
          header_font_size: $("#mtp_header_font_size").val(),
          bsizeh: $("#mtp_bsizeh").val(),
          bsizev: $("#mtp_bsizev").val(),
          bsizeoh: $("#mtp_bsizeoh").val(),
          bsizeov: $("#mtp_bsizeov").val(),
          bbsize: $("#mtp_bbsize").val(),
          ehrsize: $("#mtp_ehrsize").val(),
          ehrtop: $("#mtp_ehrtop").val(),
          ehrbottom: $("#mtp_ehrbottom").val(),
          table_padding: $("#mtp_table_padding").val(),
          inner_padding: $("#mtp_inner_padding").val(),
          text_color: $("#mtp_text_color").val().replace("#", ""),
          main_color: $("#mtp_main_color").val().replace("#", ""),
          bg_color: $("#mtp_bg_color").val().replace("#", ""),
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
          projector_presentation: $("#mtp_projector_presentation").is(":checked") ? "1" : "0",
          si: $("#mtp_si").is(":checked") ? "1" : "0",
          sf: ($("#mtp_sf").length && $("#mtp_sf").is(":visible") && $("#mtp_sf").is(":checked")) ? "1" : "0",
          st: $("#mtp_st").is(":checked") ? "1" : "0",
          sg: ($("#mtp_sg").length && $("#mtp_sg").is(":visible") && $("#mtp_sg").is(":checked")) ? "1" : "0",
          sr: ($("#mtp_sr").length && $("#mtp_sr").is(":visible") && $("#mtp_sr").is(":checked")) ? "1" : "0",
          se: ($("#mtp_se").length && $("#mtp_se").is(":visible") && $("#mtp_se").is(":checked")) ? "1" : "0",
          sp: ($("#mtp_sp").length && $("#mtp_sp").is(":visible") && $("#mtp_sp").is(":checked")) ? "1" : "0",
          sh: $("#mtp_sh").is(":checked") ? "1" : "0",
          language: $("#mtp_language").val(),
          group: $("#mtp_group").val(),
          participant: $("#mtp_participant").val(),
          match_number: $("#mtp_match_number").val(),
          action: "mtp_preview_matches",
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
            $("#mtp-preview").html(response.data);
          }
        });
      }
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
    $combined_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['bg_color'], $meta_values['bg_opacity']);
    $combined_even_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['even_bg_color'], $meta_values['even_bg_opacity']);
    $combined_odd_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['odd_bg_color'], $meta_values['odd_bg_opacity']);
    $combined_hover_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['hover_bg_color'], $meta_values['hover_bg_opacity']);
    $combined_head_bg_color = MTP_Admin_Utilities::combine_color_opacity($meta_values['head_bg_color'], $meta_values['head_bg_opacity']);

    $shortcode = '[mtp-matches id="' . esc_attr($meta_values['tournament_id']) . '" post_id="' . $post_id . '" lang="' . esc_attr($meta_values['language']) . '" s-size="' . esc_attr($meta_values['font_size']) . '" s-sizeheader="' . esc_attr($meta_values['header_font_size']) . '" s-color="' . esc_attr($meta_values['text_color']) . '" s-maincolor="' . esc_attr($meta_values['main_color']) . '" s-padding="' . esc_attr($meta_values['table_padding']) . '" s-innerpadding="' . esc_attr($meta_values['inner_padding']) . '" s-bgcolor="' . esc_attr($combined_bg_color). '" s-bcolor="' . esc_attr($meta_values['border_color']) . '" s-bbcolor="' . esc_attr($meta_values['head_bottom_border_color']) . '" s-bgeven="' . esc_attr($combined_even_bg_color) . '" s-bsizeh="' . esc_attr($meta_values['bsizeh']) . '" s-bsizev="' . esc_attr($meta_values['bsizev']) . '" s-bsizeoh="' . esc_attr($meta_values['bsizeoh']) . '" s-bsizeov="' . esc_attr($meta_values['bsizeov']) . '" s-bbsize="' . esc_attr($meta_values['bbsize']) . '" s-ehrsize="' . esc_attr($meta_values['ehrsize']) . '" s-ehrtop="' . esc_attr($meta_values['ehrtop']) . '" s-ehrbottom="' . esc_attr($meta_values['ehrbottom']). '" s-bgodd="' . esc_attr($combined_odd_bg_color) . '" s-bgover="' . esc_attr($combined_hover_bg_color) . '" s-bghead="' . esc_attr($combined_head_bg_color) . '"';

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
    MTP_Admin_Utilities::render_shortcode_generator($shortcode, $tournament_id, $config);
  }  /**
   * Add tournament table specific shortcode update JavaScript
   */
  private function add_shortcode_update_javascript($meta_values) {
    ?>
    <script>
    jQuery(document).ready(function($) {
      // Helper function to get current iframe dimensions
      function getCurrentIframeDimensions() {
        var dimensions = { width: null, height: null };

        // Check if global dimensions are available from frontend script
        if (window.MTP_IframeDimensions) {
          // Find the most recent dimensions for any iframe
          var latestTimestamp = 0;
          var latestDimensions = null;

          for (var iframeId in window.MTP_IframeDimensions) {
            var dim = window.MTP_IframeDimensions[iframeId];
            if (dim.timestamp > latestTimestamp) {
              latestTimestamp = dim.timestamp;
              latestDimensions = dim;
            }
          }

          if (latestDimensions) {
            dimensions.width = latestDimensions.width;
            dimensions.height = latestDimensions.height;
          }
        }

        // Fallback: check actual iframe dimensions in the preview
        if (!dimensions.width || !dimensions.height) {
          var previewIframe = $("#mtp-preview iframe[id^='mtp-matches-']").first();
          if (previewIframe.length) {
            dimensions.width = previewIframe.attr('width') || previewIframe.width();
            dimensions.height = previewIframe.attr('height') || previewIframe.height();
          }
        }

        return dimensions;
      }

      // Define updateShortcode function globally so shared utilities can call it
      window.updateShortcode = function() {
        var postId = <?php echo intval(get_the_ID()); ?>;
        var tournamentId = $("#mtp_tournament_id").val() || "";

        // Get current iframe dimensions if available, otherwise use defaults
        var currentDimensions = getCurrentIframeDimensions();
        var width = currentDimensions.width || "<?php echo esc_js($meta_values['width']); ?>" || "300";
        var height = currentDimensions.height || "<?php echo esc_js($meta_values['height']); ?>" || "200";

        // Update hidden fields so the values get saved
        $("#mtp_width").val(width);
        $("#mtp_height").val(height);

        var fontSize = $("#mtp_font_size").val() || "9";
        var headerFontSize = $("#mtp_header_font_size").val() || "10";
        var textColor = $("#mtp_text_color").val().replace("#", "") || "000000";
        var mainColor = $("#mtp_main_color").val().replace("#", "") || "173f75";
        var tablePadding = $("#mtp_table_padding").val() || "2";
        var innerPadding = $("#mtp_inner_padding").val() || "5";
        var borderColor = $("#mtp_border_color").val().replace("#", "") || "bbbbbb";
        var headBottomBorderColor = $("#mtp_head_bottom_border_color").val().replace("#", "") || "bbbbbb";
        var bsizeh = $("#mtp_bsizeh").val() || "1";
        var bsizev = $("#mtp_bsizev").val() || "1";
        var bsizeoh = $("#mtp_bsizeoh").val() || "1";
        var bsizeov = $("#mtp_bsizeov").val() || "1";
        var bbsize = $("#mtp_bbsize").val() || "2";
        var ehrsize = $("#mtp_ehrsize").val() || "10";
        var ehrtop = $("#mtp_ehrtop").val() || "9";
        var ehrbottom = $("#mtp_ehrbottom").val() || "3";
        var language = $("#mtp_language").val() || "en";
        var group = $("#mtp_group").val() || "";
        var participant = $("#mtp_participant").val() || "-1";
        var matchNumber = $("#mtp_match_number").val() || "";

        // Combine colors with opacity (convert opacity percentage to hex)
        var bgColor = $("#mtp_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_bg_opacity").val() / 100) * 255));
        var evenBgColor = $("#mtp_even_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_even_bg_opacity").val() / 100) * 255));
        var oddBgColor = $("#mtp_odd_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_odd_bg_opacity").val() / 100) * 255));
        var hoverBgColor = $("#mtp_hover_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_hover_bg_opacity").val() / 100) * 255));
        var headBgColor = $("#mtp_head_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_head_bg_opacity").val() / 100) * 255));

        // Build complete shortcode (width and height removed for auto-sizing)
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
                          ' s-bsizeh="' + bsizeh + '"' +
                          ' s-bsizev="' + bsizev + '"' +
                          ' s-bsizeoh="' + bsizeoh + '"' +
                          ' s-bsizeov="' + bsizeov + '"' +
                          ' s-bbsize="' + bbsize + '"' +
                          ' s-ehrsize="' + ehrsize + '"' +
                          ' s-ehrtop="' + ehrtop + '"' +
                          ' s-ehrbottom="' + ehrbottom + '"' +
                          ' s-bgodd="' + oddBgColor + '"' +
                          ' s-bgover="' + hoverBgColor + '"' +
                          ' s-bghead="' + headBgColor + '"';

        // Add bm parameter if projector_presentation checkbox is checked
        if ($("#mtp_projector_presentation").is(":checked")) {
          newShortcode += ' bm="1"';
        }

        // Add si parameter if si checkbox is checked
        if ($("#mtp_si").is(":checked")) {
          newShortcode += ' si="1"';
        }

        // Add sf parameter if sf checkbox is checked (Suppress Court)
        if ($("#mtp_sf").is(":checked")) {
          newShortcode += ' sf="1"';
        }

        // Add st parameter if st checkbox is checked
        if ($("#mtp_st").is(":checked")) {
          newShortcode += ' st="1"';
        }

        // Add sg parameter if sg checkbox is checked
        if ($("#mtp_sg").is(":checked")) {
          newShortcode += ' sg="1"';
        }

        // Add sr parameter if sr checkbox is checked (Suppress Referee)
        if ($("#mtp_sr").is(":checked")) {
          newShortcode += ' sr="1"';
        }

        // Add se parameter if se checkbox is checked
        if ($("#mtp_se").is(":checked")) {
          newShortcode += ' se="1"';
        }

        // Add sp parameter if sp checkbox is checked
        if ($("#mtp_sp").is(":checked")) {
          newShortcode += ' sp="1"';
        }

        // Add sh parameter if sh checkbox is checked
        if ($("#mtp_sh").is(":checked")) {
          newShortcode += ' sh="1"';
        }

        // Add group parameter if selected
        if (group) {
          newShortcode += ' group="' + group + '"';
        }

        // Add participant parameter if selected and not default "All"
        if (participant && participant !== '-1') {
          newShortcode += ' participant="' + participant + '"';
        }

        // Add gamenumbers parameter if specified
        if (matchNumber) {
          newShortcode += ' gamenumbers="' + matchNumber + '"';
        }

        // Add width and height parameters
        newShortcode += ' width="' + width + '" height="' + height + '"';

        newShortcode += ']';

        $("#mtp_shortcode_field").val(newShortcode);
      };

      // Convert decimal opacity to hex (match PHP behavior)
      function opacityToHex(opacity) {
        var hex = Math.round(opacity).toString(16);
        return hex.length === 1 ? "0" + hex : hex;
      }

      // Call updateShortcode initially to populate the field
      if (typeof window.updateShortcode === 'function') {
        window.updateShortcode();
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
    if (!isset($_POST['mtp_matches_meta_box_nonce']) || !wp_verify_nonce($_POST['mtp_matches_meta_box_nonce'], 'mtp_matches_meta_box')) {
      return;
    }

    // Check if user has permission
    if (isset($_POST['post_type']) && 'mtp_match_list' == $_POST['post_type']) {
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
      $post_field = 'mtp_' . $field;
      $meta_key = '_mtp_' . $field;

      if (in_array($field, array('projector_presentation', 'si', 'sf', 'st', 'sg', 'sr', 'se', 'sp', 'sh'))) {
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
    return MTP_Admin_Utilities::sanitize_meta_value($field, $value);
  }
}
