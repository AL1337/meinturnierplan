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

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

// Exit if accessed directly or not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

// Define plugin path if not already defined
if (!defined('MTRN_PLUGIN_PATH')) {
  define('MTRN_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

// The installer needs the option registry for the post type list
require_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-options.php';
require_once MTRN_PLUGIN_PATH . 'includes/class-mtrn-installer.php';

// Run the uninstall process - this will remove all plugin data
MTRN_Installer::uninstall();
