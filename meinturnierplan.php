<?php
/**
 * Plugin Name: MeinTurnierplan
 * Plugin URI: https://www.meinturnierplan.de
 * Description: Display tournament tables and match lists from MeinTurnierplan using shortcodes and blocks.
 * Version: 1.0.4
 * Author: MeinTurnierplan
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
  define('MTRN_PLUGIN_VERSION', '1.0.4');
}

// Load requirements checker
require_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-requirements-checker.php';

// Check minimum requirements
if (!MTRN_Requirements_Checker::check()) {
  return;
}

// Include required files
require_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-plugin.php';
require_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-installer.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('MTRN_Installer', 'activate'));
register_deactivation_hook(__FILE__, array('MTRN_Installer', 'deactivate'));

// Initialize the plugin
MTRN_Plugin::instance();
