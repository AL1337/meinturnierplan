<?php
/**
 * Admin Notices Class
 *
 * Handles admin notices for third-party service disclosure.
 *
 * @package MeinTurnierplan
 * @since   1.0.0
 * @version 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Admin Notices Class
 */
class MTRN_Admin_Notices {

  /**
   * Option name for dismissal status
   *
   * @var string
   */
  private $dismissal_option = 'mtrn_service_notice_dismissed';

  /**
   * Nonce action name
   *
   * @var string
   */
  private $nonce_action = 'mtrn_dismiss_notice';

  /**
   * Constructor
   */
  public function __construct() {
    $this->init();
  }

  /**
   * Initialize hooks
   */
  private function init() {
    add_action('admin_notices', array($this, 'display_service_disclosure_notice'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_notice_assets'));
    add_action('wp_ajax_mtrn_dismiss_service_notice', array($this, 'handle_dismiss_notice'));
  }

  /**
   * Display admin notice about third-party service disclosure
   *
   * Shows a dismissible notice to administrators informing them about
  * the use of language-specific service domains for embedded content and data handling.
   *
   * @since 1.0.0
   * @return void
   */
  public function display_service_disclosure_notice() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
      return;
    }

    // Check if notice has been dismissed
    if (get_option($this->dismissal_option)) {
      return;
    }

    $service_language = MTRN_Admin_Utilities::get_default_language();
    $service_base_url = MTRN_Admin_Utilities::get_api_base_url($service_language);
    $legal_language = in_array($service_language, array('de', 'en', 'es', 'fr', 'it', 'pl'), true) ? $service_language : 'en';

    $nonce = wp_create_nonce($this->nonce_action);
    ?>
    <div class="notice notice-info is-dismissible" id="mtrn-service-notice">
      <h3 class="mtrn-notice-title"><?php esc_html_e('Third-Party Service Information', 'meinturnierplan'); ?></h3>
      
      <p>
        <?php esc_html_e('This plugin embeds tournament content from language-specific service domains. When you add tournament displays to your pages, users will connect directly to the domain that matches the selected tournament language.', 'meinturnierplan'); ?>
      </p>

      <p><strong><?php esc_html_e('What data is sent:', 'meinturnierplan'); ?></strong></p>
      <ul class="mtrn-notice-list">
        <li><?php esc_html_e('Tournament ID only (when you add a tournament via shortcode, block, or widget)', 'meinturnierplan'); ?></li>
        <li><?php esc_html_e('No personal data or user tracking information is sent by this plugin', 'meinturnierplan'); ?></li>
      </ul>

      <p><strong><?php esc_html_e('Privacy & Tracking:', 'meinturnierplan'); ?></strong></p>
      <ul class="mtrn-notice-list">
        <li><?php esc_html_e('This plugin does not track users or collect personal data', 'meinturnierplan'); ?></li>
        <li><?php esc_html_e('The embedded widgets do not use cookies or tracking scripts', 'meinturnierplan'); ?></li>
        <li><?php esc_html_e('Standard web server logging (IP, browser, referrer) may occur when serving content', 'meinturnierplan'); ?></li>
      </ul>

      <p>
        <strong><?php esc_html_e('Service Information:', 'meinturnierplan'); ?></strong><br>
        <a href="<?php echo esc_url($service_base_url . '/'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Visit Website', 'meinturnierplan'); ?></a>
        | <a href="<?php echo esc_url($service_base_url . '/legal.php?t=privacy&v=2019-04-20&l=' . $legal_language); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Privacy Policy', 'meinturnierplan'); ?></a>
        | <a href="<?php echo esc_url($service_base_url . '/legal.php?t=tou&v=2019-04-20&l=' . $legal_language); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Terms of Service', 'meinturnierplan'); ?></a>
      </p>

      <p>
        <button type="button" class="button button-primary" id="mtrn-dismiss-notice">
          <?php esc_html_e('I Understand', 'meinturnierplan'); ?>
        </button>
      </p>
    </div>
    <?php
  }

  /**
   * Enqueue admin notice styles and script
   *
   * Properly enqueues the CSS styles and jQuery script for the admin notice
   * using WordPress best practices.
   *
   * @since 1.0.0
   * @return void
   */
  public function enqueue_admin_notice_assets() {
    // Only enqueue on admin pages where the notice might be shown
    if (!current_user_can('manage_options') || get_option($this->dismissal_option)) {
      return;
    }

    // Register and enqueue admin notice styles
    wp_register_style(
      'mtrn-admin-notices',
      plugins_url('assets/css/admin-notices.css', dirname(__FILE__)),
      array(),
      '1.0.0'
    );
    wp_enqueue_style('mtrn-admin-notices');

    // Enqueue jQuery (WordPress default)
    wp_enqueue_script('jquery');

    // Add inline script for notice dismissal
    $script = "
      jQuery(document).ready(function($) {
        $('#mtrn-dismiss-notice, #mtrn-service-notice .notice-dismiss').on('click', function() {
          $.post(ajaxurl, {
            action: 'mtrn_dismiss_service_notice',
            nonce: '" . wp_create_nonce($this->nonce_action) . "'
          }, function() {
            $('#mtrn-service-notice').fadeOut();
          });
        });
      });
    ";

    wp_add_inline_script('jquery', $script);
  }

  /**
   * Handle AJAX request to dismiss the service notice
   *
   * Stores a flag in the database that the notice has been dismissed
   * so it won't be shown again to the administrator.
   *
   * @since 1.0.0
   * @return void
   */
  public function handle_dismiss_notice() {
    check_ajax_referer($this->nonce_action, 'nonce');
    
    if (current_user_can('manage_options')) {
      update_option($this->dismissal_option, true);
      wp_send_json_success();
    } else {
      wp_send_json_error();
    }
  }
}
