<?php
/**
 * Plugin Name: MeinTurnierplan
 * Plugin URI: https://www.meinturnierplan.de
 * Description: Display tournament tables and match lists from MeinTurnierplan using shortcodes and blocks.
 * Version: 1.0.0
 * Author: MeinTurnierplan
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: meinturnierplan
 * Domain Path: /languages
 *
 * THIRD-PARTY SERVICE DISCLOSURE:
 * 
 * This plugin uses MeinTurnierplan.de for both displaying tournament content
 * and retrieving tournament configuration data.
 * 
 * Service: MeinTurnierplan.de
 * Website: https://www.meinturnierplan.de/
 * 
 * USAGE:
 * 
 * 1. Frontend Display (Public-Facing Content)
 * ================================================================
 * 
 * Purpose: Displays tournament tables and match schedules to site visitors
 * 
 * Endpoints Used:
 *   - https://www.meinturnierplan.de/displayTable.php (tournament standings)
 *   - https://www.meinturnierplan.de/displayMatches.php (match schedules)
 * 
 * Data Sent: Tournament ID only (when you explicitly add a tournament shortcode, block, or widget)
 * When: When a visitor loads a page with tournament content
 * Used on: Public-facing pages (frontend)
 * 
 * TRACKING & COOKIES:
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
 * 2. Admin Configuration (Admin Area Only)
 * ================================================================
 * 
 * Purpose: Provides tournament structure data via JSON API to help administrators
 *          configure displays
 * 
 * Endpoint Used:
 *   - https://www.meinturnierplan.de/json/json.php (tournament structure data in JSON format)
 * 
 * Data Sent: Tournament ID only (no personal data, no user information)
 * When: Only in WordPress admin area when:
 *   - Administrator enters a Tournament ID in settings
 *   - Administrator clicks "Refresh Groups" or similar refresh buttons
 *   - Admin preview is loaded or refreshed
 * 
 * Used on: WordPress admin area ONLY (backend)
 * NOT used on: Public-facing pages (frontend) - visitors never trigger this endpoint
 * 
 * What it retrieves:
 *   - Tournament groups/divisions structure
 *   - Team lists and names
 *   - Tournament options (showCourts, showGroups, showReferees, finalMatches)
 * 
 * Purpose of retrieval:
 *   - Auto-populate group selection dropdowns in admin interface
 *   - Determine which features are available for the tournament
 *   - Provide better admin user experience with automatic configuration
 * 
 * Data caching: Retrieved data is cached for 15 minutes to minimize API calls
 * 
 * Standard web server logging may apply (IP address, timestamp, browser type)
 * 
 * ================================================================
 * 
 * Privacy Policy: https://www.meinturnierplan.de/legal.php?t=privacy&v=2019-04-20&l=en
 * Terms of Service: https://www.meinturnierplan.de/legal.php?t=tou&v=2019-04-20&l=en
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
if (!defined('MTRN_PLUGIN_FILE')) {
  define('MTRN_PLUGIN_FILE', __FILE__);
}
if (!defined('MTRN_PLUGIN_URL')) {
  define('MTRN_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('MTRN_PLUGIN_PATH')) {
  define('MTRN_PLUGIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('MTRN_PLUGIN_VERSION')) {
  define('MTRN_PLUGIN_VERSION', '1.0.0');
}

// Load requirements checker
require_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-requirements-checker.php';

// Check minimum requirements
if (!MTRN_Requirements_Checker::check()) {
  return;
}

// Include admin notices
if (is_admin()) {
  require_once plugin_dir_path(__FILE__) . 'includes/admin-notices.php';
}

// Include required files
require_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-plugin.php';
require_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-installer.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('MTRN_Installer', 'activate'));
register_deactivation_hook(__FILE__, array('MTRN_Installer', 'deactivate'));

// Initialize the plugin
MTRN_Plugin::instance();
