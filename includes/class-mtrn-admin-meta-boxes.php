<?php
/**
 * Admin meta boxes for one embed type: settings form with live preview, and
 * the shortcode generator. Instantiated once for tables and once for match
 * lists; everything type-specific comes from MTRN_Options.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class MTRN_Admin_Meta_Boxes {

  /** @var string */
  private $type;

  /** @var array */
  private $config;

  /** @var MTRN_Embed_Renderer */
  private $renderer;

  public function __construct($type, MTRN_Embed_Renderer $renderer) {
    $this->type = $type;
    $this->config = MTRN_Options::type($type);
    $this->renderer = $renderer;

    add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
    add_action('save_post', array($this, 'save_meta_boxes'));
  }

  /**
   * Register the settings box and the shortcode box for the post type
   */
  public function add_meta_boxes() {
    add_meta_box(
      $this->config['meta_box_id'],
      $this->config['meta_box_title'],
      array($this, 'meta_box_callback'),
      $this->config['post_type'],
      'normal',
      'high'
    );

    add_meta_box(
      $this->config['shortcode_box_id'],
      __('Shortcode Generator', 'meinturnierplan'),
      array($this, 'shortcode_meta_box_callback'),
      $this->config['post_type'],
      'side',
      'high'
    );
  }

  /**
   * Settings form and live preview
   */
  public function meta_box_callback($post) {
    wp_nonce_field($this->config['nonce_action'], $this->config['nonce_name']);

    $meta_values = $this->get_meta_values($post->ID);

    echo '<div class="mtrn-admin-two-column-layout">';

    echo '<div class="mtrn-admin-column mtrn-admin-column-left">';
    echo '<h3>' . esc_html($this->config['settings_title']) . '</h3>';
    $this->render_settings_form($meta_values);
    echo '</div>';

    echo '<div class="mtrn-admin-column mtrn-admin-column-right">';
    echo '<h3>' . esc_html__('Preview', 'meinturnierplan') . '</h3>';
    echo '<div id="mtrn-preview">';
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the renderer
    echo $this->renderer->render($this->type, $post->ID, $this->build_preview_attributes($meta_values));
    echo '</div>';
    echo '</div>';

    echo '</div>';

    $this->enqueue_preview_script($post->ID);
  }

  /**
   * Saved values with the admin defaults applied. A saved '0' is kept.
   *
   * @return array<string, string>
   */
  public function get_meta_values($post_id) {
    $meta_values = array();
    foreach (MTRN_Options::meta_defaults($this->type) as $key => $default) {
      $value = get_post_meta($post_id, MTRN_Options::META_PREFIX . $key, true);
      $meta_values[$key] = (!empty($value) || $value === '0') ? $value : $default;
    }
    return $meta_values;
  }

  /**
   * The settings form, generated from the option registry
   */
  private function render_settings_form($meta_values) {
    $options = MTRN_Options::for_type($this->type);
    $tournament_id = $meta_values['tournament_id'];
    $language = $meta_values['language'];

    echo '<table class="form-table">';

    foreach (MTRN_Options::groups() as $group => $header) {
      $in_group = array_filter($options, static function ($def) use ($group) {
        return $def['group'] === $group;
      });
      if (empty($in_group)) {
        continue;
      }

      MTRN_Admin_Utilities::render_group_header($header);

      foreach ($in_group as $key => $def) {
        $field = MTRN_Options::FIELD_PREFIX . $key;
        $label = isset($def['label']) ? $def['label'] : '';
        $description = MTRN_Options::description($def, $this->type);
        $value = isset($meta_values[$key]) ? $meta_values[$key] : '';

        switch ($def['kind']) {
          case 'source':
          case 'match_number':
            MTRN_Admin_Utilities::render_text_field($field, $label, $value, $description);
            break;

          case 'language':
            MTRN_Admin_Utilities::render_select_field($field, $label, $value, MTRN_Admin_Utilities::get_language_options(), $description);
            break;

          case 'group':
            MTRN_Admin_Utilities::render_conditional_group_field($meta_values, MTRN_Options::FIELD_PREFIX, $this->type === 'matches' ? 'matches' : 'tables');
            break;

          case 'participant':
            MTRN_Admin_Utilities::render_conditional_participant_field($meta_values, MTRN_Options::FIELD_PREFIX);
            break;

          case 'flag':
            if (!empty($def['condition'])) {
              MTRN_Admin_Utilities::render_conditional_checkbox_field($field, $label, $value, $description, $tournament_id, $def['condition'], $language);
            } else {
              MTRN_Admin_Utilities::render_checkbox_field($field, $label, $value, $description);
            }
            break;

          case 'wrap':
            MTRN_Admin_Utilities::render_checkbox_field($field, $label, $value, $description);
            break;

          case 'style':
            if ($def['type'] === 'color') {
              MTRN_Admin_Utilities::render_color_field($field, $label, $value, $description);
            } else {
              MTRN_Admin_Utilities::render_number_field($field, $label, $value, $description, $def['min'], $def['max']);
            }
            break;

          case 'color_opacity':
            MTRN_Admin_Utilities::render_color_opacity_field($field, MTRN_Options::FIELD_PREFIX . $def['opacity_key'], $label, $value, $meta_values[$def['opacity_key']], $description);
            break;
        }
      }
    }

    echo '</table>';

    // Iframe size, written back by the resize script when the preview reports its size
    echo '<input type="hidden" id="mtrn_width" name="mtrn_width" value="' . esc_attr($meta_values['width']) . '" />';
    echo '<input type="hidden" id="mtrn_height" name="mtrn_height" value="' . esc_attr($meta_values['height']) . '" />';
  }

  /**
   * Whether a conditional option applies to the tournament (e.g. a court
   * column only exists when the tournament uses courts).
   */
  private function condition_met($condition, $tournament_id, $language) {
    if ($tournament_id === '') {
      return false;
    }
    $value = MTRN_Admin_Utilities::fetch_tournament_option($tournament_id, $condition, $language);
    return $value === true || is_array($value);
  }

  /**
   * Render attributes for the preview, from the admin values
   *
   * @return array<string, string>
   */
  private function build_preview_attributes($meta_values) {
    $atts = array();
    $tournament_id = $meta_values['tournament_id'];

    foreach (MTRN_Options::for_type($this->type) as $key => $def) {
      $value = isset($meta_values[$key]) ? (string) $meta_values[$key] : '';

      switch ($def['kind']) {
        case 'source':
          $atts['id'] = $value;
          break;
        case 'language':
          $atts['lang'] = $value;
          break;
        case 'group':
          if ($value !== '') {
            $atts['group'] = $value;
          }
          break;
        case 'participant':
          if ($value !== '' && $value !== '-1') {
            $atts['participant'] = $value;
          }
          break;
        case 'match_number':
          if ($value !== '') {
            $atts['gamenumbers'] = $value;
          }
          break;
        case 'flag':
          if ($value === '1' && (empty($def['condition']) || $this->condition_met($def['condition'], $tournament_id, $meta_values['language']))) {
            $atts[$def['attr']] = '1';
          }
          break;
        case 'wrap':
          if ($value === '1') {
            $atts['s-wrap'] = 'true';
          }
          break;
        case 'style':
        case 'size':
          $atts[$def['attr']] = $value;
          break;
        case 'color_opacity':
          $atts[$def['attr']] = MTRN_Options::combine_color_opacity($value, $meta_values[$def['opacity_key']]);
          break;
      }
    }

    return $atts;
  }

  /**
   * The shortcode for the saved values. The JavaScript updater produces the
   * same string from the form fields.
   */
  public function generate_shortcode($post_id, $meta_values) {
    $parts = array(
      '[' . $this->config['shortcode'],
      'id="' . esc_attr($meta_values['tournament_id']) . '"',
      'post_id="' . (int) $post_id . '"',
      'lang="' . esc_attr($meta_values['language']) . '"',
    );

    foreach (MTRN_Options::for_type($this->type) as $key => $def) {
      $value = isset($meta_values[$key]) ? (string) $meta_values[$key] : '';

      switch ($def['kind']) {
        case 'group':
        case 'match_number':
          if ($value !== '') {
            $parts[] = $def['attr'] . '="' . esc_attr($value) . '"';
          }
          break;
        case 'participant':
          if ($value !== '' && $value !== '-1') {
            $parts[] = $def['attr'] . '="' . esc_attr($value) . '"';
          }
          break;
        case 'flag':
          if ($value === '1') {
            $parts[] = $def['attr'] . '="1"';
          }
          break;
        case 'wrap':
          if ($value === '1') {
            $parts[] = $def['attr'] . '="true"';
          }
          break;
        case 'style':
          $parts[] = $def['attr'] . '="' . esc_attr($value) . '"';
          break;
        case 'color_opacity':
          $parts[] = $def['attr'] . '="' . esc_attr(MTRN_Options::combine_color_opacity($value, $meta_values[$def['opacity_key']])) . '"';
          break;
      }
    }

    $parts[] = 'width="' . esc_attr($meta_values['width']) . '"';
    $parts[] = 'height="' . esc_attr($meta_values['height']) . '"';

    return implode(' ', $parts) . ']';
  }

  /**
   * Shortcode generator box
   */
  public function shortcode_meta_box_callback($post) {
    $meta_values = $this->get_meta_values($post->ID);
    $shortcode = $this->generate_shortcode($post->ID, $meta_values);

    $this->enqueue_shortcode_script($post->ID, $meta_values);

    MTRN_Admin_Utilities::render_shortcode_generator($shortcode, $meta_values['tournament_id'], array(
      'shortcode_updater_callback' => 'updateShortcode'
    ));
  }

  /**
   * Configuration localized for the admin scripts
   *
   * @return array{metaBox: array, preview: array}
   */
  public function localized_configs($post_id, $meta_values = null) {
    if ($meta_values === null) {
      $meta_values = $this->get_meta_values($post_id);
    }
    $ajax = $this->config['ajax'];

    return array(
      'metaBox' => array(
        'type'          => $this->type,
        'shortcode'     => $this->config['shortcode'],
        'idPrefix'      => $this->config['id_prefix'],
        'postId'        => intval($post_id),
        'defaultWidth'  => (string) $meta_values['width'],
        'defaultHeight' => (string) $meta_values['height'],
        'options'       => MTRN_Options::js_config($this->type),
      ),
      'preview' => array(
        'type'              => $this->type,
        'postId'            => intval($post_id),
        'previewAction'     => $ajax['preview'],
        'previewNonce'      => wp_create_nonce('mtrn_preview_nonce'),
        'checkOptionAction' => isset($ajax['check_option']) ? $ajax['check_option'] : '',
        'checkOptionNonce'  => wp_create_nonce('mtrn_check_option_nonce'),
        'options'           => MTRN_Options::js_config($this->type),
      ),
    );
  }

  private function enqueue_preview_script($post_id) {
    $ajax = $this->config['ajax'];
    $utilities = array('ajax_actions' => array($ajax['groups'], $ajax['refresh_groups']));
    if (isset($ajax['teams'])) {
      $utilities['ajax_actions_teams'] = array($ajax['teams'], $ajax['refresh_teams']);
    }
    MTRN_Admin_Utilities::render_admin_javascript_utilities($utilities);

    wp_enqueue_script(
      'mtrn-admin-preview',
      MTRN_PLUGIN_URL . 'assets/js/admin-preview.js',
      array('jquery', 'mtrn-admin-utilities'),
      MTRN_PLUGIN_VERSION,
      true
    );
    $configs = $this->localized_configs($post_id);
    wp_localize_script('mtrn-admin-preview', 'mtrnPreviewConfig', $configs['preview']);
  }

  private function enqueue_shortcode_script($post_id, $meta_values) {
    wp_enqueue_script(
      'mtrn-admin-meta-boxes',
      MTRN_PLUGIN_URL . 'assets/js/admin-meta-boxes.js',
      array('jquery'),
      MTRN_PLUGIN_VERSION,
      true
    );
    $configs = $this->localized_configs($post_id, $meta_values);
    wp_localize_script('mtrn-admin-meta-boxes', 'mtrnMetaBoxConfig', $configs['metaBox']);
  }

  /**
   * Save the settings
   */
  public function save_meta_boxes($post_id) {
    $nonce_name = $this->config['nonce_name'];
    if (!isset($_POST[$nonce_name]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$nonce_name])), $this->config['nonce_action'])) {
      return;
    }

    if (isset($_POST['post_type']) && $this->config['post_type'] === $_POST['post_type']) {
      if (!current_user_can('edit_page', $post_id)) {
        return;
      }
    } elseif (!current_user_can('edit_post', $post_id)) {
      return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    foreach (MTRN_Options::save_keys($this->type) as $key) {
      $field = MTRN_Options::FIELD_PREFIX . $key;
      $meta_key = MTRN_Options::META_PREFIX . $key;

      if (MTRN_Options::is_checkbox($key)) {
        // An unchecked checkbox is absent from the request
        update_post_meta($post_id, $meta_key, isset($_POST[$field]) ? '1' : '0');
      } elseif (isset($_POST[$field])) {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised per option type by MTRN_Options::sanitize()
        update_post_meta($post_id, $meta_key, MTRN_Options::sanitize($key, wp_unslash($_POST[$field])));
      }
    }
  }
}
