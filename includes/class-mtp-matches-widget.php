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
            __('Tournament Matches', 'meinturnierplan-wp'),
            array(
                'description' => __('Display tournament matches.', 'meinturnierplan-wp'),
                'classname' => 'mtp-matches-widget'
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

        if (!empty($instance['matches_id'])) {
            // Use the same approach as the Gutenberg block - get settings from post meta
            $matches_id = $instance['matches_id'];

            // Get saved width and height from post meta
            $width = get_post_meta($matches_id, '_mtp_matches_width', true);
            $height = get_post_meta($matches_id, '_mtp_matches_height', true);

            // Prepare shortcode attributes
            $shortcode_atts = array('post_id' => $matches_id);

            // Add width and height if they exist
            if (!empty($width)) {
                $shortcode_atts['width'] = $width;
            }
            if (!empty($height)) {
                $shortcode_atts['height'] = $height;
            }

            // Use the existing shortcode functionality
            $mtp_plugin = MTP_Plugin::instance();
            $shortcode = new MTP_Matches_Shortcode($mtp_plugin->matches_renderer);
            echo $shortcode->shortcode_callback($shortcode_atts);
        } else {
            echo '<div class="mtp-widget-placeholder">' . __('Please select Tournament Matches.', 'meinturnierplan-wp') . '</div>';
        }

        echo $args['after_widget'];
    }

    /**
     * Widget form in admin
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Tournament Matches', 'meinturnierplan-wp');
        $matches_id = !empty($instance['matches_id']) ? $instance['matches_id'] : '';

        // Get all matches
        $matches = get_posts(array(
            'post_type' => 'mtp_matches',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'meinturnierplan-wp'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('matches_id')); ?>"><?php _e('Select Tournament Matches:', 'meinturnierplan-wp'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('matches_id')); ?>" name="<?php echo esc_attr($this->get_field_name('matches_id')); ?>">
                <option value=""><?php _e('-- Select Matches --', 'meinturnierplan-wp'); ?></option>
                <?php foreach ($matches as $match): ?>
                    <option value="<?php echo esc_attr($match->ID); ?>" <?php selected($matches_id, $match->ID); ?>>
                        <?php echo esc_html($match->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <small><?php _e('The widget will use the width, height, and all styling settings configured for the selected Tournament Matches.', 'meinturnierplan-wp'); ?></small>
        </p>
        <?php
    }

    /**
     * Update widget settings
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['matches_id'] = (!empty($new_instance['matches_id'])) ? (int) $new_instance['matches_id'] : 0;

        return $instance;
    }
}
