<?php
/**
 * Plugin Name: MeinTurnierplan
 * Plugin URI: https://github.com/danfisher85/meinturnierplan-wp
 * Description: A WordPress plugin to display tournament tables using custom post types, shortcodes, and widgets.
 * Version: 1.0.0
 * Author: Dan Fisher
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: meinturnierplan-wp
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

// Define plugin constants
define('MTP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MTP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MTP_PLUGIN_VERSION', '1.0.0');

/**
 * Main Plugin Class
 */
class MeinTurnierplanWP {
  
  /**
   * Constructor
   */
  public function __construct() {
    add_action('init', array($this, 'init'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    
    // Activation and deactivation hooks
    register_activation_hook(__FILE__, array($this, 'activate'));
    register_deactivation_hook(__FILE__, array($this, 'deactivate'));
  }
  
  /**
   * Initialize the plugin
   */
  public function init() {
    // Load text domain
    load_plugin_textdomain('meinturnierplan-wp', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Register custom post type
    $this->register_post_type();
    
    // Initialize components
    $this->init_shortcode();
    $this->init_widget();
    
    // Add meta boxes
    add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
    add_action('save_post', array($this, 'save_meta_boxes'));
    
    // Add AJAX handlers
    add_action('wp_ajax_mtp_preview_table', array($this, 'ajax_preview_table'));
  }
  
  /**
   * Register the custom post type
   */
  public function register_post_type() {
    $labels = array(
      'name'                  => _x('Tournament Tables', 'Post type general name', 'meinturnierplan-wp'),
      'singular_name'         => _x('Tournament Table', 'Post type singular name', 'meinturnierplan-wp'),
      'menu_name'             => _x('Tournament Tables', 'Admin Menu text', 'meinturnierplan-wp'),
      'name_admin_bar'        => _x('Tournament Table', 'Add New on Toolbar', 'meinturnierplan-wp'),
      'add_new'               => __('Add New', 'meinturnierplan-wp'),
      'add_new_item'          => __('Add New Tournament Table', 'meinturnierplan-wp'),
      'new_item'              => __('New Tournament Table', 'meinturnierplan-wp'),
      'edit_item'             => __('Edit Tournament Table', 'meinturnierplan-wp'),
      'view_item'             => __('View Tournament Table', 'meinturnierplan-wp'),
      'all_items'             => __('All Tournament Tables', 'meinturnierplan-wp'),
      'search_items'          => __('Search Tournament Tables', 'meinturnierplan-wp'),
      'parent_item_colon'     => __('Parent Tournament Tables:', 'meinturnierplan-wp'),
      'not_found'             => __('No tournament tables found.', 'meinturnierplan-wp'),
      'not_found_in_trash'    => __('No tournament tables found in Trash.', 'meinturnierplan-wp'),
      'featured_image'        => _x('Tournament Table Image', 'Overrides the "Featured Image" phrase', 'meinturnierplan-wp'),
      'set_featured_image'    => _x('Set tournament table image', 'Overrides the "Set featured image" phrase', 'meinturnierplan-wp'),
      'remove_featured_image' => _x('Remove tournament table image', 'Overrides the "Remove featured image" phrase', 'meinturnierplan-wp'),
      'use_featured_image'    => _x('Use as tournament table image', 'Overrides the "Use as featured image" phrase', 'meinturnierplan-wp'),
      'archives'              => _x('Tournament Table archives', 'The post type archive label', 'meinturnierplan-wp'),
      'insert_into_item'      => _x('Insert into tournament table', 'Overrides the "Insert into post"/"Insert into page" phrase', 'meinturnierplan-wp'),
      'uploaded_to_this_item' => _x('Uploaded to this tournament table', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'meinturnierplan-wp'),
      'filter_items_list'     => _x('Filter tournament tables list', 'Screen reader text for the filter links', 'meinturnierplan-wp'),
      'items_list_navigation' => _x('Tournament tables list navigation', 'Screen reader text for the pagination', 'meinturnierplan-wp'),
      'items_list'            => _x('Tournament tables list', 'Screen reader text for the items list', 'meinturnierplan-wp'),
    );
    
    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array('slug' => 'tournament-table'),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-editor-table',
      'show_in_rest'       => false, // Disable Gutenberg editor
      'supports'           => array('title', 'thumbnail')
    );
    
    register_post_type('mtp_table', $args);
  }
  
  /**
   * Add meta boxes
   */
  public function add_meta_boxes() {
    add_meta_box(
      'mtp_table_settings',
      __('Table Settings', 'meinturnierplan-wp'),
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
    
    // Get current values
    $tournament_id = get_post_meta($post->ID, '_mtp_tournament_id', true);
    $width = get_post_meta($post->ID, '_mtp_table_width', true);
    if (empty($width)) {
      $width = '300'; // Default width
    }
    $font_size = get_post_meta($post->ID, '_mtp_font_size', true);
    if (empty($font_size)) {
      $font_size = '9'; // Default font size
    }
    $header_font_size = get_post_meta($post->ID, '_mtp_header_font_size', true);
    if (empty($header_font_size)) {
      $header_font_size = '10'; // Default header font size
    }
    $table_padding = get_post_meta($post->ID, '_mtp_table_padding', true);
    if (empty($table_padding)) {
      $table_padding = '2'; // Default table padding
    }
    $inner_padding = get_post_meta($post->ID, '_mtp_inner_padding', true);
    if (empty($inner_padding)) {
      $inner_padding = '5'; // Default inner padding (cell padding)
    }
    $text_color = get_post_meta($post->ID, '_mtp_text_color', true);
    if (empty($text_color)) {
      $text_color = '000000'; // Default text color (black)
    }
    $main_color = get_post_meta($post->ID, '_mtp_main_color', true);
    if (empty($main_color)) {
      $main_color = '173f75'; // Default main color (blue)
    }
    $bg_color = get_post_meta($post->ID, '_mtp_bg_color', true);
    if (empty($bg_color)) {
      $bg_color = '000000'; // Default background color
    }
    $bg_opacity = get_post_meta($post->ID, '_mtp_bg_opacity', true);
    if (empty($bg_opacity) && $bg_opacity !== '0') {
      $bg_opacity = '0'; // Default opacity (fully transparent)
    }
    $border_color = get_post_meta($post->ID, '_mtp_border_color', true);
    if (empty($border_color)) {
      $border_color = 'bbbbbb'; // Default border color (light gray)
    }
    $head_bottom_border_color = get_post_meta($post->ID, '_mtp_head_bottom_border_color', true);
    if (empty($head_bottom_border_color)) {
      $head_bottom_border_color = 'bbbbbb'; // Default head bottom border color
    }
    $even_bg_color = get_post_meta($post->ID, '_mtp_even_bg_color', true);
    if (empty($even_bg_color)) {
      $even_bg_color = 'f0f8ff'; // Default even rows background color
    }
    $even_bg_opacity = get_post_meta($post->ID, '_mtp_even_bg_opacity', true);
    if (empty($even_bg_opacity) && $even_bg_opacity !== '0') {
      $even_bg_opacity = '69'; // Default opacity (69%)
    }
    $odd_bg_color = get_post_meta($post->ID, '_mtp_odd_bg_color', true);
    if (empty($odd_bg_color)) {
      $odd_bg_color = 'ffffff'; // Default odd rows background color
    }
    $odd_bg_opacity = get_post_meta($post->ID, '_mtp_odd_bg_opacity', true);
    if (empty($odd_bg_opacity) && $odd_bg_opacity !== '0') {
      $odd_bg_opacity = '69'; // Default opacity (69%)
    }
    $hover_bg_color = get_post_meta($post->ID, '_mtp_hover_bg_color', true);
    if (empty($hover_bg_color)) {
      $hover_bg_color = 'eeeeff'; // Default rows hover background color
    }
    $hover_bg_opacity = get_post_meta($post->ID, '_mtp_hover_bg_opacity', true);
    if (empty($hover_bg_opacity) && $hover_bg_opacity !== '0') {
      $hover_bg_opacity = '69'; // Default opacity (69%)
    }
    $head_bg_color = get_post_meta($post->ID, '_mtp_head_bg_color', true);
    if (empty($head_bg_color)) {
      $head_bg_color = 'eeeeff'; // Default head background color
    }
    $head_bg_opacity = get_post_meta($post->ID, '_mtp_head_bg_opacity', true);
    if (empty($head_bg_opacity) && $head_bg_opacity !== '0') {
      $head_bg_opacity = '100'; // Default opacity (100%)
    }
    $logo_size = get_post_meta($post->ID, '_mtp_logo_size', true);
    if (empty($logo_size)) {
      $logo_size = '20'; // Default logo size
    }
    $bsizeh = get_post_meta($post->ID, '_mtp_bsizeh', true);
    if (empty($bsizeh)) {
      $bsizeh = '1'; // Default border vertical size
    }
    $bsizev = get_post_meta($post->ID, '_mtp_bsizev', true);
    if (empty($bsizev)) {
      $bsizev = '1'; // Default border horizontal size
    }
    
    // Output the form
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_tournament_id">' . __('Tournament ID', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="mtp_tournament_id" name="mtp_tournament_id" value="' . esc_attr($tournament_id) . '" class="regular-text" />';
    echo '<p class="description">' . __('Enter the tournament ID from meinturnierplan.de (e.g., 1753883027)', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_table_width">' . __('Table Width (px)', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_table_width" name="mtp_table_width" value="' . esc_attr($width) . '" min="100" max="2000" step="1" />';
    echo '<p class="description">' . __('Set the width of the tournament table in pixels.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_font_size">' . __('Content Font Size (pt)', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_font_size" name="mtp_font_size" value="' . esc_attr($font_size) . '" min="6" max="24" step="1" />';
    echo '<p class="description">' . __('Set the font size of the tournament table content. 9pt is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_bsizeh">' . __('Border Vertical Size (px)', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_bsizeh" name="mtp_bsizeh" value="' . esc_attr($bsizeh) . '" min="1" max="10" step="1" />';
    echo '<p class="description">' . __('Set the border vertical size of the tournament table. 1px is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_bsizev">' . __('Border Horizontal Size (px)', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_bsizev" name="mtp_bsizev" value="' . esc_attr($bsizev) . '" min="1" max="10" step="1" />';
    echo '<p class="description">' . __('Set the border horizontal size of the tournament table. 1px is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_header_font_size">' . __('Header Font Size (pt)', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_header_font_size" name="mtp_header_font_size" value="' . esc_attr($header_font_size) . '" min="6" max="24" step="1" />';
    echo '<p class="description">' . __('Set the font size of the tournament table headers. 10pt is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_table_padding">' . __('Table Padding (px)', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_table_padding" name="mtp_table_padding" value="' . esc_attr($table_padding) . '" min="0" max="50" step="1" />';
    echo '<p class="description">' . __('Set the padding around the tournament table. 2px is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_inner_padding">' . __('Inner Padding (px)', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_inner_padding" name="mtp_inner_padding" value="' . esc_attr($inner_padding) . '" min="0" max="20" step="1" />';
    echo '<p class="description">' . __('Set the padding inside the tournament table cells. 5px is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_text_color">' . __('Text Color', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="mtp_text_color" name="mtp_text_color" value="#' . esc_attr($text_color) . '" class="mtp-color-picker" />';
    echo '<p class="description">' . __('Set the color of the tournament table text. Black (#000000) is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_main_color">' . __('Main Color', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="mtp_main_color" name="mtp_main_color" value="#' . esc_attr($main_color) . '" class="mtp-color-picker" />';
    echo '<p class="description">' . __('Set the main color of the tournament table (headers, highlights). Blue (#173f75) is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_bg_color">' . __('Background Color', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 15px;">';
    echo '<input type="text" id="mtp_bg_color" name="mtp_bg_color" value="#' . esc_attr($bg_color) . '" class="mtp-color-picker" style="width: 120px;" />';
    echo '<div style="display: flex; align-items: center; gap: 8px;">';
    echo '<label for="mtp_bg_opacity" style="margin: 0; font-weight: normal;">' . __('Opacity:', 'meinturnierplan-wp') . '</label>';
    echo '<input type="range" id="mtp_bg_opacity" name="mtp_bg_opacity" value="' . esc_attr($bg_opacity) . '" min="0" max="100" step="1" style="width: 100px;" />';
    echo '<span id="mtp_bg_opacity_value" style="min-width: 35px; font-size: 12px; color: #666;">' . esc_attr($bg_opacity) . '%</span>';
    echo '</div>';
    echo '</div>';
    echo '<p class="description">' . __('Set the background color and opacity of the tournament table. Use opacity 0% for transparent background.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_logo_size">' . __('Logo Size (pt)', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="number" id="mtp_logo_size" name="mtp_logo_size" value="' . esc_attr($logo_size) . '" min="6" max="24" step="1" />';
    echo '<p class="description">' . __('Set the font size of the tournament table logo. 20pt is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_border_color">' . __('Border Color', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="mtp_border_color" name="mtp_border_color" value="#' . esc_attr($border_color) . '" class="mtp-color-picker" />';
    echo '<p class="description">' . __('Set the border color of the tournament table. Light gray (#bbbbbb) is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_head_bottom_border_color">' . __('Table Head Bottom Border Color', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="mtp_head_bottom_border_color" name="mtp_head_bottom_border_color" value="#' . esc_attr($head_bottom_border_color) . '" class="mtp-color-picker" />';
    echo '<p class="description">' . __('Set the bottom border color of the table header. Light gray (#bbbbbb) is the default value.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_even_bg_color">' . __('Even Rows Background Color', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 15px;">';
    echo '<input type="text" id="mtp_even_bg_color" name="mtp_even_bg_color" value="#' . esc_attr($even_bg_color) . '" class="mtp-color-picker" style="width: 120px;" />';
    echo '<div style="display: flex; align-items: center; gap: 8px;">';
    echo '<label for="mtp_even_bg_opacity" style="margin: 0; font-weight: normal;">' . __('Opacity:', 'meinturnierplan-wp') . '</label>';
    echo '<input type="range" id="mtp_even_bg_opacity" name="mtp_even_bg_opacity" value="' . esc_attr($even_bg_opacity) . '" min="0" max="100" step="1" style="width: 100px;" />';
    echo '<span id="mtp_even_bg_opacity_value" style="min-width: 35px; font-size: 12px; color: #666;">' . esc_attr($even_bg_opacity) . '%</span>';
    echo '</div>';
    echo '</div>';
    echo '<p class="description">' . __('Set the background color and opacity for even-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_odd_bg_color">' . __('Odd Rows Background Color', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 15px;">';
    echo '<input type="text" id="mtp_odd_bg_color" name="mtp_odd_bg_color" value="#' . esc_attr($odd_bg_color) . '" class="mtp-color-picker" style="width: 120px;" />';
    echo '<div style="display: flex; align-items: center; gap: 8px;">';
    echo '<label for="mtp_odd_bg_opacity" style="margin: 0; font-weight: normal;">' . __('Opacity:', 'meinturnierplan-wp') . '</label>';
    echo '<input type="range" id="mtp_odd_bg_opacity" name="mtp_odd_bg_opacity" value="' . esc_attr($odd_bg_opacity) . '" min="0" max="100" step="1" style="width: 100px;" />';
    echo '<span id="mtp_odd_bg_opacity_value" style="min-width: 35px; font-size: 12px; color: #666;">' . esc_attr($odd_bg_opacity) . '%</span>';
    echo '</div>';
    echo '</div>';
    echo '<p class="description">' . __('Set the background color and opacity for odd-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_hover_bg_color">' . __('Row Hover Background Color', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 15px;">';
    echo '<input type="text" id="mtp_hover_bg_color" name="mtp_hover_bg_color" value="#' . esc_attr($hover_bg_color) . '" class="mtp-color-picker" style="width: 120px;" />';
    echo '<div style="display: flex; align-items: center; gap: 8px;">';
    echo '<label for="mtp_hover_bg_opacity" style="margin: 0; font-weight: normal;">' . __('Opacity:', 'meinturnierplan-wp') . '</label>';
    echo '<input type="range" id="mtp_hover_bg_opacity" name="mtp_hover_bg_opacity" value="' . esc_attr($hover_bg_opacity) . '" min="0" max="100" step="1" style="width: 100px;" />';
    echo '<span id="mtp_hover_bg_opacity_value" style="min-width: 35px; font-size: 12px; color: #666;">' . esc_attr($hover_bg_opacity) . '%</span>';
    echo '</div>';
    echo '</div>';
    echo '<p class="description">' . __('Set the background color and opacity for table rows hover. Use opacity 0% for transparent background.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row"><label for="mtp_head_bg_color">' . __('Head Background Color', 'meinturnierplan-wp') . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 15px;">';
    echo '<input type="text" id="mtp_head_bg_color" name="mtp_head_bg_color" value="#' . esc_attr($head_bg_color) . '" class="mtp-color-picker" style="width: 120px;" />';
    echo '<div style="display: flex; align-items: center; gap: 8px;">';
    echo '<label for="mtp_head_bg_opacity" style="margin: 0; font-weight: normal;">' . __('Opacity:', 'meinturnierplan-wp') . '</label>';
    echo '<input type="range" id="mtp_head_bg_opacity" name="mtp_head_bg_opacity" value="' . esc_attr($head_bg_opacity) . '" min="0" max="100" step="1" style="width: 100px;" />';
    echo '<span id="mtp_head_bg_opacity_value" style="min-width: 35px; font-size: 12px; color: #666;">' . esc_attr($head_bg_opacity) . '%</span>';
    echo '</div>';
    echo '</div>';
    echo '<p class="description">' . __('Set the background color and opacity for table head. Use opacity 0% for transparent background.', 'meinturnierplan-wp') . '</p>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    
    // Preview section
    echo '<h3>' . __('Preview', 'meinturnierplan-wp') . '</h3>';
    echo '<div id="mtp-table-preview" style="border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">';
    // Always show a table - either with data (if ID provided) or empty (if no ID)

    // Combine background color and opacity for preview
    $combined_bg_color = $bg_color;
    if ($bg_opacity !== '' && $bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_bg_color = $bg_color . $opacity_hex;
    }
    // Combine even rows background color and opacity for preview
    $combined_even_bg_color = $even_bg_color;
    if ($even_bg_opacity !== '' && $even_bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($even_bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_even_bg_color = $even_bg_color . $opacity_hex;
    }
    // Combine odd rows background color and opacity for preview
    $combined_odd_bg_color = $odd_bg_color;
    if ($odd_bg_opacity !== '' && $odd_bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($odd_bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_odd_bg_color = $odd_bg_color . $opacity_hex;
    }
    // Combine hover rows background color and opacity for preview
    $combined_hover_bg_color = $hover_bg_color;
    if ($hover_bg_opacity !== '' && $hover_bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($hover_bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_hover_bg_color = $hover_bg_color . $opacity_hex;
    }
    // Combine head background color and opacity for preview
    $combined_head_bg_color = $head_bg_color;
    if ($head_bg_opacity !== '' && $head_bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($head_bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_head_bg_color = $head_bg_color . $opacity_hex;
    }

    echo $this->render_table_html($post->ID, array('id' => $tournament_id, 'width' => $width, 's-size' => $font_size, 's-sizeheader' => $header_font_size, 's-padding' => $table_padding, 's-innerpadding' => $inner_padding, 's-color' => $text_color, 's-maincolor' => $main_color, 's-bgcolor' => $combined_bg_color, 's-logosize' => $logo_size, 's-bcolor' => $border_color, 's-bbcolor' => $head_bottom_border_color, 's-bgeven' => $combined_even_bg_color, 's-bgodd' => $combined_odd_bg_color, 's-bgover' => $combined_hover_bg_color, 's-bghead' => $combined_head_bg_color, 's-bsizeh' => $bsizeh, 's-bsizev' => $bsizev));
    echo '</div>';
    
    // Add JavaScript for live preview
    echo '<script>
    jQuery(document).ready(function($) {
      // Initialize color picker
      $(".mtp-color-picker").wpColorPicker({
        change: function(event, ui) {
          // Trigger preview update when color changes
          $("#mtp_tournament_id").trigger("input");
        },
        clear: function() {
          // Trigger preview update when color is cleared
          $("#mtp_tournament_id").trigger("input");
        }
      });
      
      // Handle opacity slider for background color
      $("#mtp_bg_opacity").on("input", function() {
        var opacity = $(this).val();
        $("#mtp_bg_opacity_value").text(opacity + "%");
        $("#mtp_tournament_id").trigger("input");
      });
      
      // Handle opacity slider for even rows background color
      $("#mtp_even_bg_opacity").on("input", function() {
        var opacity = $(this).val();
        $("#mtp_even_bg_opacity_value").text(opacity + "%");
        $("#mtp_tournament_id").trigger("input");
      });

      // Handle opacity slider for odd rows background color
      $("#mtp_odd_bg_opacity").on("input", function() {
        var opacity = $(this).val();
        $("#mtp_odd_bg_opacity_value").text(opacity + "%");
        $("#mtp_tournament_id").trigger("input");
      });

      // Handle opacity slider for hover rows background color
      $("#mtp_hover_bg_opacity").on("input", function() {
        var opacity = $(this).val();
        $("#mtp_hover_bg_opacity_value").text(opacity + "%");
        $("#mtp_tournament_id").trigger("input");
      });

      // Handle opacity slider for head background color
      $("#mtp_head_bg_opacity").on("input", function() {
        var opacity = $(this).val();
        $("#mtp_head_bg_opacity_value").text(opacity + "%");
        $("#mtp_tournament_id").trigger("input");
      });
      
      $("#mtp_tournament_id, #mtp_table_width, #mtp_font_size, #mtp_header_font_size, #mtp_bsizeh, #mtp_bsizev, #mtp_table_padding, #mtp_inner_padding, #mtp_text_color, #mtp_main_color, #mtp_bg_color, #mtp_logo_size, #mtp_bg_opacity, #mtp_border_color, #mtp_head_bottom_border_color, #mtp_even_bg_color, #mtp_even_bg_opacity, #mtp_odd_bg_color, #mtp_odd_bg_opacity, #mtp_head_bg_color, #mtp_head_bg_opacity").on("input", function() {
        var tournamentId = $("#mtp_tournament_id").val();
        var width = $("#mtp_table_width").val();
        var fontSize = $("#mtp_font_size").val();
        var headerFontSize = $("#mtp_header_font_size").val();
        var bsizeh = $("#mtp_bsizeh").val();
        var bsizev = $("#mtp_bsizev").val();
        var tablePadding = $("#mtp_table_padding").val();
        var innerPadding = $("#mtp_inner_padding").val();
        var textColor = $("#mtp_text_color").val().replace("#", "");
        var mainColor = $("#mtp_main_color").val().replace("#", "");
        var bgColor = $("#mtp_bg_color").val().replace("#", "");
        var logoSize = $("#mtp_logo_size").val();
        var bgOpacity = $("#mtp_bg_opacity").val();
        var borderColor = $("#mtp_border_color").val().replace("#", "");
        var headBottomBorderColor = $("#mtp_head_bottom_border_color").val().replace("#", "");
        var evenBgColor = $("#mtp_even_bg_color").val().replace("#", "");
        var evenBgOpacity = $("#mtp_even_bg_opacity").val();
        var oddBgColor = $("#mtp_odd_bg_color").val().replace("#", "");
        var oddBgOpacity = $("#mtp_odd_bg_opacity").val();
        var hoverBgColor = $("#mtp_hover_bg_color").val().replace("#", "");
        var hoverBgOpacity = $("#mtp_hover_bg_opacity").val();
        var headBgColor = $("#mtp_head_bg_color").val().replace("#", "");
        var headBgOpacity = $("#mtp_head_bg_opacity").val();

        // Convert opacity percentage to hex (0-100% to 00-FF)
        var opacityHex = Math.round((bgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var bgColorWithOpacity = bgColor + opacityHex;
        
        // Convert even rows opacity percentage to hex
        var evenOpacityHex = Math.round((evenBgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var evenBgColorWithOpacity = evenBgColor + evenOpacityHex;

        // Convert odd rows opacity percentage to hex
        var oddOpacityHex = Math.round((oddBgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var oddBgColorWithOpacity = oddBgColor + oddOpacityHex;

        // Convert hover rows opacity percentage to hex
        var hoverOpacityHex = Math.round((hoverBgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var hoverBgColorWithOpacity = hoverBgColor + hoverOpacityHex;

        // Convert head rows opacity percentage to hex
        var headOpacityHex = Math.round((headBgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var headBgColorWithOpacity = headBgColor + headOpacityHex;

        var preview = $("#mtp-table-preview");
        
        // Always update preview - either with data or empty table
        var postId = ' . intval($post->ID) . ';
        $.post(ajaxurl, {
          action: "mtp_preview_table",
          post_id: postId,
          tournament_id: tournamentId,
          width: width,
          font_size: fontSize,
          header_font_size: headerFontSize,
          bsizeh: bsizeh,
          bsizev: bsizev,
          table_padding: tablePadding,
          inner_padding: innerPadding,
          text_color: textColor,
          main_color: mainColor,
          bg_color: bgColorWithOpacity,
          logo_size: logoSize,
          border_color: borderColor,
          head_bottom_border_color: headBottomBorderColor,
          even_bg_color: evenBgColorWithOpacity,
          odd_bg_color: oddBgColorWithOpacity,
          hover_bg_color: hoverBgColorWithOpacity,
          head_bg_color: headBgColorWithOpacity,
          nonce: "' . wp_create_nonce('mtp_preview_nonce') . '"
        }, function(response) {
          if (response.success) {
            preview.html(response.data);
          }
        });
      });
    });
    </script>';
  }
  
  /**
   * Shortcode meta box callback
   */
  public function shortcode_meta_box_callback($post) {
    // Get current values
    $tournament_id = get_post_meta($post->ID, '_mtp_tournament_id', true);
    $width = get_post_meta($post->ID, '_mtp_table_width', true);
    if (empty($width)) {
      $width = '300'; // Default width
    }
    $font_size = get_post_meta($post->ID, '_mtp_font_size', true);
    if (empty($font_size)) {
      $font_size = '9'; // Default font size
    }
    $header_font_size = get_post_meta($post->ID, '_mtp_header_font_size', true);
    if (empty($header_font_size)) {
      $header_font_size = '10'; // Default header font size
    }
    $bsizeh = get_post_meta($post->ID, '_mtp_bsizeh', true);
    if (empty($bsizeh)) {
      $bsizeh = '1'; // Default border vertical size
    }
    $bsizev = get_post_meta($post->ID, '_mtp_bsizev', true);
    if (empty($bsizev)) {
      $bsizev = '1'; // Default border horizontal size
    }
    $table_padding = get_post_meta($post->ID, '_mtp_table_padding', true);
    if (empty($table_padding)) {
      $table_padding = '2'; // Default table padding
    }
    $inner_padding = get_post_meta($post->ID, '_mtp_inner_padding', true);
    if (empty($inner_padding)) {
      $inner_padding = '5'; // Default inner padding
    }
    $text_color = get_post_meta($post->ID, '_mtp_text_color', true);
    if (empty($text_color)) {
      $text_color = '000000'; // Default text color
    }
    $main_color = get_post_meta($post->ID, '_mtp_main_color', true);
    if (empty($main_color)) {
      $main_color = '173f75'; // Default main color
    }
    $bg_color = get_post_meta($post->ID, '_mtp_bg_color', true);
    if (empty($bg_color)) {
      $bg_color = '000000'; // Default background color
    }
    $logo_size = get_post_meta($post->ID, '_mtp_logo_size', true);
    if (empty($logo_size)) {
      $logo_size = '20'; // Default logo size
    }
    $bg_opacity = get_post_meta($post->ID, '_mtp_bg_opacity', true);
    if (empty($bg_opacity) && $bg_opacity !== '0') {
      $bg_opacity = '0'; // Default opacity
    }

    // Use empty string if no tournament ID, but still generate shortcode
    if (empty($tournament_id)) {
      $tournament_id = '';
    }
    
    // Combine background color and opacity to create 8-character hex
    $combined_bg_color = $bg_color;
    if ($bg_opacity !== '' && $bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_bg_color = $bg_color . $opacity_hex;
    }
    
    $border_color = get_post_meta($post->ID, '_mtp_border_color', true);
    if (empty($border_color)) {
      $border_color = 'bbbbbb'; // Default border color
    }
    
    $head_bottom_border_color = get_post_meta($post->ID, '_mtp_head_bottom_border_color', true);
    if (empty($head_bottom_border_color)) {
      $head_bottom_border_color = 'bbbbbb'; // Default head bottom border color
    }
    
    $even_bg_color = get_post_meta($post->ID, '_mtp_even_bg_color', true);
    if (empty($even_bg_color)) {
      $even_bg_color = 'f0f8ff'; // Default even rows background color
    }
    $even_bg_opacity = get_post_meta($post->ID, '_mtp_even_bg_opacity', true);
    if (empty($even_bg_opacity) && $even_bg_opacity !== '0') {
      $even_bg_opacity = '69'; // Default opacity (69%)
    }
    
    // Combine even rows background color and opacity
    $combined_even_bg_color = $even_bg_color;
    if ($even_bg_opacity !== '' && $even_bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($even_bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_even_bg_color = $even_bg_color . $opacity_hex;
    }

    $odd_bg_color = get_post_meta($post->ID, '_mtp_odd_bg_color', true);
    if (empty($odd_bg_color)) {
      $odd_bg_color = 'ffffff'; // Default odd rows background color
    }
    $odd_bg_opacity = get_post_meta($post->ID, '_mtp_odd_bg_opacity', true);
    if (empty($odd_bg_opacity) && $odd_bg_opacity !== '0') {
      $odd_bg_opacity = '69'; // Default opacity (69%)
    }
    
    // Combine odd rows background color and opacity
    $combined_odd_bg_color = $odd_bg_color;
    if ($odd_bg_opacity !== '' && $odd_bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($odd_bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_odd_bg_color = $odd_bg_color . $opacity_hex;
    }

    $hover_bg_color = get_post_meta($post->ID, '_mtp_hover_bg_color', true);
    if (empty($hover_bg_color)) {
      $hover_bg_color = 'eeeeff'; // Default hover rows background color
    }
    $hover_bg_opacity = get_post_meta($post->ID, '_mtp_hover_bg_opacity', true);
    if (empty($hover_bg_opacity) && $hover_bg_opacity !== '0') {
      $hover_bg_opacity = '69'; // Default opacity (69%)
    }
    
    // Combine hover rows background color and opacity
    $combined_hover_bg_color = $hover_bg_color;
    if ($hover_bg_opacity !== '' && $hover_bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($hover_bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_hover_bg_color = $hover_bg_color . $opacity_hex;
    }

    $head_bg_color = get_post_meta($post->ID, '_mtp_head_bg_color', true);
    if (empty($head_bg_color)) {
      $head_bg_color = 'eeeeff'; // Default head background color
    }
    $head_bg_opacity = get_post_meta($post->ID, '_mtp_head_bg_opacity', true);
    if (empty($head_bg_opacity) && $head_bg_opacity !== '0') {
      $head_bg_opacity = '100'; // Default opacity (100%)
    }

    // Combine head rows background color and opacity
    $combined_head_bg_color = $head_bg_color;
    if ($head_bg_opacity !== '' && $head_bg_opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($head_bg_opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      $combined_head_bg_color = $head_bg_color . $opacity_hex;
    }
    
    // Generate the shortcode
    $shortcode = '[mtp-table id="' . esc_attr($tournament_id) . '" post_id="' . $post->ID . '" lang="en" s-size="' . esc_attr($font_size) . '" s-sizeheader="' . esc_attr($header_font_size) . '" s-color="' . esc_attr($text_color) . '" s-maincolor="' . esc_attr($main_color) . '" s-padding="' . esc_attr($table_padding) . '" s-innerpadding="' . esc_attr($inner_padding) . '" s-bgcolor="' . esc_attr($combined_bg_color). '" s-bcolor="' . esc_attr($border_color) . '" s-bbcolor="' . esc_attr($head_bottom_border_color) . '" s-bgeven="' . esc_attr($combined_even_bg_color) . '" s-logosize="' . esc_attr($logo_size) . '" s-bsizeh="' . esc_attr($bsizeh) . '" s-bsizev="' . esc_attr($bsizev) . '" s-bsizeoh="1" s-bsizeov="1" s-bbsize="2" s-bgodd="' . esc_attr($combined_odd_bg_color) . '" s-bgover="' . esc_attr($combined_hover_bg_color) . '" s-bghead="' . esc_attr($combined_head_bg_color) . '" width="' . esc_attr($width) . '" height="152"]';

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
      echo __('Enter a Tournament ID above to display live tournament data. Without an ID, an empty table will be shown.', 'meinturnierplan-wp');
      echo '</div>';
    }
    
    // Add JavaScript for copy functionality and live update
    echo '<script>
    jQuery(document).ready(function($) {
      // Copy shortcode to clipboard
      $("#mtp_copy_shortcode").on("click", function() {
        var shortcodeField = $("#mtp_shortcode_field");
        shortcodeField.select();
        document.execCommand("copy");
        
        $("#mtp_copy_success").fadeIn().delay(2000).fadeOut();
      });
      
      // Update shortcode when tournament ID or width changes
      $("#mtp_tournament_id, #mtp_table_width, #mtp_font_size, #mtp_header_font_size, #mtp_table_padding, #mtp_inner_padding, #mtp_text_color, #mtp_main_color, #mtp_bg_color, #mtp_logo_size, #mtp_bg_opacity, #mtp_border_color, #mtp_head_bottom_border_color, #mtp_even_bg_color, #mtp_even_bg_opacity, #mtp_odd_bg_color, #mtp_odd_bg_opacity, #mtp_hover_bg_color, #mtp_hover_bg_opacity, #mtp_head_bg_color, #mtp_head_bg_opacity, #mtp_bsizeh, #mtp_bsizev").on("input", function() {
        var tournamentId = $("#mtp_tournament_id").val();
        var width = $("#mtp_table_width").val();
        var fontSize = $("#mtp_font_size").val();
        var headerFontSize = $("#mtp_header_font_size").val();
        var tablePadding = $("#mtp_table_padding").val();
        var innerPadding = $("#mtp_inner_padding").val();
        var textColor = $("#mtp_text_color").val().replace("#", "");
        var mainColor = $("#mtp_main_color").val().replace("#", "");
        var bgColor = $("#mtp_bg_color").val().replace("#", "");
        var bgOpacity = $("#mtp_bg_opacity").val();
        var evenBgColor = $("#mtp_even_bg_color").val().replace("#", "");
        var evenBgOpacity = $("#mtp_even_bg_opacity").val();
        var oddBgColor = $("#mtp_odd_bg_color").val().replace("#", "");
        var oddBgOpacity = $("#mtp_odd_bg_opacity").val();
        var hoverBgColor = $("#mtp_hover_bg_color").val().replace("#", "");
        var hoverBgOpacity = $("#mtp_hover_bg_opacity").val();
        var headBgColor = $("#mtp_head_bg_color").val().replace("#", "");
        var headBgOpacity = $("#mtp_head_bg_opacity").val();
        var borderColor = $("#mtp_border_color").val().replace("#", "");
        var headBottomBorderColor = $("#mtp_head_bottom_border_color").val().replace("#", "");
        var logoSize = $("#mtp_logo_size").val();
        var bsizeh = $("#mtp_bsizeh").val();
        var bsizev = $("#mtp_bsizev").val();
        var postId = ' . intval($post->ID) . ';
        
        // Convert opacity percentage to hex (0-100% to 00-FF)
        var opacityHex = Math.round((bgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var bgColorWithOpacity = bgColor + opacityHex;
        var evenOpacityHex = Math.round((evenBgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var evenBgColorWithOpacity = evenBgColor + evenOpacityHex;
        var oddOpacityHex = Math.round((oddBgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var oddBgColorWithOpacity = oddBgColor + oddOpacityHex;
        var hoverOpacityHex = Math.round((hoverBgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var hoverBgColorWithOpacity = hoverBgColor + hoverOpacityHex;
        var headBgOpacityHex = Math.round((headBgOpacity / 100) * 255).toString(16).padStart(2, "0");
        var headBgColorWithOpacity = headBgColor + headBgOpacityHex;

        // Always generate shortcode, even with empty tournament ID
        var newShortcode = "[mtp-table id=\"" + tournamentId + "\" post_id=\"" + postId + "\" lang=\"en\" s-size=\"" + fontSize + "\" s-sizeheader=\"" + headerFontSize + "\" s-color=\"" + textColor + "\" s-maincolor=\"" + mainColor + "\" s-padding=\"" + tablePadding + "\" s-innerpadding=\"" + innerPadding + "\" s-bgcolor=\"" + bgColorWithOpacity + "\" s-bcolor=\"" + borderColor + "\" s-bbcolor=\"" + headBottomBorderColor + "\" s-logosize=\"" + logoSize + "\" s-bsizeh=\"" + bsizeh + "\" s-bsizev=\"" + bsizev + "\" s-bsizeoh=\"1\" s-bsizeov=\"1\" s-bbsize=\"2\" s-bgeven=\"" + evenBgColorWithOpacity + "\" s-bgodd=\"" + oddBgColorWithOpacity + "\" s-bgover=\"" + hoverBgColorWithOpacity + "\" s-bghead=\"" + headBgColorWithOpacity + "\" width=\"" + width + "\" height=\"152\"]";
        $("#mtp_shortcode_field").val(newShortcode);
      });
    });
    </script>';
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
    
    // Save tournament ID
    if (isset($_POST['mtp_tournament_id'])) {
      $tournament_id = sanitize_text_field($_POST['mtp_tournament_id']);
      update_post_meta($post_id, '_mtp_tournament_id', $tournament_id);
    }
    
    // Save width
    if (isset($_POST['mtp_table_width'])) {
      $width = sanitize_text_field($_POST['mtp_table_width']);
      update_post_meta($post_id, '_mtp_table_width', $width);
    }
    
    // Save font size
    if (isset($_POST['mtp_font_size'])) {
      $font_size = sanitize_text_field($_POST['mtp_font_size']);
      update_post_meta($post_id, '_mtp_font_size', $font_size);
    }
    
    // Save header font size
    if (isset($_POST['mtp_header_font_size'])) {
      $header_font_size = sanitize_text_field($_POST['mtp_header_font_size']);
      update_post_meta($post_id, '_mtp_header_font_size', $header_font_size);
    }

    // Save border vertical size
    if (isset($_POST['mtp_bsizeh'])) {
      $bsizeh = sanitize_text_field($_POST['mtp_bsizeh']);
      update_post_meta($post_id, '_mtp_bsizeh', $bsizeh);
    }

    // Save border horizontal size
    if (isset($_POST['mtp_bsizev'])) {
      $bsizev = sanitize_text_field($_POST['mtp_bsizev']);
      update_post_meta($post_id, '_mtp_bsizev', $bsizev);
    }

    // Save table padding
    if (isset($_POST['mtp_table_padding'])) {
      $table_padding = sanitize_text_field($_POST['mtp_table_padding']);
      update_post_meta($post_id, '_mtp_table_padding', $table_padding);
    }
    
    // Save inner padding
    if (isset($_POST['mtp_inner_padding'])) {
      $inner_padding = sanitize_text_field($_POST['mtp_inner_padding']);
      update_post_meta($post_id, '_mtp_inner_padding', $inner_padding);
    }
    
    // Save text color
    if (isset($_POST['mtp_text_color'])) {
      $text_color = sanitize_hex_color($_POST['mtp_text_color']);
      if ($text_color) {
        // Remove # from color value for storage
        $text_color = ltrim($text_color, '#');
        update_post_meta($post_id, '_mtp_text_color', $text_color);
      }
    }
    
    // Save main color
    if (isset($_POST['mtp_main_color'])) {
      $main_color = sanitize_hex_color($_POST['mtp_main_color']);
      if ($main_color) {
        // Remove # from color value for storage
        $main_color = ltrim($main_color, '#');
        update_post_meta($post_id, '_mtp_main_color', $main_color);
      }
    }

    // Save background color and opacity
    if (isset($_POST['mtp_bg_color'])) {
      $bg_color = sanitize_hex_color($_POST['mtp_bg_color']);
      if ($bg_color) {
        // Remove # from color value for storage
        $bg_color = ltrim($bg_color, '#');
        update_post_meta($post_id, '_mtp_bg_color', $bg_color);
      }
    }
    
    // Save background opacity
    if (isset($_POST['mtp_bg_opacity'])) {
      $bg_opacity = absint($_POST['mtp_bg_opacity']);
      // Ensure opacity is between 0 and 100
      $bg_opacity = max(0, min(100, $bg_opacity));
      update_post_meta($post_id, '_mtp_bg_opacity', $bg_opacity);
    }
    
    // Save border color
    if (isset($_POST['mtp_border_color'])) {
      $border_color = sanitize_hex_color($_POST['mtp_border_color']);
      if ($border_color) {
        // Remove # from color value for storage
        $border_color = ltrim($border_color, '#');
        update_post_meta($post_id, '_mtp_border_color', $border_color);
      }
    }
    
    // Save head bottom border color
    if (isset($_POST['mtp_head_bottom_border_color'])) {
      $head_bottom_border_color = sanitize_hex_color($_POST['mtp_head_bottom_border_color']);
      if ($head_bottom_border_color) {
        // Remove # from color value for storage
        $head_bottom_border_color = ltrim($head_bottom_border_color, '#');
        update_post_meta($post_id, '_mtp_head_bottom_border_color', $head_bottom_border_color);
      }
    }
    
    // Save even rows background color and opacity
    if (isset($_POST['mtp_even_bg_color'])) {
      $even_bg_color = sanitize_hex_color($_POST['mtp_even_bg_color']);
      if ($even_bg_color) {
        // Remove # from color value for storage
        $even_bg_color = ltrim($even_bg_color, '#');
        update_post_meta($post_id, '_mtp_even_bg_color', $even_bg_color);
      }
    }
    
    if (isset($_POST['mtp_even_bg_opacity'])) {
      $even_bg_opacity = intval($_POST['mtp_even_bg_opacity']);
      if ($even_bg_opacity >= 0 && $even_bg_opacity <= 100) {
        update_post_meta($post_id, '_mtp_even_bg_opacity', $even_bg_opacity);
      }
    }

    // Save odd rows background color and opacity
    if (isset($_POST['mtp_odd_bg_color'])) {
      $odd_bg_color = sanitize_hex_color($_POST['mtp_odd_bg_color']);
      if ($odd_bg_color) {
        // Remove # from color value for storage
        $odd_bg_color = ltrim($odd_bg_color, '#');
        update_post_meta($post_id, '_mtp_odd_bg_color', $odd_bg_color);
      }
    }

    if (isset($_POST['mtp_odd_bg_opacity'])) {
      $odd_bg_opacity = intval($_POST['mtp_odd_bg_opacity']);
      if ($odd_bg_opacity >= 0 && $odd_bg_opacity <= 100) {
        update_post_meta($post_id, '_mtp_odd_bg_opacity', $odd_bg_opacity);
      }
    }

    // Save hover rows background color and opacity
    if (isset($_POST['mtp_hover_bg_color'])) {
      $hover_bg_color = sanitize_hex_color($_POST['mtp_hover_bg_color']);
      if ($hover_bg_color) {
        // Remove # from color value for storage
        $hover_bg_color = ltrim($hover_bg_color, '#');
        update_post_meta($post_id, '_mtp_hover_bg_color', $hover_bg_color);
      }
    }

    if (isset($_POST['mtp_hover_bg_opacity'])) {
      $hover_bg_opacity = intval($_POST['mtp_hover_bg_opacity']);
      if ($hover_bg_opacity >= 0 && $hover_bg_opacity <= 100) {
        update_post_meta($post_id, '_mtp_hover_bg_opacity', $hover_bg_opacity);
      }
    }

    // Save head background color and opacity
    if (isset($_POST['mtp_head_bg_color'])) {
      $head_bg_color = sanitize_hex_color($_POST['mtp_head_bg_color']);
      if ($head_bg_color) {
        // Remove # from color value for storage
        $head_bg_color = ltrim($head_bg_color, '#');
        update_post_meta($post_id, '_mtp_head_bg_color', $head_bg_color);
      }
    }

    if (isset($_POST['mtp_head_bg_opacity'])) {
      $head_bg_opacity = intval($_POST['mtp_head_bg_opacity']);
      if ($head_bg_opacity >= 0 && $head_bg_opacity <= 100) {
        update_post_meta($post_id, '_mtp_head_bg_opacity', $head_bg_opacity);
      }
    }

    // Save logo size
    if (isset($_POST['mtp_logo_size'])) {
      $logo_size = sanitize_text_field($_POST['mtp_logo_size']);
      update_post_meta($post_id, '_mtp_logo_size', $logo_size);
    }
  }
  
  /**
   * Initialize shortcode
   */
  public function init_shortcode() {
    add_shortcode('mtp-table', array($this, 'shortcode_callback'));
  }
  
  /**
   * Shortcode callback
   */
  public function shortcode_callback($atts) {
    $atts = shortcode_atts(array(
      'id' => '',
      'post_id' => '', // Internal WordPress post ID (optional)
      'lang' => 'en',
      's-size' => '9',
      's-sizeheader' => '10',
      's-color' => '000000',
      's-maincolor' => '173f75',
      's-padding' => '2',
      's-innerpadding' => '5',
      's-bgcolor' => '00000000',
      's-logosize' => '20',
      's-bcolor' => 'bbbbbb',
      's-bsizeh' => '1',
      's-bsizev' => '1',
      's-bsizeoh' => '1',
      's-bsizeov' => '1',
      's-bbcolor' => 'bbbbbb',
      's-bbsize' => '2',
      's-bgeven' => 'f0f8ffb0',
      's-bgodd' => 'ffffffb0',
      's-bgover' => 'eeeeffb0',
      's-bghead' => 'eeeeffff',
      'width' => '',
      'height' => '152'
    ), $atts, 'mtp-table');
    
    // Use post_id if provided for getting width from meta, otherwise use null
    $post_id = !empty($atts['post_id']) ? $atts['post_id'] : null;
    
    // Always render table - empty if no ID, with data if ID provided
    return $this->render_table_html($post_id, $atts);
  }
  
  /**
   * Initialize widget
   */
  public function init_widget() {
    add_action('widgets_init', function() {
      register_widget('MTP_Table_Widget');
    });
    
    // Include widget class
    require_once MTP_PLUGIN_PATH . 'includes/class-mtp-table-widget.php';
  }
  

  

  

  
  /**
   * AJAX handler for table preview (existing one for admin)
   */
  public function ajax_preview_table() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'mtp_preview_nonce')) {
      wp_die('Security check failed');
    }
    
    $post_id = absint($_POST['post_id']);
    $tournament_id = sanitize_text_field($_POST['tournament_id']);
    $width = sanitize_text_field($_POST['width']);
    $font_size = sanitize_text_field($_POST['font_size']);
    $header_font_size = sanitize_text_field($_POST['header_font_size']);
    $bsizeh = sanitize_text_field($_POST['bsizeh']);
    $bsizev = sanitize_text_field($_POST['bsizev']);
    $table_padding = sanitize_text_field($_POST['table_padding']);
    $inner_padding = sanitize_text_field($_POST['inner_padding']);
    $text_color = sanitize_text_field($_POST['text_color']);
    $main_color = sanitize_text_field($_POST['main_color']);
    $bg_color = sanitize_text_field($_POST['bg_color']);
    $border_color = isset($_POST['border_color']) ? sanitize_text_field($_POST['border_color']) : 'bbbbbb';
    $head_bottom_border_color = isset($_POST['head_bottom_border_color']) ? sanitize_text_field($_POST['head_bottom_border_color']) : 'bbbbbb';
    $even_bg_color = isset($_POST['even_bg_color']) ? sanitize_text_field($_POST['even_bg_color']) : 'f0f8ffb0';
    $odd_bg_color = isset($_POST['odd_bg_color']) ? sanitize_text_field($_POST['odd_bg_color']) : 'ffffffb0';
    $hover_bg_color = isset($_POST['hover_bg_color']) ? sanitize_text_field($_POST['hover_bg_color']) : 'eeeeffb0';
    $head_bg_color = isset($_POST['head_bg_color']) ? sanitize_text_field($_POST['head_bg_color']) : 'eeeeffff';
    $logo_size = sanitize_text_field($_POST['logo_size']);
    
    // Create attributes for rendering
    $atts = array(
      'id' => $tournament_id,
      'width' => $width ? $width : '300',
      's-size' => $font_size ? $font_size : '9',
      's-sizeheader' => $header_font_size ? $header_font_size : '10',
      's-padding' => $table_padding ? $table_padding : '2',
      's-innerpadding' => $inner_padding ? $inner_padding : '5',
      's-color' => $text_color ? $text_color : '000000',
      's-maincolor' => $main_color ? $main_color : '173f75',
      's-bgcolor' => $bg_color ? $bg_color : '00000000',
      's-bcolor' => $border_color ? $border_color : 'bbbbbb',
      's-bbcolor' => $head_bottom_border_color ? $head_bottom_border_color : 'bbbbbb',
      's-bgeven' => $even_bg_color ? $even_bg_color : 'f0f8ffb0',
      's-bgodd' => $odd_bg_color ? $odd_bg_color : 'ffffffb0',
      's-bgover' => $hover_bg_color ? $hover_bg_color : 'eeeeffb0',
      's-bghead' => $head_bg_color ? $head_bg_color : 'eeeeffff',
      's-logosize' => $logo_size ? $logo_size : '20',
      's-bsizeh' => $bsizeh ? $bsizeh : '1',
      's-bsizev' => $bsizev ? $bsizev : '1'
    );
    
    $html = $this->render_table_html($post_id, $atts);
    
    wp_send_json_success($html);
  }
  
  /**
   * Combine hex color and opacity percentage into 8-character hex
   */
  private function combine_color_opacity($hex_color, $opacity_percent) {
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
   */
  private function get_bg_color_with_opacity($post_id) {
    if (!$post_id) {
      return '00000000'; // Transparent default
    }
    
    $bg_color = get_post_meta($post_id, '_mtp_bg_color', true);
    $bg_opacity = get_post_meta($post_id, '_mtp_bg_opacity', true);
    
    if (empty($bg_color)) {
      $bg_color = '000000'; // Default color
    }
    
    if (empty($bg_opacity) && $bg_opacity !== '0') {
      $bg_opacity = 0; // Default opacity
    }
    
    return $this->combine_color_opacity($bg_color, $bg_opacity);
  }
  
  /**
   * Render table HTML
   */
  public function render_table_html($table_id, $atts = array()) {
    // Get tournament ID from attributes or post meta
    $tournament_id = '';
    if (!empty($atts['id'])) {
      $tournament_id = $atts['id'];
    } elseif (!empty($table_id)) {
      $tournament_id = get_post_meta($table_id, '_mtp_tournament_id', true);
    }
    
    // If no tournament ID, show empty static table
    if (empty($tournament_id)) {
      return $this->render_empty_table($atts);
    }
    
    // Get width from shortcode attribute or post meta
    $width = !empty($atts['width']) ? $atts['width'] : get_post_meta($table_id, '_mtp_table_width', true);
    if (empty($width)) {
      $width = '300'; // Default width
    }
    
    // Get font size from shortcode attribute or post meta
    $font_size = !empty($atts['s-size']) ? $atts['s-size'] : get_post_meta($table_id, '_mtp_font_size', true);
    if (empty($font_size)) {
      $font_size = '9'; // Default font size
    }

    // Get border vertical size from shortcode attribute or post meta
    $bsizeh = !empty($atts['s-bsizeh']) ? $atts['s-bsizeh'] : get_post_meta($table_id, '_mtp_bsizeh', true);
    if (empty($bsizeh)) {
      $bsizeh = '1'; // Default border vertical size
    }

    // Get border horizontal size from shortcode attribute or post meta
    $bsizev = !empty($atts['s-bsizev']) ? $atts['s-bsizev'] : get_post_meta($table_id, '_mtp_bsizev', true);
    if (empty($bsizev)) {
      $bsizev = '1'; // Default border horizontal size
    }
    
    // Get header font size from shortcode attribute or post meta
    $header_font_size = !empty($atts['s-sizeheader']) ? $atts['s-sizeheader'] : get_post_meta($table_id, '_mtp_header_font_size', true);
    if (empty($header_font_size)) {
      $header_font_size = '10'; // Default header font size
    }
    
    // Get table padding from shortcode attribute or post meta
    $table_padding = !empty($atts['s-padding']) ? $atts['s-padding'] : get_post_meta($table_id, '_mtp_table_padding', true);
    if (empty($table_padding)) {
      $table_padding = '2'; // Default table padding
    }
    
    // Get inner padding from shortcode attribute or post meta
    $inner_padding = !empty($atts['s-innerpadding']) ? $atts['s-innerpadding'] : get_post_meta($table_id, '_mtp_inner_padding', true);
    if (empty($inner_padding)) {
      $inner_padding = '5'; // Default inner padding
    }
    
    // Get text color from shortcode attribute or post meta
    $text_color = !empty($atts['s-color']) ? $atts['s-color'] : get_post_meta($table_id, '_mtp_text_color', true);
    if (empty($text_color)) {
      $text_color = '000000'; // Default text color (black)
    }
    
    // Get main color from shortcode attribute or post meta
    $main_color = !empty($atts['s-maincolor']) ? $atts['s-maincolor'] : get_post_meta($table_id, '_mtp_main_color', true);
    if (empty($main_color)) {
      $main_color = '173f75'; // Default main color (blue)
    }

    // Get background color from shortcode attribute or post meta (with opacity handling)
    $bg_color = '';
    if (!empty($atts['s-bgcolor'])) {
      $bg_color = $atts['s-bgcolor'];
    } else {
      $bg_color = $this->get_bg_color_with_opacity($table_id);
    }
    
    // Ensure we have a valid background color (fallback to transparent)
    if (empty($bg_color)) {
      $bg_color = '00000000';
    }
    
    // Get border color from shortcode attribute or post meta
    $border_color = !empty($atts['s-bcolor']) ? $atts['s-bcolor'] : get_post_meta($table_id, '_mtp_border_color', true);
    if (empty($border_color)) {
      $border_color = 'bbbbbb'; // Default border color (light gray)
    }
    
    // Get head bottom border color from shortcode attribute or post meta
    $head_bottom_border_color = !empty($atts['s-bbcolor']) ? $atts['s-bbcolor'] : get_post_meta($table_id, '_mtp_head_bottom_border_color', true);
    if (empty($head_bottom_border_color)) {
      $head_bottom_border_color = 'bbbbbb'; // Default head bottom border color (light gray)
    }

    // Get even background color from shortcode attribute or post meta (with opacity handling)
    $even_bg_color = '';
    if (!empty($atts['s-bgeven'])) {
      $even_bg_color = $atts['s-bgeven'];
    } else {
      $even_bg_color = $this->get_bg_color_with_opacity($table_id);
    }

    // Get odd background color from shortcode attribute or post meta (with opacity handling)
    $odd_bg_color = '';
    if (!empty($atts['s-bgodd'])) {
      $odd_bg_color = $atts['s-bgodd'];
    } else {
      $odd_bg_color = $this->get_bg_color_with_opacity($table_id);
    }

    // Get hover background color from shortcode attribute or post meta (with opacity handling)
    $hover_bg_color = '';
    if (!empty($atts['s-bgover'])) {
      $hover_bg_color = $atts['s-bgover'];
    } else {
      $hover_bg_color = $this->get_bg_color_with_opacity($table_id);
    }

    // Get head background color from shortcode attribute or post meta (with opacity handling)
    $head_bg_color = '';
    if (!empty($atts['s-bghead'])) {
      $head_bg_color = $atts['s-bghead'];
    } else {
      $head_bg_color = $this->get_bg_color_with_opacity($table_id);
    }
    
    // Get height
    $height = !empty($atts['height']) ? $atts['height'] : '152';

    // Get logo size
    $logo_size = !empty($atts['s-logosize']) ? $atts['s-logosize'] : get_post_meta($table_id, '_mtp_logo_size', true);
    if (empty($logo_size)) {
      $logo_size = '20'; // Default logo size
    }
    
    // Build URL parameters array
    $params = array();
    $params['id'] = $tournament_id;
    
    // Map shortcode styling parameters to URL parameters
    if (!empty($atts['s-size'])) $params['s[size]'] = $atts['s-size'];
    if (!empty($atts['s-sizeheader'])) $params['s[sizeheader]'] = $atts['s-sizeheader'];
    if (!empty($atts['s-color'])) $params['s[color]'] = $atts['s-color'];
    if (!empty($atts['s-maincolor'])) $params['s[maincolor]'] = $atts['s-maincolor'];
    if (!empty($atts['s-padding'])) $params['s[padding]'] = $atts['s-padding'];
    if (!empty($atts['s-innerpadding'])) $params['s[innerpadding]'] = $atts['s-innerpadding'];
    if (!empty($atts['s-bgcolor'])) $params['s[bgcolor]'] = $atts['s-bgcolor'];
    if (!empty($atts['s-logosize'])) $params['s[logosize]'] = $atts['s-logosize'];
    if (!empty($atts['s-bcolor'])) $params['s[bcolor]'] = $atts['s-bcolor'];
    if (!empty($atts['s-bsizeh'])) $params['s[bsizeh]'] = $atts['s-bsizeh'];
    if (!empty($atts['s-bsizev'])) $params['s[bsizev]'] = $atts['s-bsizev'];
    if (!empty($atts['s-bsizeoh'])) $params['s[bsizeoh]'] = $atts['s-bsizeoh'];
    if (!empty($atts['s-bsizeov'])) $params['s[bsizeov]'] = $atts['s-bsizeov'];
    if (!empty($atts['s-bbcolor'])) $params['s[bbcolor]'] = $atts['s-bbcolor'];
    if (!empty($atts['s-bbsize'])) $params['s[bbsize]'] = $atts['s-bbsize'];
    if (!empty($atts['s-bgeven'])) $params['s[bgeven]'] = $atts['s-bgeven'];
    if (!empty($atts['s-bgodd'])) $params['s[bgodd]'] = $atts['s-bgodd'];
    if (!empty($atts['s-bgover'])) $params['s[bgover]'] = $atts['s-bgover'];
    if (!empty($atts['s-bghead'])) $params['s[bghead]'] = $atts['s-bghead'];
    
    // Ensure font size is always set, either from attributes or our retrieved value
    if (empty($params['s[size]'])) {
      $params['s[size]'] = $font_size;
    }
    
    // Ensure header font size is always set, either from attributes or our retrieved value
    if (empty($params['s[sizeheader]'])) {
      $params['s[sizeheader]'] = $header_font_size;
    }
    
    // Ensure table padding is always set, either from attributes or our retrieved value
    if (empty($params['s[padding]'])) {
      $params['s[padding]'] = $table_padding;
    }
    
    // Ensure inner padding is always set, either from attributes or our retrieved value
    if (empty($params['s[innerpadding]'])) {
      $params['s[innerpadding]'] = $inner_padding;
    }
    
    // Ensure text color is always set, either from attributes or our retrieved value
    if (empty($params['s[color]'])) {
      $params['s[color]'] = $text_color;
    }

    // Ensure main color is always set, either from attributes or our retrieved value
    if (empty($params['s[maincolor]'])) {
      $params['s[maincolor]'] = $main_color;
    }
    
    // Ensure border color is always set, either from attributes or our retrieved value
    if (empty($params['s[bcolor]'])) {
      $params['s[bcolor]'] = $border_color;
    }
    
    // Ensure head bottom border color is always set, either from attributes or our retrieved value
    if (empty($params['s[bbcolor]'])) {
      $params['s[bbcolor]'] = $head_bottom_border_color;
    }

    // Ensure odd background color is always set, either from attributes or our retrieved value
    if (empty($params['s[bgodd]'])) {
      $params['s[bgodd]'] = $odd_bg_color;
    }

    // Ensure even background color is always set, either from attributes or our retrieved value
    if (empty($params['s[bgeven]'])) {
      $params['s[bgeven]'] = $even_bg_color;
    }

    // Ensure even background hover color is always set, either from attributes or our retrieved value
    if (empty($params['s[bgover]'])) {
      $params['s[bgover]'] = $hover_bg_color;
    }

    // Ensure head background color is always set, either from attributes or our retrieved value
    if (empty($params['s[bghead]'])) {
      $params['s[bghead]'] = $head_bg_color;
    }

    // Ensure logo size is always set, either from attributes or our retrieved value
    if (empty($params['s[logosize]'])) {
      $params['s[logosize]'] = $logo_size;
    }

    // Ensure border vertical size is always set, either from attributes or our retrieved value
    if (empty($params['s[bsizeh]'])) {
      $params['s[bsizeh]'] = $bsizeh;
    }

    // Ensure border horizontal size is always set, either from attributes or our retrieved value
    if (empty($params['s[bsizev]'])) {
      $params['s[bsizev]'] = $bsizev;
    }

    // Add wrap=false parameter
    $params['s[wrap]'] = 'false';
    
    // Build the iframe URL
    $iframe_url = 'https://www.meinturnierplan.de/displayTable.php?' . http_build_query($params);
    
    // Generate unique ID for this iframe instance
    $iframe_id = 'mtp-table-' . $tournament_id . '-' . substr(md5(serialize($atts)), 0, 8);
    
    // Build the iframe HTML
    $iframe_html = sprintf(
      '<iframe id="%s" src="%s" style="overflow:hidden;" allowtransparency="true" frameborder="0" width="%s" height="%s">
        <p>%s <a href="https://www.meinturnierplan.de/showit.php?id=%s">%s</a></p>
      </iframe>',
      esc_attr($iframe_id),
      esc_url($iframe_url),
      esc_attr($width),
      esc_attr($height),
      __('Your browser does not support the tournament widget.', 'meinturnierplan-wp'),
      esc_attr($tournament_id),
      __('Go to Tournament.', 'meinturnierplan-wp')
    );
    
    return $iframe_html;
  }
  
  /**
   * Render empty static table when no tournament ID is provided
   */
  private function render_empty_table($atts = array()) {
    // Get width from shortcode attributes
    $width = !empty($atts['width']) ? $atts['width'] : '300';
    $height = !empty($atts['height']) ? $atts['height'] : '152';
    
    // Parse styling parameters with defaults
    $size = !empty($atts['s-size']) ? $atts['s-size'] : '9';
    $sizeheader = !empty($atts['s-sizeheader']) ? $atts['s-sizeheader'] : '10';
    $color = !empty($atts['s-color']) ? '#' . ltrim($atts['s-color'], '#') : '#000000';
    $maincolor = !empty($atts['s-maincolor']) ? '#' . ltrim($atts['s-maincolor'], '#') : '#173f75';
    $padding = !empty($atts['s-padding']) ? $atts['s-padding'] : '2';
    $innerpadding = !empty($atts['s-innerpadding']) ? $atts['s-innerpadding'] : '5';
    $bgcolor = !empty($atts['s-bgcolor']) ? $this->hex_to_rgba($atts['s-bgcolor']) : 'transparent';
    $bcolor = !empty($atts['s-bcolor']) ? '#' . ltrim($atts['s-bcolor'], '#') : '#bbbbbb';
    $bsizeh = !empty($atts['s-bsizeh']) ? $atts['s-bsizeh'] : '1';
    $bsizev = !empty($atts['s-bsizev']) ? $atts['s-bsizev'] : '1';
    $bbcolor = !empty($atts['s-bbcolor']) ? '#' . ltrim($atts['s-bbcolor'], '#') : '#bbbbbb';
    $bbsize = !empty($atts['s-bbsize']) ? $atts['s-bbsize'] : '2';
    $bgeven = !empty($atts['s-bgeven']) ? $this->hex_to_rgba($atts['s-bgeven']) : 'rgba(240, 248, 255, 0.69)';
    $bgodd = !empty($atts['s-bgodd']) ? $this->hex_to_rgba($atts['s-bgodd']) : 'rgba(255, 255, 255, 0.69)';
    $bgover = !empty($atts['s-bgover']) ? $this->hex_to_rgba($atts['s-bgover']) : 'rgba(238, 238, 255, 0.69)';
    $bghead = !empty($atts['s-bghead']) ? $this->hex_to_rgba($atts['s-bghead']) : 'rgba(238, 238, 255, 1)';
    $logo_size = !empty($atts['s-logosize']) ? $atts['s-logosize'] : '20';
    
    // Generate unique ID for this table instance
    $table_id_unique = 'mtp-table-empty-' . substr(md5(serialize($atts)), 0, 8);
    
    // Build inline styles
    $table_style = sprintf(
      'width: %spx !important; height: %spx; font-size: %spt; color: %s; padding: %spx; background-color: %s; border: %spx solid %s;',
      esc_attr($width),
      esc_attr($height),
      esc_attr($size),
      esc_attr($color),
      esc_attr($padding),
      esc_attr($bgcolor),
      esc_attr($bbsize),
      esc_attr($bbcolor),
      esc_attr($bgeven),
      esc_attr($bgodd),
      esc_attr($bgover),
      esc_attr($bghead),
      esc_attr($logo_size),
      esc_attr($bsizeh),
      esc_attr($bsizev)
    );
    
    // Generate CSS for this specific table
    $css = sprintf('
    <style>
    #%s {
      width: %spx !important;
      max-width: %spx !important;
    }
    #%s th {
      font-size: %spt !important;
      color: %s !important;
      background-color: %s !important;
      border: %spx solid %s !important;
      border-top: %spx solid %s !important;
      border-bottom: %spx solid %s !important;
      padding: %spx !important;
    }
    #%s tbody tr:nth-child(even) {
      background-color: %s !important;
    }
    #%s tbody tr:nth-child(odd) {
      background-color: %s !important;
    }
    #%s tbody tr:hover {
      background-color: %s !important;
    }
    #%s td {
      border: %spx solid %s !important;
      border-left: %spx solid %s !important;
      border-right: %spx solid %s !important;
      padding: %spx !important;
      color: %s !important;
    }
    </style>',
      esc_attr($table_id_unique),
      esc_attr($width),
      esc_attr($width),
      esc_attr($table_id_unique),
      esc_attr($sizeheader),
      esc_attr($maincolor),
      esc_attr($bghead),
      esc_attr($bsizeh),
      esc_attr($bcolor),
      esc_attr($bsizeh),
      esc_attr($bcolor),
      esc_attr($bsizeh),
      esc_attr($bcolor),
      esc_attr($innerpadding),
      esc_attr($table_id_unique),
      esc_attr($bgeven),
      esc_attr($table_id_unique),
      esc_attr($bgodd),
      esc_attr($table_id_unique),
      esc_attr($bgover),
      esc_attr($table_id_unique),
      esc_attr($bsizev),
      esc_attr($bcolor),
      esc_attr($bsizev),
      esc_attr($bcolor),
      esc_attr($bsizev),
      esc_attr($bcolor),
      esc_attr($innerpadding),
      esc_attr($color)
    );
    
    // Generate the empty tournament table HTML
    $html = $css;
    $html .= '<table id="' . esc_attr($table_id_unique) . '" class="width100 centered mtp-tournament-table" name="RankTable" style="' . esc_attr($table_style) . '">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th title="' . __('Rank in Group', 'meinturnierplan-wp') . '">' . __('Pl', 'meinturnierplan-wp') . '</th>';
    $html .= '<th>' . __('Participant', 'meinturnierplan-wp') . '</th>';
    $html .= '<th title="' . __('Matches', 'meinturnierplan-wp') . '">' . __('M', 'meinturnierplan-wp') . '</th>';
    $html .= '<th title="' . __('Wins', 'meinturnierplan-wp') . '">' . __('W', 'meinturnierplan-wp') . '</th>';
    $html .= '<th title="' . __('Draws', 'meinturnierplan-wp') . '">' . __('D', 'meinturnierplan-wp') . '</th>';
    $html .= '<th title="' . __('Loss', 'meinturnierplan-wp') . '">' . __('L', 'meinturnierplan-wp') . '</th>';
    $html .= '<th title="' . __('Goals', 'meinturnierplan-wp') . '">' . __('G', 'meinturnierplan-wp') . '</th>';
    $html .= '<th title="' . __('Goal Difference', 'meinturnierplan-wp') . '">' . __('GD', 'meinturnierplan-wp') . '</th>';
    $html .= '<th title="' . __('Points', 'meinturnierplan-wp') . '">' . __('Pts', 'meinturnierplan-wp') . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    
    // Generate 5 empty rows
    for ($i = 0; $i < 5; $i++) {
      $html .= '<tr>';
      $html .= '<td class="tdRank">&nbsp;</td>';
      $html .= '<td class="tdRankTeamName"><div></div></td>';
      $html .= '<td class="tdNumGames">&nbsp;</td>';
      $html .= '<td class="tdNumWins">&nbsp;</td>';
      $html .= '<td class="tdNumEquals">&nbsp;</td>';
      $html .= '<td class="tdNumLosts">&nbsp;</td>';
      $html .= '<td class="tdGoals">&nbsp;</td>';
      $html .= '<td class="tdGoalDiff">&nbsp;</td>';
      $html .= '<td class="tdPoints">&nbsp;</td>';
      $html .= '</tr>';
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
    
    return $html;
  }
  
  /**
   * Convert hex color with alpha to rgba
   */
  private function hex_to_rgba($hex) {
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
   * Enqueue styles
   */
  public function enqueue_styles() {
    wp_enqueue_style(
      'mtp-tournament-table',
      MTP_PLUGIN_URL . 'assets/css/style.css',
      array(),
      MTP_PLUGIN_VERSION
    );
  }
  
  /**
   * Enqueue admin scripts
   */
  public function enqueue_admin_scripts($hook) {
    if ('post.php' == $hook || 'post-new.php' == $hook) {
      global $post;
      if ($post && $post->post_type == 'mtp_table') {
        wp_enqueue_script('jquery');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');
      }
    }
  }
  
  /**
   * Plugin activation
   */
  public function activate() {
    // Register the post type
    $this->register_post_type();
    
    // Flush rewrite rules
    flush_rewrite_rules();
  }
  
  /**
   * Plugin deactivation
   */
  public function deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
  }
}

// Initialize the plugin
new MeinTurnierplanWP();
