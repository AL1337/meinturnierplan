/**
 * MTP Shortcode Generator
 * 
 * Handles shortcode copy-to-clipboard functionality and form field listeners
 * 
 * @package MeinTurnierplan
 * @since   1.0.0
 */

(function($) {
  'use strict';

  $(document).ready(function() {
    var config = window.mtpShortcodeConfig || {};
    
    // Copy shortcode to clipboard functionality
    $("#" + config.copyButtonId).on("click", function() {
      var shortcodeField = $("#" + config.fieldId);
      shortcodeField.select();
      document.execCommand("copy");

      $("#" + config.successMessageId).fadeIn().delay(2000).fadeOut();
    });

    // Form field change listeners - calls custom update function if it exists
    if (typeof window[config.updateCallback] === 'function') {
      // Input and select field changes
      $("input[id^='" + config.fieldPrefix + "'], .mtp-color-picker, select[id^='" + config.fieldPrefix + "']").on("input change", function() {
        window[config.updateCallback]();
      });

      // Opacity slider changes
      $("input[type='range'][id^='" + config.fieldPrefix + "']").on("input", function() {
        window[config.updateCallback]();
      });
    }
  });

})(jQuery);
