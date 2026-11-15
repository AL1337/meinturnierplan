<?php
/**
 * Widget: shows a saved tournament table or match list.
 *
 * One class, registered once per embed type with its own widget id
 * (mtrn_table_widget / mtrn_matches_widget), so existing widget settings
 * keep working.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class MTRN_Embed_Widget extends WP_Widget {

  /** @var string */
  private $type;

  /** @var MTRN_Embed_Renderer */
  private $renderer;

  /**
   * @param string                   $type     'table' or 'matches'.
   * @param MTRN_Embed_Renderer|null $renderer Shared renderer.
   */
  public function __construct($type = 'table', $renderer = null) {
    $this->type = array_key_exists($type, MTRN_Options::types()) ? $type : 'table';
    $this->renderer = $renderer instanceof MTRN_Embed_Renderer ? $renderer : new MTRN_Embed_Renderer();
    $config = MTRN_Options::type($this->type);

    parent::__construct(
      $config['widget_id'],
      $config['widget_name'],
      array(
        'description' => $config['widget_description'],
        'classname'   => $config['widget_classname']
      )
    );
  }

  /**
   * Widget output
   */
  public function widget($args, $instance) {
    $config = MTRN_Options::type($this->type);

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['before_widget'] is already escaped by WordPress core
    echo $args['before_widget'];

    if (!empty($instance['title'])) {
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['before_title'] and $args['after_title'] are already escaped by WordPress core
      echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
    }

    if (!empty($instance['table_id'])) {
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the renderer
      echo $this->renderer->render($this->type, (int) $instance['table_id'], array());
    } else {
      echo '<div class="mtrn-widget-placeholder">' . esc_html($config['select_placeholder']) . '</div>';
    }

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['after_widget'] is already escaped by WordPress core
    echo $args['after_widget'];
  }

  /**
   * Widget form in admin
   */
  public function form($instance) {
    $config = MTRN_Options::type($this->type);
    $title = !empty($instance['title']) ? $instance['title'] : $config['widget_name'];
    $table_id = !empty($instance['table_id']) ? $instance['table_id'] : '';

    $posts = get_posts(array(
      'post_type'      => $config['post_type'],
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
      <label for="<?php echo esc_attr($this->get_field_id('table_id')); ?>"><?php echo esc_html($config['widget_select_label']); ?></label>
      <select class="widefat" id="<?php echo esc_attr($this->get_field_id('table_id')); ?>" name="<?php echo esc_attr($this->get_field_name('table_id')); ?>">
        <option value=""><?php echo esc_html($config['widget_select_empty']); ?></option>
        <?php foreach ($posts as $post) : ?>
          <option value="<?php echo esc_attr($post->ID); ?>" <?php selected($table_id, $post->ID); ?>>
            <?php echo esc_html($post->post_title); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>

    <p>
      <small><?php echo esc_html($config['widget_note']); ?></small>
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
