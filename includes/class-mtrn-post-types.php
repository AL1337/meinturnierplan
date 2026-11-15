<?php
/**
 * Custom post types: tournament table and tournament match list.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class MTRN_Post_Types {

  public function __construct() {
    add_action('init', array($this, 'register_post_types'));
  }

  /**
   * Register both post types
   */
  public function register_post_types() {
    foreach (MTRN_Options::types() as $type => $config) {
      register_post_type($config['post_type'], array(
        'labels'             => $this->labels($type),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => $config['rewrite_slug']),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => $config['menu_icon'],
        'show_in_rest'       => false, // Classic editor: the settings live in meta boxes
        'supports'           => array('title')
      ));
    }
  }

  /**
   * Post type labels (kept as literal strings so they stay translatable)
   */
  private function labels($type) {
    if ($type === 'matches') {
      return array(
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
    }

    return array(
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
  }
}
