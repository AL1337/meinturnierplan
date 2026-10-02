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
    if (window.MTRN_IframeDimensions) {
      // Find the most recent dimensions for any iframe
      var latestTimestamp = 0;
      var latestDimensions = null;

      for (var iframeId in window.MTRN_IframeDimensions) {
        var dim = window.MTRN_IframeDimensions[iframeId];
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
      var previewIframe = $("#mtrn-preview iframe[id^='mtrn-table-']").first();
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
    var config = window.mtrnTableMetaBoxConfig || {};
    var postId = config.postId || 0;
    var defaultWidth = config.defaultWidth || "300";
    var defaultHeight = config.defaultHeight || "200";

    var tournamentId = $("#mtrn_tournament_id").val() || "";

    // Get current iframe dimensions if available, otherwise use defaults
    var currentDimensions = getCurrentIframeDimensions();
    var width = currentDimensions.width || defaultWidth;
    var height = currentDimensions.height || defaultHeight;

    // Update hidden fields so the values get saved
    $("#mtrn_width").val(width);
    $("#mtrn_height").val(height);

    var fontSize = $("#mtrn_font_size").val() || "9";
    var headerFontSize = $("#mtrn_header_font_size").val() || "10";
    var textColor = $("#mtrn_text_color").val().replace("#", "") || "000000";
    var mainColor = $("#mtrn_main_color").val().replace("#", "") || "173f75";
    var tablePadding = $("#mtrn_table_padding").val() || "2";
    var innerPadding = $("#mtrn_inner_padding").val() || "5";
    var logoSize = $("#mtrn_logo_size").val() || "20";
    var borderColor = $("#mtrn_border_color").val().replace("#", "") || "bbbbbb";
    var headBottomBorderColor = $("#mtrn_head_bottom_border_color").val().replace("#", "") || "bbbbbb";
    var bsizeh = $("#mtrn_bsizeh").val() || "1";
    var bsizev = $("#mtrn_bsizev").val() || "1";
    var bsizeoh = $("#mtrn_bsizeoh").val() || "1";
    var bsizeov = $("#mtrn_bsizeov").val() || "1";
    var bbsize = $("#mtrn_bbsize").val() || "2";
    var language = $("#mtrn_language").val() || "en";
    var group = $("#mtrn_group").val() || "";

    // Combine colors with opacity (convert opacity percentage to hex)
    var bgColor = $("#mtrn_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_bg_opacity").val() / 100) * 255));
    var evenBgColor = $("#mtrn_even_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_even_bg_opacity").val() / 100) * 255));
    var oddBgColor = $("#mtrn_odd_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_odd_bg_opacity").val() / 100) * 255));
    var hoverBgColor = $("#mtrn_hover_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_hover_bg_opacity").val() / 100) * 255));
    var headBgColor = $("#mtrn_head_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_head_bg_opacity").val() / 100) * 255));

    // Build complete shortcode (width and height removed for auto-sizing)
    var newShortcode = '[mtrn-table id="' + tournamentId + '" post_id="' + postId + '" lang="' + language + '"' +
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
    if ($("#mtrn_suppress_wins").is(":checked")) {
      newShortcode += ' sw="1"';
    }

    // Add sl parameter if suppress_logos checkbox is checked
    if ($("#mtrn_suppress_logos").is(":checked")) {
      newShortcode += ' sl="1"';
    }

    // Add sn parameter if suppress_num_matches checkbox is checked
    if ($("#mtrn_suppress_num_matches").is(":checked")) {
      newShortcode += ' sn="1"';
    }

    // Add bm parameter if projector_presentation checkbox is checked
    if ($("#mtrn_projector_presentation").is(":checked")) {
      newShortcode += ' bm="1"';
    }

    // Add nav parameter if navigation_for_groups checkbox is checked
    if ($("#mtrn_navigation_for_groups").is(":checked")) {
      newShortcode += ' nav="1"';
    }

    // Add s-wrap parameter if responsive layout checkbox is checked
    if ($("#mtrn_responsive").is(":checked")) {
      newShortcode += ' s-wrap="true"';
    }

    // Add group parameter if selected
    if (group) {
      newShortcode += ' group="' + group + '"';
    }

    // Add width and height parameters
    newShortcode += ' width="' + width + '" height="' + height + '"';

    newShortcode += ']';

    $("#mtrn_shortcode_field").val(newShortcode);
  };

  // Initialize on document ready
  $(document).ready(function() {
    // Call updateShortcode initially to populate the field
    if (typeof window.updateShortcode === 'function') {
      window.updateShortcode();
    }
  });

})(jQuery);
