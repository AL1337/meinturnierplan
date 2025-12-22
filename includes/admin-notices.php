<?php
/**
 * Admin Notices
 *
 * Handles admin notices for third-party service disclosure.
 *
 * @package MeinTurnierplan
 * @since   1.0.0
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Display admin notice about third-party service disclosure
 *
 * Shows a dismissible notice to administrators informing them about
 * the use of meinturnierplan.de embedded content and data handling.
 *
 * @since 1.0.0
 * @return void
 */
function mtp_service_disclosure_notice() {
  // Only show to administrators
  if (!current_user_can('manage_options')) {
    return;
  }

  // Check if notice has been dismissed
  if (get_option('mtp_service_notice_dismissed')) {
    return;
  }

  $nonce = wp_create_nonce('mtp_dismiss_notice');
  ?>
  <div class="notice notice-info is-dismissible" id="mtp-service-notice">
    <h3 style="margin-bottom: 0.5em;"><?php esc_html_e('Third-Party Service Information', 'meinturnierplan'); ?></h3>
    
    <p>
      <?php esc_html_e('This plugin embeds tournament content from meinturnierplan.de. When you add tournament displays to your pages, users will connect directly to meinturnierplan.de servers.', 'meinturnierplan'); ?>
    </p>

    <p><strong><?php esc_html_e('What data is sent:', 'meinturnierplan'); ?></strong></p>
    <ul style="list-style: disc; margin-left: 20px; margin-top: 0;">
      <li><?php esc_html_e('Tournament ID only (when you add a tournament via shortcode, block, or widget)', 'meinturnierplan'); ?></li>
      <li><?php esc_html_e('No personal data or user tracking information is sent by this plugin', 'meinturnierplan'); ?></li>
    </ul>

    <p><strong><?php esc_html_e('Privacy & Tracking:', 'meinturnierplan'); ?></strong></p>
    <ul style="list-style: disc; margin-left: 20px; margin-top: 0;">
      <li><?php esc_html_e('This plugin does not track users or collect personal data', 'meinturnierplan'); ?></li>
      <li><?php esc_html_e('The embedded widgets do not use cookies or tracking scripts', 'meinturnierplan'); ?></li>
      <li><?php esc_html_e('Standard web server logging (IP, browser, referrer) may occur when serving content', 'meinturnierplan'); ?></li>
    </ul>

    <p>
      <strong><?php esc_html_e('Service Information:', 'meinturnierplan'); ?></strong><br>
      <a href="https://www.meinturnierplan.de/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Visit Website', 'meinturnierplan'); ?></a>
      | <a href="https://www.meinturnierplan.de/legal.php?t=privacy&v=2019-04-20&l=en" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Privacy Policy', 'meinturnierplan'); ?></a>
      | <a href="https://www.meinturnierplan.de/legal.php?t=tou&v=2019-04-20&l=en" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Terms of Service', 'meinturnierplan'); ?></a>
    </p>

    <p>
      <button type="button" class="button button-primary" id="mtp-dismiss-notice">
        <?php esc_html_e('I Understand', 'meinturnierplan'); ?>
      </button>
    </p>
  </div>
  <script>
  jQuery(document).ready(function($) {
    $('#mtp-dismiss-notice, #mtp-service-notice .notice-dismiss').on('click', function() {
      $.post(ajaxurl, {
        action: 'mtp_dismiss_service_notice',
        nonce: '<?php echo esc_js($nonce); ?>'
      }, function() {
        $('#mtp-service-notice').fadeOut();
      });
    });
  });
  </script>
  <?php
}
add_action('admin_notices', 'mtp_service_disclosure_notice');

/**
 * Handle AJAX request to dismiss the service notice
 *
 * Stores a flag in the database that the notice has been dismissed
 * so it won't be shown again to the administrator.
 *
 * @since 1.0.0
 * @return void
 */
function mtp_dismiss_service_notice() {
  check_ajax_referer('mtp_dismiss_notice', 'nonce');
  
  if (current_user_can('manage_options')) {
    update_option('mtp_service_notice_dismissed', true);
    wp_send_json_success();
  } else {
    wp_send_json_error();
  }
}
add_action('wp_ajax_mtp_dismiss_service_notice', 'mtp_dismiss_service_notice');
