<?php
/**
 * Plugin Name: MeinTurnierplan
 * Plugin URI: https://www.meinturnierplan.de
 * Description: Display tournament tables and match lists from Tournej/MeinTurnierplan using shortcodes and blocks.
 * Version: 1.0.0
 * Author: Roman Perevala
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: meinturnierplan
 * Domain Path: /languages
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

// Include required files
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-plugin.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-installer.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('MTP_Installer', 'activate'));
register_deactivation_hook(__FILE__, array('MTP_Installer', 'deactivate'));

// Initialize the plugin
MTP_Plugin::instance();
