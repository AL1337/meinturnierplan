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
            __('Tournament Table', 'meinturnierplan-wp'),
            array(
                'description' => __('Display a tournament table.', 'meinturnierplan-wp'),
                'classname' => 'mtp-table-widget'
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
            $width = !empty($instance['width']) ? $instance['width'] : '';
            $attributes = array('width' => $width);
            
            // Get the main plugin instance to render table
            $mtp_plugin = new MeinTurnierplanWP();
            echo $mtp_plugin->render_table_html($instance['table_id'], $attributes);
        } else {
            echo '<p>' . __('Please select a tournament table.', 'meinturnierplan-wp') . '</p>';
        }
        
        echo $args['after_widget'];
    }
    
    /**
     * Widget form in admin
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Tournament Table', 'meinturnierplan-wp');
        $table_id = !empty($instance['table_id']) ? $instance['table_id'] : '';
        $width = !empty($instance['width']) ? $instance['width'] : '';
        
        // Get all tournament tables
        $tables = get_posts(array(
            'post_type' => 'mtp_table',
            'post_status' => 'publish',
            'numberposts' => -1
        ));
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'meinturnierplan-wp'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('table_id')); ?>"><?php _e('Select Tournament Table:', 'meinturnierplan-wp'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('table_id')); ?>" name="<?php echo esc_attr($this->get_field_name('table_id')); ?>">
                <option value=""><?php _e('-- Select Table --', 'meinturnierplan-wp'); ?></option>
                <?php foreach ($tables as $table): ?>
                    <option value="<?php echo esc_attr($table->ID); ?>" <?php selected($table_id, $table->ID); ?>>
                        <?php echo esc_html($table->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('width')); ?>"><?php _e('Custom Width (px):', 'meinturnierplan-wp'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('width')); ?>" name="<?php echo esc_attr($this->get_field_name('width')); ?>" type="number" value="<?php echo esc_attr($width); ?>" min="100" max="2000" step="1">
            <small><?php _e('Leave empty to use table default width.', 'meinturnierplan-wp'); ?></small>
        </p>
        <?php
    }
    
    /**
     * Update widget settings
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['table_id'] = (!empty($new_instance['table_id'])) ? absint($new_instance['table_id']) : '';
        $instance['width'] = (!empty($new_instance['width'])) ? sanitize_text_field($new_instance['width']) : '';
        
        return $instance;
    }
}
