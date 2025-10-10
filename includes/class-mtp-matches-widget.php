<?php
/**
 * Widget class for Matches
 */

class MTP_Matches_Widget extends WP_Widget {

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct(
      'mtp_matches_widget',
      __('Matches Table', 'meinturnierplan'),
      array(
        'description' => __('Display a matches table.', 'meinturnierplan'),
        'classname'   => 'mtp-matches-widget'
      )
    );
  }

  /**
   * Widget output
   */
  public function widget($args, $instance) {
    echo $args['before_widget'];

    if (!empty($instance['title'])) {
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
      $mtp_plugin = MTP_Plugin::instance();
      $shortcode = new MTP_Matches_Shortcode($mtp_plugin->matches_renderer);
      echo $shortcode->shortcode_callback($shortcode_atts);
    } else {
      echo '<div class="mtp-widget-placeholder">' . __('Please select a Matches Table.', 'meinturnierplan') . '</div>';
    }

    echo $args['after_widget'];
  }

  /**
   * Widget form in admin
   */
  public function form($instance) {
    $title = !empty($instance['title']) ? $instance['title'] : __('Matches Table', 'meinturnierplan');
    $table_id = !empty($instance['table_id']) ? $instance['table_id'] : '';

    // Get all matches tables
    $tables = get_posts(array(
      'post_type'      => 'mtp_match_list',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'title',
      'order'          => 'ASC'
    ));
    ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'meinturnierplan'); ?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
    </p>

    <p>
      <label for="<?php echo esc_attr($this->get_field_id('table_id')); ?>"><?php _e('Select Matches Table:', 'meinturnierplan'); ?></label>
      <select class="widefat" id="<?php echo esc_attr($this->get_field_id('table_id')); ?>" name="<?php echo esc_attr($this->get_field_name('table_id')); ?>">
        <option value=""><?php _e('-- Select Matches --', 'meinturnierplan'); ?></option>
        <?php foreach ($tables as $table): ?>
          <option value="<?php echo esc_attr($table->ID); ?>" <?php selected($table_id, $table->ID); ?>>
            <?php echo esc_html($table->post_title); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>

    <p>
      <small><?php _e('The widget will use all styling settings configured for the selected Matches Table. Width and height are automatically determined.', 'meinturnierplan'); ?></small>
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
      '_mtp_font_size' => 's-size',
      '_mtp_header_font_size' => 's-sizeheader',
      '_mtp_text_color' => 's-color',
      '_mtp_main_color' => 's-maincolor',
      '_mtp_table_padding' => 's-padding',
      '_mtp_inner_padding' => 's-innerpadding',
      '_mtp_border_color' => 's-bcolor',
      '_mtp_bsizeh' => 's-bsizeh',
      '_mtp_bsizev' => 's-bsizev',
      '_mtp_bsizeoh' => 's-bsizeoh',
      '_mtp_bsizeov' => 's-bsizeov',
      '_mtp_head_bottom_border_color' => 's-bbcolor',
      '_mtp_bbsize' => 's-bbsize',
      '_mtp_ehrsize' => 's-ehrsize',
      '_mtp_ehrtop' => 's-ehrtop',
      '_mtp_ehrbottom' => 's-ehrbottom',
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
      '_mtp_bg_color' => array('attr' => 's-bgcolor', 'opacity_meta' => '_mtp_bg_opacity'),
      '_mtp_even_bg_color' => array('attr' => 's-bgeven', 'opacity_meta' => '_mtp_even_bg_opacity'),
      '_mtp_odd_bg_color' => array('attr' => 's-bgodd', 'opacity_meta' => '_mtp_odd_bg_opacity'),
      '_mtp_hover_bg_color' => array('attr' => 's-bgover', 'opacity_meta' => '_mtp_hover_bg_opacity'),
      '_mtp_head_bg_color' => array('attr' => 's-bghead', 'opacity_meta' => '_mtp_head_bg_opacity'),
    );

    foreach ($color_opacity_mapping as $color_meta => $config) {
      $color = get_post_meta($table_id, $color_meta, true);
      $opacity = get_post_meta($table_id, $config['opacity_meta'], true);

      if (!empty($color)) {
        $combined_color = MTP_Admin_Utilities::combine_color_opacity($color, $opacity);
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
      '_mtp_projector_presentation' => 'bm',
      '_mtp_si' => 'si',
      '_mtp_sf' => 'sf',
      '_mtp_st' => 'st',
      '_mtp_sg' => 'sg',
      '_mtp_se' => 'se',
      '_mtp_sp' => 'sp',
      '_mtp_sh' => 'sh',
    );

    foreach ($boolean_params as $meta_key => $attr_name) {
      $value = get_post_meta($table_id, $meta_key, true);
      if ($value === '1') {
        $attributes[$attr_name] = '1';
      }
    }

    // Get language setting
    $language = get_post_meta($table_id, '_mtp_language', true);
    if (!empty($language)) {
      $attributes['lang'] = $language;
    }

    // Get group setting
    $group = get_post_meta($table_id, '_mtp_group', true);
    if (!empty($group)) {
      $attributes['group'] = $group;
    }

    return $attributes;
  }
}
