<?php
/**
 * Admin AJAX: live preview, tournament groups / participants / options.
 *
 * All action names are unchanged from the per-type handlers this replaces.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

class MTRN_Ajax_Handler {

  /** @var MTRN_Embed_Renderer */
  private $renderer;

  public function __construct(MTRN_Embed_Renderer $renderer) {
    $this->renderer = $renderer;

    foreach (MTRN_Options::types() as $type => $config) {
      $ajax = $config['ajax'];

      add_action('wp_ajax_' . $ajax['preview'], function () use ($type) {
        $this->preview($type);
      });
      add_action('wp_ajax_' . $ajax['groups'], function () {
        $this->groups(false);
      });
      add_action('wp_ajax_' . $ajax['refresh_groups'], function () {
        $this->groups(true);
      });
      if (isset($ajax['teams'])) {
        add_action('wp_ajax_' . $ajax['teams'], function () {
          $this->teams(false);
        });
        add_action('wp_ajax_' . $ajax['refresh_teams'], function () {
          $this->teams(true);
        });
      }
      if (isset($ajax['check_option'])) {
        add_action('wp_ajax_' . $ajax['check_option'], array($this, 'check_option'));
      }
    }
  }

  private function verify_preview_nonce() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mtrn_preview_nonce')) {
      wp_die('Security check failed');
    }
  }

  private function post_value($key) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified by the calling handler
    return isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
  }

  /**
   * Live preview: render the embed for the current form values.
   *
   * Every option is passed explicitly so that the preview never falls back
   * to the values saved on the post.
   */
  public function preview($type) {
    $this->verify_preview_nonce();

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by verify_preview_nonce() above
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $atts = array();

    foreach (MTRN_Options::for_type($type) as $key => $def) {
      $raw = $this->post_value($key);

      switch ($def['kind']) {
        case 'source':
          $atts['id'] = $raw;
          break;
        case 'language':
          $atts['lang'] = $raw !== '' ? $raw : 'en';
          break;
        case 'group':
          if ($raw !== '') {
            $atts['group'] = $raw;
          }
          break;
        case 'participant':
          if ($raw !== '' && $raw !== '-1') {
            $atts['participant'] = $raw;
          }
          break;
        case 'match_number':
          if ($raw !== '') {
            $atts['gamenumbers'] = $raw;
          }
          break;
        case 'flag':
          $atts[$def['attr']] = $raw === '1' ? '1' : '0';
          break;
        case 'wrap':
          $atts[$def['attr']] = $raw === '1' ? 'true' : 'false';
          break;
        case 'style':
        case 'color_opacity':
          $atts[$def['attr']] = $raw !== '' ? $raw : $def['default'];
          break;
        case 'size':
          $atts[$def['attr']] = $raw !== '' ? $raw : $def['admin_default'];
          break;
      }
    }

    wp_send_json_success($this->renderer->render($type, $post_id, $atts));
  }

  /**
   * Tournament groups (cached), optionally bypassing the cache
   */
  public function groups($force_refresh) {
    $this->verify_preview_nonce();

    $tournament_id = $this->post_value('tournament_id');
    $language = $this->post_value('language');
    if (!$force_refresh) {
      $force_refresh = filter_var($this->post_value('force_refresh'), FILTER_VALIDATE_BOOLEAN);
    }

    if ($tournament_id === '') {
      wp_send_json_success(array('groups' => array(), 'hasFinalRound' => false));
      return;
    }

    $data = MTRN_Admin_Utilities::fetch_tournament_groups($tournament_id, $force_refresh, $language !== '' ? $language : 'en');
    if ($force_refresh) {
      $data['refreshed'] = true;
    }
    wp_send_json_success($data);
  }

  /**
   * Tournament participants (cached), optionally bypassing the cache
   */
  public function teams($force_refresh) {
    $this->verify_preview_nonce();

    $tournament_id = $this->post_value('tournament_id');
    $language = $this->post_value('language');
    if (!$force_refresh) {
      $force_refresh = filter_var($this->post_value('force_refresh'), FILTER_VALIDATE_BOOLEAN);
    }

    if ($tournament_id === '') {
      wp_send_json_success(array('teams' => array()));
      return;
    }

    $response = array('teams' => MTRN_Admin_Utilities::fetch_tournament_teams($tournament_id, $force_refresh, $language !== '' ? $language : 'en'));
    if ($force_refresh) {
      $response['refreshed'] = true;
    }
    wp_send_json_success($response);
  }

  /**
   * One field of the tournament JSON (showCourts, finalMatches, ...)
   */
  public function check_option() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mtrn_check_option_nonce')) {
      wp_send_json_error(array('message' => 'Security check failed'));
      return;
    }

    $tournament_id = $this->post_value('tournament_id');
    $option_name = $this->post_value('option_name');
    $language = $this->post_value('language');

    if ($tournament_id === '' || $option_name === '') {
      wp_send_json_error(array('message' => 'Missing required parameters'));
      return;
    }

    wp_send_json_success(array(
      'value' => MTRN_Admin_Utilities::fetch_tournament_option($tournament_id, $option_name, $language !== '' ? $language : 'en'),
      'tournament_id' => $tournament_id,
      'option_name' => $option_name
    ));
  }
}
