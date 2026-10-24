<?php
/**
 * PHPUnit bootstrap.
 *
 * The unit tests stub WordPress functions with Brain Monkey, so no WordPress
 * install or database is needed. Only the plugin classes under test are loaded.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('ABSPATH')) {
  define('ABSPATH', dirname(__DIR__) . '/');
}
if (!defined('MINUTE_IN_SECONDS')) {
  define('MINUTE_IN_SECONDS', 60);
}
if (!defined('MTRN_PLUGIN_PATH')) {
  define('MTRN_PLUGIN_PATH', dirname(__DIR__) . '/');
}
if (!defined('MTRN_PLUGIN_URL')) {
  define('MTRN_PLUGIN_URL', 'https://example.test/wp-content/plugins/meinturnierplan/');
}
if (!defined('MTRN_PLUGIN_VERSION')) {
  define('MTRN_PLUGIN_VERSION', 'test');
}

foreach (array(
  'class-mtrn-admin-utilities.php',
  'class-mtrn-table-renderer.php',
  'class-mtrn-matches-renderer.php',
  'class-mtrn-table-shortcode.php',
  'class-mtrn-matches-shortcode.php',
  'class-mtrn-table-block.php',
  'class-mtrn-matches-block.php',
) as $file) {
  require_once MTRN_PLUGIN_PATH . 'includes/' . $file;
}
