<?php
/**
 * Matches List Post Type Management Class
 *
 * @package MeinTurnierplan
 * @since 0.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Matches List Post Type Management Class
 */
class MTP_Matches_Post_Type {

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
      'name'                  => _x('Tournament Match Lists', 'Post type general name', 'meinturnierplan'),
      'singular_name'         => _x('Tournament Match List', 'Post type singular name', 'meinturnierplan'),
      'menu_name'             => _x('Tournament Match Lists', 'Admin Menu text', 'meinturnierplan'),
      'name_admin_bar'        => _x('Tournament Match List', 'Add New on Toolbar', 'meinturnierplan'),
      'add_new'               => __('Add New', 'meinturnierplan'),
      'add_new_item'          => __('Add New Tournament Match List', 'meinturnierplan'),
      'new_item'              => __('New Tournament Match List', 'meinturnierplan'),
      'edit_item'             => __('Edit Tournament Match List', 'meinturnierplan'),
      'view_item'             => __('View Tournament Match List', 'meinturnierplan'),
      'all_items'             => __('All Tournament Match Lists', 'meinturnierplan'),
      'search_items'          => __('Search Tournament Match Lists', 'meinturnierplan'),
      'parent_item_colon'     => __('Parent Tournament Match Lists:', 'meinturnierplan'),
      'not_found'             => __('No tournament match lists found.', 'meinturnierplan'),
      'not_found_in_trash'    => __('No tournament match lists found in Trash.', 'meinturnierplan'),
      'archives'              => _x('Tournament Match List archives', 'The post type archive label', 'meinturnierplan'),
      'insert_into_item'      => _x('Insert into tournament match list', 'Overrides the "Insert into post"/"Insert into page" phrase', 'meinturnierplan'),
      'uploaded_to_this_item' => _x('Uploaded to this tournament match list', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'meinturnierplan'),
      'filter_items_list'     => _x('Filter tournament matches list', 'Screen reader text for the filter links', 'meinturnierplan'),
      'items_list_navigation' => _x('Tournament matches list navigation', 'Screen reader text for the pagination', 'meinturnierplan'),
      'items_list'            => _x('Tournament matches list', 'Screen reader text for the items list', 'meinturnierplan'),
    );

    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array('slug' => 'tournament-match-list'),
      'capability_type'    => 'post',
      'has_archive'        => false,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-forms',
      'show_in_rest'       => false, // Disable Gutenberg editor
      'supports'           => array('title')
    );

    register_post_type('mtp_match_list', $args);
  }
}
