<?php
/**
 * Nonce Helper Class
 * Centralizes nonce creation and verification
 *
 * @package MeinTurnierplan
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * MTP_Nonce_Helper Class
 */
class MTP_Nonce_Helper {

  /**
   * Nonce action names
   */
  const ACTION_PREVIEW_TABLE = 'mtp_preview_table';
  const ACTION_PREVIEW_MATCHES = 'mtp_preview_matches';
  const ACTION_GET_GROUPS = 'mtp_get_groups';
  const ACTION_REFRESH_GROUPS = 'mtp_refresh_groups';
  const ACTION_GET_MATCHES_GROUPS = 'mtp_get_matches_groups';
  const ACTION_REFRESH_MATCHES_GROUPS = 'mtp_refresh_matches_groups';
  const ACTION_GET_MATCHES_TEAMS = 'mtp_get_matches_teams';
  const ACTION_REFRESH_MATCHES_TEAMS = 'mtp_refresh_matches_teams';
  const ACTION_CHECK_TOURNAMENT_OPTION = 'mtp_check_tournament_option';
  const ACTION_SAVE_META_BOX = 'mtp_save_meta_box';
  const ACTION_SAVE_MATCHES_META_BOX = 'mtp_save_matches_meta_box';
  const ACTION_SAVE_TABLE_META_BOX = 'mtp_save_table_meta_box';
  const ACTION_DELETE_POST = 'mtp_delete_post';
  const ACTION_ADMIN_SETTINGS = 'mtp_admin_settings';

  /**
   * Create nonce
   *
   * @param string $action Nonce action name
   * @return string Nonce value
   */
  public static function create($action) {
    return wp_create_nonce($action);
  }

  /**
   * Verify nonce
   *
   * @param string $nonce Nonce value to verify
   * @param string $action Nonce action name
   * @return bool True if valid, false otherwise
   */
  public static function verify($nonce, $action) {
    return wp_verify_nonce($nonce, $action) !== false;
  }

  /**
   * Verify nonce from request and die if invalid
   *
   * @param string $action Nonce action name
   * @param string $nonce_key Request parameter key (default: '_wpnonce')
   * @param string $query_arg Query string argument name (default: same as $nonce_key)
   */
  public static function verify_or_die($action, $nonce_key = '_wpnonce', $query_arg = '') {
    if (empty($query_arg)) {
      $query_arg = $nonce_key;
    }

    // Check both POST and GET
    $nonce = '';
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- This IS the nonce verification function
    if (isset($_POST[$nonce_key])) {
      // phpcs:ignore WordPress.Security.NonceVerification.Missing -- This IS the nonce verification function
      $nonce = sanitize_text_field(wp_unslash($_POST[$nonce_key]));
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This IS the nonce verification function
    } elseif (isset($_GET[$nonce_key])) {
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This IS the nonce verification function
      $nonce = sanitize_text_field(wp_unslash($_GET[$nonce_key]));
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This IS the nonce verification function
    } elseif (isset($_REQUEST[$nonce_key])) {
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This IS the nonce verification function
      $nonce = sanitize_text_field(wp_unslash($_REQUEST[$nonce_key]));
    }

    if (!self::verify($nonce, $action)) {
      if (wp_doing_ajax()) {
        wp_send_json_error(
          array(
            'message' => __('Security check failed. Please refresh the page and try again.', 'meinturnierplan'),
          ),
          403
        );
      } else {
        wp_die(
          esc_html__('Security check failed. Please refresh the page and try again.', 'meinturnierplan'),
          esc_html__('Security Error', 'meinturnierplan'),
          array('response' => 403)
        );
      }
    }
  }

  /**
   * Verify nonce and capability together
   *
   * @param string $action Nonce action name
   * @param string $capability Required capability
   * @param string $nonce_key Request parameter key (default: '_wpnonce')
   */
  public static function verify_capability_or_die($action, $capability, $nonce_key = '_wpnonce') {
    // First verify nonce
    self::verify_or_die($action, $nonce_key);

    // Then check capability
    if (!current_user_can($capability)) {
      if (wp_doing_ajax()) {
        wp_send_json_error(
          array(
            'message' => __('You do not have permission to perform this action.', 'meinturnierplan'),
          ),
          403
        );
      } else {
        wp_die(
          esc_html__('You do not have permission to perform this action.', 'meinturnierplan'),
          esc_html__('Permission Error', 'meinturnierplan'),
          array('response' => 403)
        );
      }
    }
  }

  /**
   * Get nonce field HTML
   *
   * @param string $action Nonce action name
   * @param string $name Field name (default: '_wpnonce')
   * @param bool   $referer Whether to add referer field
   * @param bool   $echo Whether to echo or return
   * @return string Nonce field HTML
   */
  public static function field($action, $name = '_wpnonce', $referer = true, $echo = true) {
    return wp_nonce_field($action, $name, $referer, $echo);
  }

  /**
   * Get nonce URL
   *
   * @param string $actionurl URL to add nonce to
   * @param string $action Nonce action name
   * @param string $name Parameter name (default: '_wpnonce')
   * @return string URL with nonce
   */
  public static function url($actionurl, $action, $name = '_wpnonce') {
    return wp_nonce_url($actionurl, $action, $name);
  }

  /**
   * Check if nonce exists in request
   *
   * @param string $nonce_key Request parameter key
   * @return bool True if nonce exists in request
   */
  public static function exists_in_request($nonce_key = '_wpnonce') {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- This helper checks FOR nonces
    return isset($_REQUEST[$nonce_key]) || isset($_POST[$nonce_key]) || isset($_GET[$nonce_key]);
  }

  /**
   * Get nonce from request
   *
   * @param string $nonce_key Request parameter key
   * @return string Nonce value or empty string
   */
  public static function get_from_request($nonce_key = '_wpnonce') {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- This helper retrieves nonces FOR verification
    if (isset($_POST[$nonce_key])) {
      // phpcs:ignore WordPress.Security.NonceVerification.Missing -- This helper retrieves nonces FOR verification
      return sanitize_text_field(wp_unslash($_POST[$nonce_key]));
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This helper retrieves nonces FOR verification
    } elseif (isset($_GET[$nonce_key])) {
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This helper retrieves nonces FOR verification
      return sanitize_text_field(wp_unslash($_GET[$nonce_key]));
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This helper retrieves nonces FOR verification
    } elseif (isset($_REQUEST[$nonce_key])) {
      // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This helper retrieves nonces FOR verification
      return sanitize_text_field(wp_unslash($_REQUEST[$nonce_key]));
    }
    return '';
  }
}
