<?php
/**
 * Post Type Management Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Post Type Management Class
 */
class MTP_Post_Type {

  /**
   * Constructor
   */
  public function __construct() {
    add_action('init', array($this, 'register_post_types'));
  }

  /**
   * Register the custom post types
   */
  public function register_post_types() {
    $this->register_tournament_table_post_type();
    $this->register_matches_post_type();
  }

  /**
   * Register the tournament table post type
   */
  public function register_tournament_table_post_type() {
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
      'supports'           => array('title')
    );

    register_post_type('mtp_table', $args);
  }

  /**
   * Register the matches post type
   */
  public function register_matches_post_type() {
    $labels = array(
      'name'                  => _x('Matches', 'Post type general name', 'meinturnierplan-wp'),
      'singular_name'         => _x('Matches', 'Post type singular name', 'meinturnierplan-wp'),
      'menu_name'             => _x('Matches', 'Admin Menu text', 'meinturnierplan-wp'),
      'name_admin_bar'        => _x('Matches', 'Add New on Toolbar', 'meinturnierplan-wp'),
      'add_new'               => __('Add New', 'meinturnierplan-wp'),
      'add_new_item'          => __('Add New Matches', 'meinturnierplan-wp'),
      'new_item'              => __('New Matches', 'meinturnierplan-wp'),
      'edit_item'             => __('Edit Matches', 'meinturnierplan-wp'),
      'view_item'             => __('View Matches', 'meinturnierplan-wp'),
      'all_items'             => __('All Matches', 'meinturnierplan-wp'),
      'search_items'          => __('Search Matches', 'meinturnierplan-wp'),
      'parent_item_colon'     => __('Parent Matches:', 'meinturnierplan-wp'),
      'not_found'             => __('No matches found.', 'meinturnierplan-wp'),
      'not_found_in_trash'    => __('No matches found in Trash.', 'meinturnierplan-wp'),
      'archives'              => _x('Matches archives', 'The post type archive label', 'meinturnierplan-wp'),
      'insert_into_item'      => _x('Insert into matches', 'Overrides the "Insert into post"/"Insert into page" phrase', 'meinturnierplan-wp'),
      'uploaded_to_this_item' => _x('Uploaded to this matches', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'meinturnierplan-wp'),
      'filter_items_list'     => _x('Filter matches list', 'Screen reader text for the filter links', 'meinturnierplan-wp'),
      'items_list_navigation' => _x('Matches list navigation', 'Screen reader text for the pagination', 'meinturnierplan-wp'),
      'items_list'            => _x('Matches list', 'Screen reader text for the items list', 'meinturnierplan-wp'),
    );

    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array('slug' => 'matches'),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-calendar-alt',
      'show_in_rest'       => false, // Disable Gutenberg editor
      'supports'           => array('title')
    );

    register_post_type('mtp_matches', $args);
  }
}
