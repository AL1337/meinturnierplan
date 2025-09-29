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
            $font_size = !empty($instance['font_size']) ? $instance['font_size'] : '9';
            $header_font_size = !empty($instance['header_font_size']) ? $instance['header_font_size'] : '10';
            $table_padding = !empty($instance['table_padding']) ? $instance['table_padding'] : '2';
            $inner_padding = !empty($instance['inner_padding']) ? $instance['inner_padding'] : '5';
            $text_color = !empty($instance['text_color']) ? $instance['text_color'] : '000000';
            $main_color = !empty($instance['main_color']) ? $instance['main_color'] : '';
            $attributes = array(
                'width' => $width, 
                's-size' => $font_size,
                's-sizeheader' => $header_font_size,
                's-padding' => $table_padding,
                's-innerpadding' => $inner_padding,
                's-color' => $text_color,
                's-maincolor' => $main_color
            );
            
            // Get the main plugin instance to render table
            $mtp_plugin = new MeinTurnierplanWP();
            echo $mtp_plugin->render_table_html($instance['table_id'], $attributes);
        } else {
            // Show empty table when no table selected
            $width = !empty($instance['width']) ? $instance['width'] : '';
            $font_size = !empty($instance['font_size']) ? $instance['font_size'] : '9';
            $header_font_size = !empty($instance['header_font_size']) ? $instance['header_font_size'] : '10';
            $table_padding = !empty($instance['table_padding']) ? $instance['table_padding'] : '2';
            $inner_padding = !empty($instance['inner_padding']) ? $instance['inner_padding'] : '5';
            $text_color = !empty($instance['text_color']) ? $instance['text_color'] : '000000';
            $main_color = !empty($instance['main_color']) ? $instance['main_color'] : '';
            $attributes = array(
                'width' => $width, 
                's-size' => $font_size,
                's-sizeheader' => $header_font_size,
                's-padding' => $table_padding,
                's-innerpadding' => $inner_padding,
                's-color' => $text_color,
                's-maincolor' => $main_color
            );
            
            $mtp_plugin = new MeinTurnierplanWP();
            echo $mtp_plugin->render_table_html(null, $attributes);
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
        $font_size = !empty($instance['font_size']) ? $instance['font_size'] : '';
        $header_font_size = !empty($instance['header_font_size']) ? $instance['header_font_size'] : '';
        $table_padding = !empty($instance['table_padding']) ? $instance['table_padding'] : '';
        $inner_padding = !empty($instance['inner_padding']) ? $instance['inner_padding'] : '';
        $text_color = !empty($instance['text_color']) ? $instance['text_color'] : '';
        $main_color = !empty($instance['main_color']) ? $instance['main_color'] : '';
        
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
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('font_size')); ?>"><?php _e('Content Font Size (pt):', 'meinturnierplan-wp'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('font_size')); ?>" name="<?php echo esc_attr($this->get_field_name('font_size')); ?>" type="number" value="<?php echo esc_attr($font_size); ?>" min="6" max="24" step="1">
            <small><?php _e('Leave empty to use table default font size. 9pt is the default value.', 'meinturnierplan-wp'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('header_font_size')); ?>"><?php _e('Header Font Size (pt):', 'meinturnierplan-wp'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('header_font_size')); ?>" name="<?php echo esc_attr($this->get_field_name('header_font_size')); ?>" type="number" value="<?php echo esc_attr($header_font_size); ?>" min="6" max="24" step="1">
            <small><?php _e('Leave empty to use table default header font size. 10pt is the default value.', 'meinturnierplan-wp'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('table_padding')); ?>"><?php _e('Table Padding (px):', 'meinturnierplan-wp'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('table_padding')); ?>" name="<?php echo esc_attr($this->get_field_name('table_padding')); ?>" type="number" value="<?php echo esc_attr($table_padding); ?>" min="0" max="50" step="1">
            <small><?php _e('Leave empty to use table default padding. 2px is the default value.', 'meinturnierplan-wp'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('inner_padding')); ?>"><?php _e('Inner Padding (px):', 'meinturnierplan-wp'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('inner_padding')); ?>" name="<?php echo esc_attr($this->get_field_name('inner_padding')); ?>" type="number" value="<?php echo esc_attr($inner_padding); ?>" min="0" max="20" step="1">
            <small><?php _e('Leave empty to use table default inner padding. 5px is the default value.', 'meinturnierplan-wp'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('text_color')); ?>"><?php _e('Text Color:', 'meinturnierplan-wp'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('text_color')); ?>" name="<?php echo esc_attr($this->get_field_name('text_color')); ?>" type="text" value="<?php echo esc_attr($text_color); ?>" placeholder="000000">
            <small><?php _e('Leave empty to use table default text color. Enter hex color without # (e.g., 000000 for black).', 'meinturnierplan-wp'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('main_color')); ?>"><?php _e('Main Color:', 'meinturnierplan-wp'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('main_color')); ?>" name="<?php echo esc_attr($this->get_field_name('main_color')); ?>" type="text" value="<?php echo esc_attr($main_color); ?>" placeholder="000000">
            <small><?php _e('Leave empty to use table default main color. Enter hex color without # (e.g., 000000 for black).', 'meinturnierplan-wp'); ?></small>
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
        $instance['font_size'] = (!empty($new_instance['font_size'])) ? sanitize_text_field($new_instance['font_size']) : '';
        $instance['header_font_size'] = (!empty($new_instance['header_font_size'])) ? sanitize_text_field($new_instance['header_font_size']) : '';
        $instance['table_padding'] = (!empty($new_instance['table_padding'])) ? sanitize_text_field($new_instance['table_padding']) : '';
        $instance['inner_padding'] = (!empty($new_instance['inner_padding'])) ? sanitize_text_field($new_instance['inner_padding']) : '';
        $instance['text_color'] = (!empty($new_instance['text_color'])) ? sanitize_text_field($new_instance['text_color']) : '';
        $instance['main_color'] = (!empty($new_instance['main_color'])) ? sanitize_text_field($new_instance['main_color']) : '';
        
        return $instance;
    }
}
