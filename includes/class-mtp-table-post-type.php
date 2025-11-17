<?php
/**
 * Table Post Type Management Class
 *
 * @package MeinTurnierplan
 * @since   0.1.0
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Table Post Type Management Class
 */
class MTP_Table_Post_Type {

  /**
   * Constructor
   */
  public function __construct() {
    add_action('init', array($this, 'register_post_type'));
  }

  /**
   * Register the custom post type
   */
  public function register_post_type() {
    $labels = array(
      'name'                  => _x('Tournament Tables', 'Post type general name', 'meinturnierplan'),
      'singular_name'         => _x('Tournament Table', 'Post type singular name', 'meinturnierplan'),
      'menu_name'             => _x('Tournament Tables', 'Admin Menu text', 'meinturnierplan'),
      'name_admin_bar'        => _x('Tournament Table', 'Add New on Toolbar', 'meinturnierplan'),
      'add_new'               => __('Add New', 'meinturnierplan'),
      'add_new_item'          => __('Add New Tournament Table', 'meinturnierplan'),
      'new_item'              => __('New Tournament Table', 'meinturnierplan'),
      'edit_item'             => __('Edit Tournament Table', 'meinturnierplan'),
      'view_item'             => __('View Tournament Table', 'meinturnierplan'),
      'all_items'             => __('All Tournament Tables', 'meinturnierplan'),
      'search_items'          => __('Search Tournament Tables', 'meinturnierplan'),
      'parent_item_colon'     => __('Parent Tournament Tables:', 'meinturnierplan'),
      'not_found'             => __('No tournament tables found.', 'meinturnierplan'),
      'not_found_in_trash'    => __('No tournament tables found in Trash.', 'meinturnierplan'),
      'archives'              => _x('Tournament Table archives', 'The post type archive label', 'meinturnierplan'),
      'insert_into_item'      => _x('Insert into tournament table', 'Overrides the "Insert into post"/"Insert into page" phrase', 'meinturnierplan'),
      'uploaded_to_this_item' => _x('Uploaded to this tournament table', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'meinturnierplan'),
      'filter_items_list'     => _x('Filter tournament tables list', 'Screen reader text for the filter links', 'meinturnierplan'),
      'items_list_navigation' => _x('Tournament tables list navigation', 'Screen reader text for the pagination', 'meinturnierplan'),
      'items_list'            => _x('Tournament tables list', 'Screen reader text for the items list', 'meinturnierplan'),
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
      'has_archive'        => false,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-editor-table',
      'show_in_rest'       => false, // Disable Gutenberg editor
      'supports'           => array('title')
    );

    register_post_type('mtp_table', $args);
  }
}
