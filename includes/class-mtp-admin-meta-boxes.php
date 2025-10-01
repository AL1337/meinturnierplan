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
    
    // Tournament ID
    $this->render_text_field('tournament_id', __('Tournament ID', 'meinturnierplan-wp'), $meta_values['tournament_id'], __('Enter the tournament ID from meinturnierplan.de (e.g., 1753883027)', 'meinturnierplan-wp'));
    
    // Dimensions
    $this->render_number_field('width', __('Table Width (px)', 'meinturnierplan-wp'), $meta_values['width'], __('Set the width of the tournament table in pixels.', 'meinturnierplan-wp'), 100, 2000);
    $this->render_number_field('height', __('Table Height (px)', 'meinturnierplan-wp'), $meta_values['height'], __('Set the height of the tournament table in pixels.', 'meinturnierplan-wp'), 100, 2000);
    
    // Font sizes
    $this->render_number_field('font_size', __('Content Font Size (pt)', 'meinturnierplan-wp'), $meta_values['font_size'], __('Set the font size of the tournament table content. 9pt is the default value.', 'meinturnierplan-wp'), 6, 24);
    $this->render_number_field('header_font_size', __('Header Font Size (pt)', 'meinturnierplan-wp'), $meta_values['header_font_size'], __('Set the font size of the tournament table headers. 10pt is the default value.', 'meinturnierplan-wp'), 6, 24);
    
    // Border sizes
    $this->render_number_field('bsizeh', __('Border Vertical Size (px)', 'meinturnierplan-wp'), $meta_values['bsizeh'], __('Set the border vertical size of the tournament table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bsizev', __('Border Horizontal Size (px)', 'meinturnierplan-wp'), $meta_values['bsizev'], __('Set the border horizontal size of the tournament table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bsizeoh', __('Table Block Border Size (px)', 'meinturnierplan-wp'), $meta_values['bsizeoh'], __('Set the block border size of the tournament table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bsizeov', __('Table Inline Border Size (px)', 'meinturnierplan-wp'), $meta_values['bsizeov'], __('Set the inline border size of the tournament table. 1px is the default value.', 'meinturnierplan-wp'), 1, 10);
    $this->render_number_field('bbsize', __('Table Head Border Bottom Size (px)', 'meinturnierplan-wp'), $meta_values['bbsize'], __('Set the head border bottom size of the tournament table. 2px is the default value.', 'meinturnierplan-wp'), 1, 10);
    
    // Padding
    $this->render_number_field('table_padding', __('Table Padding (px)', 'meinturnierplan-wp'), $meta_values['table_padding'], __('Set the padding around the tournament table. 2px is the default value.', 'meinturnierplan-wp'), 0, 50);
    $this->render_number_field('inner_padding', __('Inner Padding (px)', 'meinturnierplan-wp'), $meta_values['inner_padding'], __('Set the padding inside the tournament table cells. 5px is the default value.', 'meinturnierplan-wp'), 0, 20);
    
    // Colors
    $this->render_color_field('text_color', __('Text Color', 'meinturnierplan-wp'), $meta_values['text_color'], __('Set the color of the tournament table text. Black (#000000) is the default value.', 'meinturnierplan-wp'));
    $this->render_color_field('main_color', __('Main Color', 'meinturnierplan-wp'), $meta_values['main_color'], __('Set the main color of the tournament table (headers, highlights). Blue (#173f75) is the default value.', 'meinturnierplan-wp'));
    
    // Background colors with opacity
    $this->render_color_opacity_field('bg_color', 'bg_opacity', __('Background Color', 'meinturnierplan-wp'), $meta_values['bg_color'], $meta_values['bg_opacity'], __('Set the background color and opacity of the tournament table. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    
    // Logo size
    $this->render_number_field('logo_size', __('Logo Size (pt)', 'meinturnierplan-wp'), $meta_values['logo_size'], __('Set the font size of the tournament table logo. 20pt is the default value.', 'meinturnierplan-wp'), 6, 24);
    
    // Border colors
    $this->render_color_field('border_color', __('Border Color', 'meinturnierplan-wp'), $meta_values['border_color'], __('Set the border color of the tournament table. Light gray (#bbbbbb) is the default value.', 'meinturnierplan-wp'));
    $this->render_color_field('head_bottom_border_color', __('Table Head Bottom Border Color', 'meinturnierplan-wp'), $meta_values['head_bottom_border_color'], __('Set the bottom border color of the table header. Light gray (#bbbbbb) is the default value.', 'meinturnierplan-wp'));
    
    // Row background colors with opacity
    $this->render_color_opacity_field('even_bg_color', 'even_bg_opacity', __('Even Rows Background Color', 'meinturnierplan-wp'), $meta_values['even_bg_color'], $meta_values['even_bg_opacity'], __('Set the background color and opacity for even-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    $this->render_color_opacity_field('odd_bg_color', 'odd_bg_opacity', __('Odd Rows Background Color', 'meinturnierplan-wp'), $meta_values['odd_bg_color'], $meta_values['odd_bg_opacity'], __('Set the background color and opacity for odd-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    $this->render_color_opacity_field('hover_bg_color', 'hover_bg_opacity', __('Row Hover Background Color', 'meinturnierplan-wp'), $meta_values['hover_bg_color'], $meta_values['hover_bg_opacity'], __('Set the background color and opacity for table rows hover. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    $this->render_color_opacity_field('head_bg_color', 'head_bg_opacity', __('Head Background Color', 'meinturnierplan-wp'), $meta_values['head_bg_color'], $meta_values['head_bg_opacity'], __('Set the background color and opacity for table head. Use opacity 0% for transparent background.', 'meinturnierplan-wp'));
    
    echo '</table>';
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
    
    return array(
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
      's-bbsize' => $meta_values['bbsize']
    );
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
      'mtp_head_bg_color', 'mtp_head_bg_opacity'
    );
    ?>
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
      
      $("#<?php echo implode(', #', $field_list); ?>").on("input", function() {
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
    
    return '[mtp-table id="' . esc_attr($meta_values['tournament_id']) . '" post_id="' . $post_id . '" lang="en" s-size="' . esc_attr($meta_values['font_size']) . '" s-sizeheader="' . esc_attr($meta_values['header_font_size']) . '" s-color="' . esc_attr($meta_values['text_color']) . '" s-maincolor="' . esc_attr($meta_values['main_color']) . '" s-padding="' . esc_attr($meta_values['table_padding']) . '" s-innerpadding="' . esc_attr($meta_values['inner_padding']) . '" s-bgcolor="' . esc_attr($combined_bg_color). '" s-bcolor="' . esc_attr($meta_values['border_color']) . '" s-bbcolor="' . esc_attr($meta_values['head_bottom_border_color']) . '" s-bgeven="' . esc_attr($combined_even_bg_color) . '" s-logosize="' . esc_attr($meta_values['logo_size']) . '" s-bsizeh="' . esc_attr($meta_values['bsizeh']) . '" s-bsizev="' . esc_attr($meta_values['bsizev']) . '" s-bsizeoh="' . esc_attr($meta_values['bsizeoh']) . '" s-bsizeov="' . esc_attr($meta_values['bsizeov']) . '" s-bbsize="' . esc_attr($meta_values['bbsize']) . '" s-bgodd="' . esc_attr($combined_odd_bg_color) . '" s-bgover="' . esc_attr($combined_hover_bg_color) . '" s-bghead="' . esc_attr($combined_head_bg_color) . '" width="' . esc_attr($meta_values['width']) . '" height="' . esc_attr($meta_values['height']) . '"]';
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
      $("input[id^='mtp_'], .mtp-color-picker").on("input change", function() {
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
        
        // Combine colors with opacity (convert opacity percentage to hex)
        var bgColor = $("#mtp_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_bg_opacity").val() / 100) * 255));
        var evenBgColor = $("#mtp_even_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_even_bg_opacity").val() / 100) * 255));
        var oddBgColor = $("#mtp_odd_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_odd_bg_opacity").val() / 100) * 255));
        var hoverBgColor = $("#mtp_hover_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_hover_bg_opacity").val() / 100) * 255));
        var headBgColor = $("#mtp_head_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_head_bg_opacity").val() / 100) * 255));
        
        // Build complete shortcode
        var newShortcode = '[mtp-table id="' + tournamentId + '" post_id="' + postId + '" lang="en"' +
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
                          ' height="' + height + '"]';
        
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
      'head_bg_color', 'head_bg_opacity', 'logo_size'
    );
    
    foreach ($meta_fields as $field) {
      $post_field = 'mtp_' . $field;
      $meta_key = '_mtp_' . $field;
      
      if (isset($_POST[$post_field])) {
        $value = $this->sanitize_meta_value($field, $_POST[$post_field]);
        update_post_meta($post_id, $meta_key, $value);
      }
    }
  }
  
  /**
   * Sanitize meta value based on field type
   */
  private function sanitize_meta_value($field, $value) {
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
    
    // Text fields
    return sanitize_text_field($value);
  }
}
