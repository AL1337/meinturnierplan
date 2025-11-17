<?php
/**
 * Admin Table Meta Boxes Class
 *
 * @package MeinTurnierplan
 * @since 0.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Admin Table Meta Boxes Class
 */
class MTP_Admin_Table_Meta_Boxes {

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
      __('Table Settings & Preview', 'meinturnierplan'),
      array($this, 'meta_box_callback'),
      'mtp_table',
      'normal',
      'high'
    );

    add_meta_box(
      'mtp_table_shortcode',
      __('Shortcode Generator', 'meinturnierplan'),
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
    echo '<h3>' . esc_html__('Table Settings', 'meinturnierplan') . '</h3>';
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
      'language' => MTP_Admin_Utilities::get_default_language(),
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
    MTP_Admin_Utilities::render_group_header(__('Basic Settings', 'meinturnierplan'));
    MTP_Admin_Utilities::render_text_field('mtp_tournament_id', __('Tournament ID', 'meinturnierplan'), $meta_values['tournament_id'], __('Enter the tournament ID from meinturnierplan.de (e.g., 1753883027)', 'meinturnierplan'));
    MTP_Admin_Utilities::render_select_field('mtp_language', __('Language', 'meinturnierplan'), $meta_values['language'], MTP_Admin_Utilities::get_language_options(), __('Select the language for the tournament table display.', 'meinturnierplan'));

    // Note: Width and height are now automatically determined by the iframe content via postMessage

    // Display Options Group
    MTP_Admin_Utilities::render_group_header(__('Display Options', 'meinturnierplan'));
    MTP_Admin_Utilities::render_conditional_group_field($meta_values);
    MTP_Admin_Utilities::render_checkbox_field('mtp_suppress_wins', __('Suppress Num Wins, Losses, etc.', 'meinturnierplan'), $meta_values['suppress_wins'], __('Hide the number of wins, losses, and other statistical columns from the tournament table.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_checkbox_field('mtp_suppress_logos', __('Suppress Logos', 'meinturnierplan'), $meta_values['suppress_logos'], __('Hide the logos from the tournament table.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_checkbox_field('mtp_suppress_num_matches', __('Suppress Num Matches', 'meinturnierplan'), $meta_values['suppress_num_matches'], __('Hide the number of matches from the tournament table.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_checkbox_field('mtp_projector_presentation', __('Projector Presentation', 'meinturnierplan'), $meta_values['projector_presentation'], __('Enable projector presentation mode for the tournament table.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_checkbox_field('mtp_navigation_for_groups', __('Navigation for Groups', 'meinturnierplan'), $meta_values['navigation_for_groups'], __('Enable navigation for groups in the tournament table.', 'meinturnierplan'));

    // Typography Group
    MTP_Admin_Utilities::render_group_header(__('Typography', 'meinturnierplan'));
    MTP_Admin_Utilities::render_number_field('mtp_font_size', __('Content Font Size (pt)', 'meinturnierplan'), $meta_values['font_size'], __('Set the font size of the tournament table content. 9pt is the default value.', 'meinturnierplan'), 6, 24);
    MTP_Admin_Utilities::render_number_field('mtp_header_font_size', __('Header Font Size (pt)', 'meinturnierplan'), $meta_values['header_font_size'], __('Set the font size of the tournament table headers. 10pt is the default value.', 'meinturnierplan'), 6, 24);
    MTP_Admin_Utilities::render_number_field('mtp_logo_size', __('Logo Size (pt)', 'meinturnierplan'), $meta_values['logo_size'], __('Set the font size of the tournament table logo. 20pt is the default value.', 'meinturnierplan'), 6, 24);

    // Spacing & Layout Group
    MTP_Admin_Utilities::render_group_header(__('Spacing & Layout', 'meinturnierplan'));
    MTP_Admin_Utilities::render_number_field('mtp_table_padding', __('Table Padding (px)', 'meinturnierplan'), $meta_values['table_padding'], __('Set the padding around the tournament table. 2px is the default value.', 'meinturnierplan'), 0, 50);
    MTP_Admin_Utilities::render_number_field('mtp_inner_padding', __('Inner Padding (px)', 'meinturnierplan'), $meta_values['inner_padding'], __('Set the padding inside the tournament table cells. 5px is the default value.', 'meinturnierplan'), 0, 20);

    // Border Settings Group
    MTP_Admin_Utilities::render_group_header(__('Border Settings', 'meinturnierplan'));
    MTP_Admin_Utilities::render_number_field('mtp_bsizeh', __('Border Horizontal Size (px)', 'meinturnierplan'), $meta_values['bsizeh'], __('Set the border horizontal size of the tournament table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTP_Admin_Utilities::render_number_field('mtp_bsizev', __('Border Vertical Size (px)', 'meinturnierplan'), $meta_values['bsizev'], __('Set the border vertical size of the tournament table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTP_Admin_Utilities::render_number_field('mtp_bsizeoh', __('Table Top and Bottom Border Size (px)', 'meinturnierplan'), $meta_values['bsizeoh'], __('Set the top and bottom border size of the tournament table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTP_Admin_Utilities::render_number_field('mtp_bsizeov', __('Table Left and Right Border Size (px)', 'meinturnierplan'), $meta_values['bsizeov'], __('Set the left and right border size of the tournament table. 1px is the default value.', 'meinturnierplan'), 1, 10);
    MTP_Admin_Utilities::render_number_field('mtp_bbsize', __('Table Head Border Bottom Size (px)', 'meinturnierplan'), $meta_values['bbsize'], __('Set the head border bottom size of the tournament table. 2px is the default value.', 'meinturnierplan'), 1, 10);

    // Colors Group
    MTP_Admin_Utilities::render_group_header(__('Colors', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_field('mtp_text_color', __('Text Color', 'meinturnierplan'), $meta_values['text_color'], __('Set the color of the tournament table text. Black (#000000) is the default value.', 'meinturnierplan'));
    MTP_Admin_Utilities::render_color_field('mtp_main_color', __('Link & Navigation Color', 'meinturnierplan'), $meta_values['main_color'], __('Set the color for links (e.g., "Show Full Tournament") and navigation arrows between groups. Blue (#173f75) is the default value.', 'meinturnierplan'));
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
    echo '<h3>' . esc_html__('Preview', 'meinturnierplan') . '</h3>';
    echo '<div id="mtp-preview">';

    // Create attributes for preview
    $atts = $this->build_preview_attributes($meta_values);
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is already escaped in the renderer class
    echo $this->table_renderer->render_table_html($post->ID, $atts);

    echo '</div>';
  }

  /**
   * Build preview attributes from meta values
   */
  private function build_preview_attributes($meta_values) {
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
   * Add preview JavaScript
   */
  private function add_preview_javascript($post_id) {
    $field_list = array(
      'mtp_tournament_id', 'mtp_font_size', 'mtp_header_font_size',
      'mtp_bsizeh', 'mtp_bsizev', 'mtp_bsizeoh', 'mtp_bsizeov', 'mtp_bbsize',
      'mtp_table_padding', 'mtp_inner_padding', 'mtp_text_color', 'mtp_main_color',
      'mtp_bg_color', 'mtp_logo_size', 'mtp_bg_opacity', 'mtp_border_color',
      'mtp_head_bottom_border_color', 'mtp_even_bg_color', 'mtp_even_bg_opacity',
      'mtp_odd_bg_color', 'mtp_odd_bg_opacity', 'mtp_hover_bg_color', 'mtp_hover_bg_opacity',
      'mtp_head_bg_color', 'mtp_head_bg_opacity', 'mtp_suppress_wins', 'mtp_suppress_logos', 'mtp_suppress_num_matches', 'mtp_projector_presentation', 'mtp_navigation_for_groups', 'mtp_language', 'mtp_group'
    );

    // Include reusable admin JavaScript utilities
    MTP_Admin_Utilities::render_admin_javascript_utilities();
    ?>
    <script>
    jQuery(document).ready(function($) {
      // Initialize reusable utilities with preview update callback
      MTPAdminUtils.initColorPickers(updatePreview);
      MTPAdminUtils.initOpacitySliders(updatePreview);
      MTPAdminUtils.initFormFieldListeners('mtp_', updatePreview);

      // Initialize tournament ID field with group loading
      MTPAdminUtils.initTournamentIdField('#mtp_tournament_id', updatePreview, function(tournamentId) {
        MTPAdminUtils.loadTournamentGroups(tournamentId);
      });

      // Initialize group refresh button
      MTPAdminUtils.initGroupRefreshButton('#mtp_refresh_groups', '#mtp_tournament_id', function(tournamentId, options) {
        MTPAdminUtils.loadTournamentGroups(tournamentId, options);
      });

      // Load groups on page load if tournament ID exists
      var initialTournamentId = $("#mtp_tournament_id").val();
      if (initialTournamentId) {
        MTPAdminUtils.loadTournamentGroups(initialTournamentId, {preserveSelection: false});
      }

      // Add specific field listeners for all form fields
      $("#<?php echo esc_js(implode(', #', array_map('esc_js', $field_list))); ?>").on("input change", function() {
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
          nonce: "<?php echo esc_js(wp_create_nonce('mtp_preview_nonce')); ?>"
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

    $shortcode = '[mtp-table id="' . esc_attr($meta_values['tournament_id']) . '" post_id="' . $post_id . '" lang="' . esc_attr($meta_values['language']) . '" s-size="' . esc_attr($meta_values['font_size']) . '" s-sizeheader="' . esc_attr($meta_values['header_font_size']) . '" s-color="' . esc_attr($meta_values['text_color']) . '" s-maincolor="' . esc_attr($meta_values['main_color']) . '" s-padding="' . esc_attr($meta_values['table_padding']) . '" s-innerpadding="' . esc_attr($meta_values['inner_padding']) . '" s-bgcolor="' . esc_attr($combined_bg_color). '" s-bcolor="' . esc_attr($meta_values['border_color']) . '" s-bbcolor="' . esc_attr($meta_values['head_bottom_border_color']) . '" s-bgeven="' . esc_attr($combined_even_bg_color) . '" s-logosize="' . esc_attr($meta_values['logo_size']) . '" s-bsizeh="' . esc_attr($meta_values['bsizeh']) . '" s-bsizev="' . esc_attr($meta_values['bsizev']) . '" s-bsizeoh="' . esc_attr($meta_values['bsizeoh']) . '" s-bsizeov="' . esc_attr($meta_values['bsizeov']) . '" s-bbsize="' . esc_attr($meta_values['bbsize']) . '" s-bgodd="' . esc_attr($combined_odd_bg_color) . '" s-bgover="' . esc_attr($combined_hover_bg_color) . '" s-bghead="' . esc_attr($combined_head_bg_color) . '"';

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
          var previewIframe = $("#mtp-preview iframe[id^='mtp-table-']").first();
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

        // Build complete shortcode (width and height removed for auto-sizing)
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
                          ' s-bghead="' + headBgColor + '"';

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
    if (!isset($_POST['mtp_table_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mtp_table_meta_box_nonce'])), 'mtp_table_meta_box')) {
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
        $value = $this->sanitize_meta_value($field, wp_unslash($_POST[$post_field]));
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
