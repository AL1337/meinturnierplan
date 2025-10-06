<?php
/**
 * Widget class for Tournament Tables
 */

class MTP_Table_Widget extends WP_Widget {

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct(
      'mtp_table_widget',
      __('Tournament Table', 'meinturnierplan'),
      array(
        'description' => __('Display a tournament table.', 'meinturnierplan'),
        'classname'   => 'mtp-table-widget'
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

      // Get saved width and height from post meta
      $width = get_post_meta($table_id, '_mtp_width', true);
      $height = get_post_meta($table_id, '_mtp_height', true);

      // Prepare shortcode attributes
      $shortcode_atts = array('post_id' => $table_id);

      // Add width and height if they exist
      if (!empty($width)) {
        $shortcode_atts['width'] = $width;
      }
      if (!empty($height)) {
        $shortcode_atts['height'] = $height;
      }

      // Use the existing shortcode functionality
      $mtp_plugin = MTP_Plugin::instance();
      $shortcode = new MTP_Shortcode($mtp_plugin->table_renderer);
      echo $shortcode->shortcode_callback($shortcode_atts);
    } else {
      echo '<div class="mtp-widget-placeholder">' . __('Please select a Tournament Table.', 'meinturnierplan') . '</div>';
    }

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
      'post_type'      => 'mtp_table',
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
      <label for="<?php echo esc_attr($this->get_field_id('table_id')); ?>"><?php _e('Select Tournament Table:', 'meinturnierplan'); ?></label>
      <select class="widefat" id="<?php echo esc_attr($this->get_field_id('table_id')); ?>" name="<?php echo esc_attr($this->get_field_name('table_id')); ?>">
        <option value=""><?php _e('-- Select Table --', 'meinturnierplan'); ?></option>
        <?php foreach ($tables as $table): ?>
          <option value="<?php echo esc_attr($table->ID); ?>" <?php selected($table_id, $table->ID); ?>>
            <?php echo esc_html($table->post_title); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>

    <p>
      <small><?php _e('The widget will use the width, height, and all styling settings configured for the selected Tournament Table.', 'meinturnierplan'); ?></small>
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
}
