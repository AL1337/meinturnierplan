<?php
/**
 * Widget class for Tournament Tables
 * 
 * @package MeinTurnierplan
 * @since   0.3.0
 * @version 1.2.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class MTRN_Table_Widget extends WP_Widget {

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct(
      'mtrn_table_widget',
      __('Tournament Table', 'meinturnierplan'),
      array(
        'description' => __('Display a tournament table.', 'meinturnierplan'),
        'classname'   => 'mtrn-table-widget'
      )
    );
  }

  /**
   * Widget output
   */
  public function widget($args, $instance) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['before_widget'] is already escaped by WordPress core
    echo $args['before_widget'];

    if (!empty($instance['title'])) {
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['before_title'] and $args['after_title'] are already escaped by WordPress core
      echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
    }

    if (!empty($instance['table_id'])) {
      // Use the same approach as the Gutenberg block - get settings from post meta
      $table_id = $instance['table_id'];

      // Prepare shortcode attributes (width and height are now auto-determined)
      $shortcode_atts = array('post_id' => $table_id);

      // Load all styling parameters from post meta to ensure customizations are applied
      $shortcode_atts = array_merge($shortcode_atts, $this->get_styling_attributes_from_meta($table_id));

      // Load other configuration parameters from post meta
      $shortcode_atts = array_merge($shortcode_atts, $this->get_config_attributes_from_meta($table_id));

      // Use the existing shortcode functionality
      $mtrn_plugin = MTRN_Plugin::instance();
      $shortcode = new MTRN_Table_Shortcode($mtrn_plugin->table_renderer);
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is already escaped in the renderer class
      echo $shortcode->shortcode_callback($shortcode_atts);
    } else {
      echo '<div class="mtrn-widget-placeholder">' . esc_html__('Please select a Tournament Table.', 'meinturnierplan') . '</div>';
    }

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['after_widget'] is already escaped by WordPress core
    echo $args['after_widget'];
  }

  /**
   * Widget form in admin
   */
  public function form($instance) {
    $title = !empty($instance['title']) ? $instance['title'] : __('Tournament Table', 'meinturnierplan');
    $table_id = !empty($instance['table_id']) ? $instance['table_id'] : '';

    // Get all tournament tables
    $tables = get_posts(array(
      'post_type'      => 'mtrn_table',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'title',
      'order'          => 'ASC'
    ));
    ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'meinturnierplan'); ?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
    </p>

    <p>
      <label for="<?php echo esc_attr($this->get_field_id('table_id')); ?>"><?php esc_html_e('Select Tournament Table:', 'meinturnierplan'); ?></label>
      <select class="widefat" id="<?php echo esc_attr($this->get_field_id('table_id')); ?>" name="<?php echo esc_attr($this->get_field_name('table_id')); ?>">
        <option value=""><?php esc_html_e('-- Select Table --', 'meinturnierplan'); ?></option>
        <?php foreach ($tables as $table): ?>
          <option value="<?php echo esc_attr($table->ID); ?>" <?php selected($table_id, $table->ID); ?>>
            <?php echo esc_html($table->post_title); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>

    <p>
      <small><?php esc_html_e('The widget will use all styling settings configured for the selected Tournament Table. Width and height are automatically determined.', 'meinturnierplan'); ?></small>
    </p>
    <?php
  }

  /**
   * Update widget settings
   */
  public function update($new_instance, $old_instance) {
    $instance             = array();
    $instance['title']    = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
    $instance['table_id'] = (!empty($new_instance['table_id'])) ? absint($new_instance['table_id']) : '';

    return $instance;
  }

  /**
   * Get styling attributes from post meta
   */
  private function get_styling_attributes_from_meta($table_id) {
    $attributes = array();

    // Define parameter mapping from meta keys to shortcode attribute names
    $param_mapping = array(
      '_mtrn_font_size' => 's-size',
      '_mtrn_header_font_size' => 's-sizeheader',
      '_mtrn_text_color' => 's-color',
      '_mtrn_main_color' => 's-maincolor',
      '_mtrn_table_padding' => 's-padding',
      '_mtrn_inner_padding' => 's-innerpadding',
      '_mtrn_logo_size' => 's-logosize',
      '_mtrn_border_color' => 's-bcolor',
      '_mtrn_bsizeh' => 's-bsizeh',
      '_mtrn_bsizev' => 's-bsizev',
      '_mtrn_bsizeoh' => 's-bsizeoh',
      '_mtrn_bsizeov' => 's-bsizeov',
      '_mtrn_head_bottom_border_color' => 's-bbcolor',
      '_mtrn_bbsize' => 's-bbsize',
    );

    // Get simple color/styling values
    foreach ($param_mapping as $meta_key => $attr_name) {
      $value = get_post_meta($table_id, $meta_key, true);
      if (!empty($value)) {
        $attributes[$attr_name] = $value;
      }
    }

    // Handle color+opacity combinations
    $color_opacity_mapping = array(
      '_mtrn_bg_color' => array('attr' => 's-bgcolor', 'opacity_meta' => '_mtrn_bg_opacity'),
      '_mtrn_even_bg_color' => array('attr' => 's-bgeven', 'opacity_meta' => '_mtrn_even_bg_opacity'),
      '_mtrn_odd_bg_color' => array('attr' => 's-bgodd', 'opacity_meta' => '_mtrn_odd_bg_opacity'),
      '_mtrn_hover_bg_color' => array('attr' => 's-bgover', 'opacity_meta' => '_mtrn_hover_bg_opacity'),
      '_mtrn_head_bg_color' => array('attr' => 's-bghead', 'opacity_meta' => '_mtrn_head_bg_opacity'),
    );

    foreach ($color_opacity_mapping as $color_meta => $config) {
      $color = get_post_meta($table_id, $color_meta, true);
      $opacity = get_post_meta($table_id, $config['opacity_meta'], true);

      if (!empty($color)) {
        $combined_color = MTRN_Admin_Utilities::combine_color_opacity($color, $opacity);
        $attributes[$config['attr']] = $combined_color;
      }
    }

    return $attributes;
  }

  /**
   * Get configuration attributes from post meta
   */
  private function get_config_attributes_from_meta($table_id) {
    $attributes = array();

    // Define boolean parameter mapping
    $boolean_params = array(
      '_mtrn_suppress_wins' => 'sw',
      '_mtrn_suppress_logos' => 'sl',
      '_mtrn_suppress_num_matches' => 'sn',
      '_mtrn_projector_presentation' => 'bm',
      '_mtrn_navigation_for_groups' => 'nav',
    );

    foreach ($boolean_params as $meta_key => $attr_name) {
      $value = get_post_meta($table_id, $meta_key, true);
      if ($value === '1') {
        $attributes[$attr_name] = '1';
      }
    }

    // Responsive layout maps to the s-wrap attribute
    if (get_post_meta($table_id, '_mtrn_responsive', true) === '1') {
      $attributes['s-wrap'] = 'true';
    }

    // Get language setting
    $language = get_post_meta($table_id, '_mtrn_language', true);
    if (!empty($language)) {
      $attributes['lang'] = $language;
    }

    // Get group setting
    $group = get_post_meta($table_id, '_mtrn_group', true);
    if (!empty($group)) {
      $attributes['group'] = $group;
    }

    return $attributes;
  }
}
