<?php
/**
 * Plugin Name: MeinTurnierplan
 * Plugin URI: https://github.com/danfisher85/meinturnierplan-wp
 * Description: A WordPress plugin to display tournament tables and match lists using custom post types, shortcodes, and widgets.
 * Version: 0.2.3
 * Author: Roman Perevala
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: meinturnierplan
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

// Define plugin constants
define('MTP_PLUGIN_FILE', __FILE__);
define('MTP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MTP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MTP_PLUGIN_VERSION', '0.2.3');

// Include required files
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-plugin.php';
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-installer.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('MTP_Installer', 'activate'));
register_deactivation_hook(__FILE__, array('MTP_Installer', 'deactivate'));

// Initialize the plugin
MTP_Plugin::instance();
