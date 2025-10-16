<?php
/**
 * Plugin Requirements Checker
 *
 * @package MeinTurnierplan
 * @since 0.3.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Class MTP_Requirements_Checker
 *
 * Checks if the server environment meets the plugin requirements
 */
class MTP_Requirements_Checker {

  /**
   * Minimum PHP version required
   */
  const MIN_PHP_VERSION = '7.4';

  /**
   * Minimum WordPress version required
   */
  const MIN_WP_VERSION = '6.3';

  /**
   * Check if all requirements are met
   *
   * @return bool True if requirements are met, false otherwise
   */
  public static function check() {
    $errors = array();

    // Check PHP version
    if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
      $errors[] = sprintf(
        /* translators: 1: minimum PHP version, 2: current PHP version */
        esc_html__('MeinTurnierplan requires PHP %1$s or higher. You are running PHP %2$s.', 'meinturnierplan'),
        self::MIN_PHP_VERSION,
        PHP_VERSION
      );
    }

    // Check WordPress version
    if (version_compare(get_bloginfo('version'), self::MIN_WP_VERSION, '<')) {
      $errors[] = sprintf(
        /* translators: 1: minimum WordPress version, 2: current WordPress version */
        esc_html__('MeinTurnierplan requires WordPress %1$s or higher. You are running WordPress %2$s.', 'meinturnierplan'),
        self::MIN_WP_VERSION,
        get_bloginfo('version')
      );
    }

    // Display error notices if requirements are not met
    if (!empty($errors)) {
      self::display_admin_notices($errors);
      return false;
    }

    return true;
  }

  /**
   * Display admin error notices
   *
   * @param array $errors Array of error messages
   */
  private static function display_admin_notices($errors) {
    add_action('admin_notices', function() use ($errors) {
      foreach ($errors as $error) {
        echo '<div class="notice notice-error"><p><strong>' .
             esc_html__('MeinTurnierplan Error:', 'meinturnierplan') .
             '</strong> ' . $error . '</p></div>';
      }
    });
  }

  /**
   * Get minimum PHP version
   *
   * @return string Minimum PHP version
   */
  public static function get_min_php_version() {
    return self::MIN_PHP_VERSION;
  }

  /**
   * Get minimum WordPress version
   *
   * @return string Minimum WordPress version
   */
  public static function get_min_wp_version() {
    return self::MIN_WP_VERSION;
  }
}
