<?php
/**
 * Plugin Name: MeinTurnierplan
 * Plugin URI: https://github.com/danfisher85/meinturnierplan-wp
 * Description: A WordPress plugin to display tournament tables using custom post types, supporting widgets, blocks, and shortcodes.
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
    echo '</table>';
    
    // Preview section
    echo '<h3>' . __('Preview', 'meinturnierplan-wp') . '</h3>';
    echo '<div id="mtp-table-preview" style="border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">';
    // Always show a table - either with data (if ID provided) or empty (if no ID)
    echo $this->render_table_html($post->ID, array('id' => $tournament_id, 'width' => $width));
    echo '</div>';
    
    // Add JavaScript for live preview
    echo '<script>
    jQuery(document).ready(function($) {
      $("#mtp_tournament_id, #mtp_table_width").on("input", function() {
        var tournamentId = $("#mtp_tournament_id").val();
        var width = $("#mtp_table_width").val();
        var preview = $("#mtp-table-preview");
        
        // Always update preview - either with data or empty table
        var postId = ' . intval($post->ID) . ';
        $.post(ajaxurl, {
          action: "mtp_preview_table",
          post_id: postId,
          tournament_id: tournamentId,
          width: width,
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
    
    // Use empty string if no tournament ID, but still generate shortcode
    if (empty($tournament_id)) {
      $tournament_id = '';
    }
    
    // Generate the shortcode
    $shortcode = '[mtp-table id="' . esc_attr($tournament_id) . '" post_id="' . $post->ID . '" lang="en" s-size="9" s-sizeheader="10" s-color="000000" s-maincolor="173f75" s-padding="2" s-innerpadding="5" s-bgcolor="00000000" s-logosize="20" s-bcolor="bbbbbb" s-bsizeh="1" s-bsizev="1" s-bsizeoh="1" s-bsizeov="1" s-bbcolor="bbbbbb" s-bbsize="2" s-bgeven="f0f8ffb0" s-bgodd="ffffffb0" s-bgover="eeeeffb0" s-bghead="eeeeffff" width="' . esc_attr($width) . '" height="152"]';
    
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
      $("#mtp_tournament_id, #mtp_table_width").on("input", function() {
        var tournamentId = $("#mtp_tournament_id").val();
        var width = $("#mtp_table_width").val();
        var postId = ' . intval($post->ID) . ';
        
        // Always generate shortcode, even with empty tournament ID
        var newShortcode = "[mtp-table id=\"" + tournamentId + "\" post_id=\"" + postId + "\" lang=\"en\" s-size=\"9\" s-sizeheader=\"10\" s-color=\"000000\" s-maincolor=\"173f75\" s-padding=\"2\" s-innerpadding=\"5\" s-bgcolor=\"00000000\" s-logosize=\"20\" s-bcolor=\"bbbbbb\" s-bsizeh=\"1\" s-bsizev=\"1\" s-bsizeoh=\"1\" s-bsizeov=\"1\" s-bbcolor=\"bbbbbb\" s-bbsize=\"2\" s-bgeven=\"f0f8ffb0\" s-bgodd=\"ffffffb0\" s-bgover=\"eeeeffb0\" s-bghead=\"eeeeffff\" width=\"" + width + "\" height=\"152\"]";
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
    
    // Create attributes for rendering
    $atts = array(
      'id' => $tournament_id,
      'width' => $width ? $width : '300'
    );
    
    $html = $this->render_table_html($post_id, $atts);
    
    wp_send_json_success($html);
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
    
    // Get height
    $height = !empty($atts['height']) ? $atts['height'] : '152';
    
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
      esc_attr($bbcolor)
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
