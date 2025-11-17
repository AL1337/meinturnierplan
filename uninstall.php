<?php
/**
 * Uninstall MeinTurnierplan
 *
 * Fired when the plugin is uninstalled via the WordPress admin.
 * This file is executed when a user deletes the plugin from WordPress.
 *
 * @package MeinTurnierplan
 * @since   0.3.2
 * @version 1.0.0
 */

// Exit if accessed directly or not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

// Define plugin path if not already defined
if (!defined('MTP_PLUGIN_PATH')) {
  define('MTP_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

// Include the installer class which contains the uninstall method
require_once MTP_PLUGIN_PATH . 'includes/class-mtp-installer.php';

// Run the uninstall process - this will remove all plugin data
MTP_Installer::uninstall();
