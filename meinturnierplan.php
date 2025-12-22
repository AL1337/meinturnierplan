<?php
/**
 * Plugin Name: MeinTurnierplan
 * Plugin URI: https://www.meinturnierplan.de
 * Description: Display tournament tables and match lists from Tournej/MeinTurnierplan using shortcodes and blocks.
 * Version: 1.0.0
 * Author: MeinTurnierplan
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: meinturnierplan
 * Domain Path: /languages
 *
 * THIRD-PARTY SERVICE DISCLOSURE:
 * 
 * This plugin embeds content from meinturnierplan.de using iframes.
 * 
 * Service: MeinTurnierplan.de
 * Website: https://www.meinturnierplan.de/
 * Endpoints Used:
 *   - https://www.meinturnierplan.de/displayTable.php (tournament standings)
 *   - https://www.meinturnierplan.de/displayMatches.php (match schedules)
 * 
 * Data Sent: Tournament ID only (when you explicitly add a tournament shortcode, block, or widget)
 * When: When a visitor loads a page with tournament content
 * 
 * Privacy Policy: https://www.meinturnierplan.de/legal.php?t=privacy&v=2019-04-20&l=en
 * Terms of Service: https://www.meinturnierplan.de/legal.php?t=tou&v=2019-04-20&l=en
 * 
 * TRACKING & COOKIES (Technical Analysis - December 2024):
 * 
 * The embedded widgets do NOT:
 *   - Use tracking scripts (no Google Analytics, Facebook Pixel, etc.)
 *   - Set cookies
 *   - Load third-party resources (no Google Fonts, AdSense, etc.)
 *   - Track or identify users
 * 
 * The widgets ONLY:
 *   - Load CSS styling from meinturnierplan.de
 *   - Use JavaScript to communicate iframe dimensions (postMessage API)
 * 
 * Standard web server logging (IP address, browser, referrer, timestamp) may
 * occur when serving the embedded content, but this does not involve cookies
 * or user tracking.
 * 
 * PRIVACY NOTICE:
 * 
 * This plugin itself does not:
 *   - Track users
 *   - Collect personal data
 *   - Use cookies or localStorage
 *   - Send personal or sensitive data to any server
 * 
 * The only data sent is the Tournament ID to display the requested content.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

// Define plugin constants
if (!defined('MTP_PLUGIN_FILE')) {
  define('MTP_PLUGIN_FILE', __FILE__);
}
if (!defined('MTP_PLUGIN_URL')) {
  define('MTP_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('MTP_PLUGIN_PATH')) {
  define('MTP_PLUGIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('MTP_PLUGIN_VERSION')) {
  define('MTP_PLUGIN_VERSION', '1.0.0');
}

// Load requirements checker
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-requirements-checker.php';

// Check minimum requirements
if (!MTP_Requirements_Checker::check()) {
  return;
}

// Include admin notices
if (is_admin()) {
  require_once plugin_dir_path(__FILE__) . 'includes/admin-notices.php';
}

// Include required files
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-plugin.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-installer.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('MTP_Installer', 'activate'));
register_deactivation_hook(__FILE__, array('MTP_Installer', 'deactivate'));

// Initialize the plugin
MTP_Plugin::instance();
