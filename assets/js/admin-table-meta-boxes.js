/**
 * Admin Table Meta Boxes JavaScript
 *
 * @package MeinTurnierplan
 * @since   1.0.0
 */

(function($) {
  'use strict';

  // Helper function to get current iframe dimensions
  function getCurrentIframeDimensions() {
    var dimensions = { width: null, height: null };

    // Check if global dimensions are available from frontend script
    if (window.MTP_IframeDimensions) {
      // Find the most recent dimensions for any iframe
      var latestTimestamp = 0;
      var latestDimensions = null;

      for (var iframeId in window.MTP_IframeDimensions) {
        var dim = window.MTP_IframeDimensions[iframeId];
        if (dim.timestamp > latestTimestamp) {
          latestTimestamp = dim.timestamp;
          latestDimensions = dim;
        }
      }

      if (latestDimensions) {
        dimensions.width = latestDimensions.width;
        dimensions.height = latestDimensions.height;
      }
    }

    // Fallback: check actual iframe dimensions in the preview
    if (!dimensions.width || !dimensions.height) {
      var previewIframe = $("#mtp-preview iframe[id^='mtp-table-']").first();
      if (previewIframe.length) {
        dimensions.width = previewIframe.attr('width') || previewIframe.width();
        dimensions.height = previewIframe.attr('height') || previewIframe.height();
      }
    }

    return dimensions;
  }

  // Convert decimal opacity to hex (match PHP behavior)
  function opacityToHex(opacity) {
    var hex = Math.round(opacity).toString(16);
    return hex.length === 1 ? "0" + hex : hex;
  }

  // Define updateShortcode function globally so shared utilities can call it
  window.updateShortcode = function() {
    // Get configuration from localized script
    var config = window.mtpTableMetaBoxConfig || {};
    var postId = config.postId || 0;
    var defaultWidth = config.defaultWidth || "300";
    var defaultHeight = config.defaultHeight || "200";

    var tournamentId = $("#mtp_tournament_id").val() || "";

    // Get current iframe dimensions if available, otherwise use defaults
    var currentDimensions = getCurrentIframeDimensions();
    var width = currentDimensions.width || defaultWidth;
    var height = currentDimensions.height || defaultHeight;

    // Update hidden fields so the values get saved
    $("#mtp_width").val(width);
    $("#mtp_height").val(height);

    var fontSize = $("#mtp_font_size").val() || "9";
    var headerFontSize = $("#mtp_header_font_size").val() || "10";
    var textColor = $("#mtp_text_color").val().replace("#", "") || "000000";
    var mainColor = $("#mtp_main_color").val().replace("#", "") || "173f75";
    var tablePadding = $("#mtp_table_padding").val() || "2";
    var innerPadding = $("#mtp_inner_padding").val() || "5";
    var logoSize = $("#mtp_logo_size").val() || "20";
    var borderColor = $("#mtp_border_color").val().replace("#", "") || "bbbbbb";
    var headBottomBorderColor = $("#mtp_head_bottom_border_color").val().replace("#", "") || "bbbbbb";
    var bsizeh = $("#mtp_bsizeh").val() || "1";
    var bsizev = $("#mtp_bsizev").val() || "1";
    var bsizeoh = $("#mtp_bsizeoh").val() || "1";
    var bsizeov = $("#mtp_bsizeov").val() || "1";
    var bbsize = $("#mtp_bbsize").val() || "2";
    var language = $("#mtp_language").val() || "en";
    var group = $("#mtp_group").val() || "";

    // Combine colors with opacity (convert opacity percentage to hex)
    var bgColor = $("#mtp_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_bg_opacity").val() / 100) * 255));
    var evenBgColor = $("#mtp_even_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_even_bg_opacity").val() / 100) * 255));
    var oddBgColor = $("#mtp_odd_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_odd_bg_opacity").val() / 100) * 255));
    var hoverBgColor = $("#mtp_hover_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_hover_bg_opacity").val() / 100) * 255));
    var headBgColor = $("#mtp_head_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtp_head_bg_opacity").val() / 100) * 255));

    // Build complete shortcode (width and height removed for auto-sizing)
    var newShortcode = '[mtp-table id="' + tournamentId + '" post_id="' + postId + '" lang="' + language + '"' +
                      ' s-size="' + fontSize + '"' +
                      ' s-sizeheader="' + headerFontSize + '"' +
                      ' s-color="' + textColor + '"' +
                      ' s-maincolor="' + mainColor + '"' +
                      ' s-padding="' + tablePadding + '"' +
                      ' s-innerpadding="' + innerPadding + '"' +
                      ' s-bgcolor="' + bgColor + '"' +
                      ' s-bcolor="' + borderColor + '"' +
                      ' s-bbcolor="' + headBottomBorderColor + '"' +
                      ' s-bgeven="' + evenBgColor + '"' +
                      ' s-logosize="' + logoSize + '"' +
                      ' s-bsizeh="' + bsizeh + '"' +
                      ' s-bsizev="' + bsizev + '"' +
                      ' s-bsizeoh="' + bsizeoh + '"' +
                      ' s-bsizeov="' + bsizeov + '"' +
                      ' s-bbsize="' + bbsize + '"' +
                      ' s-bgodd="' + oddBgColor + '"' +
                      ' s-bgover="' + hoverBgColor + '"' +
                      ' s-bghead="' + headBgColor + '"';

    // Add sw parameter if suppress_wins checkbox is checked
    if ($("#mtp_suppress_wins").is(":checked")) {
      newShortcode += ' sw="1"';
    }

    // Add sl parameter if suppress_logos checkbox is checked
    if ($("#mtp_suppress_logos").is(":checked")) {
      newShortcode += ' sl="1"';
    }

    // Add sn parameter if suppress_num_matches checkbox is checked
    if ($("#mtp_suppress_num_matches").is(":checked")) {
      newShortcode += ' sn="1"';
    }

    // Add bm parameter if projector_presentation checkbox is checked
    if ($("#mtp_projector_presentation").is(":checked")) {
      newShortcode += ' bm="1"';
    }

    // Add nav parameter if navigation_for_groups checkbox is checked
    if ($("#mtp_navigation_for_groups").is(":checked")) {
      newShortcode += ' nav="1"';
    }

    // Add group parameter if selected
    if (group) {
      newShortcode += ' group="' + group + '"';
    }

    // Add width and height parameters
    newShortcode += ' width="' + width + '" height="' + height + '"';

    newShortcode += ']';

    $("#mtp_shortcode_field").val(newShortcode);
  };

  // Initialize on document ready
  $(document).ready(function() {
    // Call updateShortcode initially to populate the field
    if (typeof window.updateShortcode === 'function') {
      window.updateShortcode();
    }
  });

})(jQuery);
