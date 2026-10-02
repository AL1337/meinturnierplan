/**
 * Admin Matches Meta Boxes JavaScript
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
      var previewIframe = $("#mtrn-preview iframe[id^='mtrn-matches-']").first();
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
    var config = window.mtrnMatchesMetaBoxConfig || {};
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
    var borderColor = $("#mtrn_border_color").val().replace("#", "") || "bbbbbb";
    var headBottomBorderColor = $("#mtrn_head_bottom_border_color").val().replace("#", "") || "bbbbbb";
    var bsizeh = $("#mtrn_bsizeh").val() || "1";
    var bsizev = $("#mtrn_bsizev").val() || "1";
    var bsizeoh = $("#mtrn_bsizeoh").val() || "1";
    var bsizeov = $("#mtrn_bsizeov").val() || "1";
    var bbsize = $("#mtrn_bbsize").val() || "2";
    var ehrsize = $("#mtrn_ehrsize").val() || "10";
    var ehrtop = $("#mtrn_ehrtop").val() || "9";
    var ehrbottom = $("#mtrn_ehrbottom").val() || "3";
    var language = $("#mtrn_language").val() || "en";
    var group = $("#mtrn_group").val() || "";
    var participant = $("#mtrn_participant").val() || "-1";
    var matchNumber = $("#mtrn_match_number").val() || "";

    // Combine colors with opacity (convert opacity percentage to hex)
    var bgColor = $("#mtrn_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_bg_opacity").val() / 100) * 255));
    var evenBgColor = $("#mtrn_even_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_even_bg_opacity").val() / 100) * 255));
    var oddBgColor = $("#mtrn_odd_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_odd_bg_opacity").val() / 100) * 255));
    var hoverBgColor = $("#mtrn_hover_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_hover_bg_opacity").val() / 100) * 255));
    var headBgColor = $("#mtrn_head_bg_color").val().replace("#", "") + opacityToHex(Math.round(($("#mtrn_head_bg_opacity").val() / 100) * 255));

    // Build complete shortcode (width and height removed for auto-sizing)
    var newShortcode = '[mtrn-matches id="' + tournamentId + '" post_id="' + postId + '" lang="' + language + '"' +
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
                      ' s-bsizeh="' + bsizeh + '"' +
                      ' s-bsizev="' + bsizev + '"' +
                      ' s-bsizeoh="' + bsizeoh + '"' +
                      ' s-bsizeov="' + bsizeov + '"' +
                      ' s-bbsize="' + bbsize + '"' +
                      ' s-ehrsize="' + ehrsize + '"' +
                      ' s-ehrtop="' + ehrtop + '"' +
                      ' s-ehrbottom="' + ehrbottom + '"' +
                      ' s-bgodd="' + oddBgColor + '"' +
                      ' s-bgover="' + hoverBgColor + '"' +
                      ' s-bghead="' + headBgColor + '"';

    // Add bm parameter if projector_presentation checkbox is checked
    if ($("#mtrn_projector_presentation").is(":checked")) {
      newShortcode += ' bm="1"';
    }

    // Add si parameter if si checkbox is checked
    if ($("#mtrn_si").is(":checked")) {
      newShortcode += ' si="1"';
    }

    // Add sf parameter if sf checkbox is checked (Suppress Court)
    if ($("#mtrn_sf").is(":checked")) {
      newShortcode += ' sf="1"';
    }

    // Add st parameter if st checkbox is checked
    if ($("#mtrn_st").is(":checked")) {
      newShortcode += ' st="1"';
    }

    // Add sg parameter if sg checkbox is checked
    if ($("#mtrn_sg").is(":checked")) {
      newShortcode += ' sg="1"';
    }

    // Add sr parameter if sr checkbox is checked (Suppress Referee)
    if ($("#mtrn_sr").is(":checked")) {
      newShortcode += ' sr="1"';
    }

    // Add se parameter if se checkbox is checked
    if ($("#mtrn_se").is(":checked")) {
      newShortcode += ' se="1"';
    }

    // Add sp parameter if sp checkbox is checked
    if ($("#mtrn_sp").is(":checked")) {
      newShortcode += ' sp="1"';
    }

    // Add sh parameter if sh checkbox is checked
    if ($("#mtrn_sh").is(":checked")) {
      newShortcode += ' sh="1"';
    }

    // Add s-wrap parameter if responsive layout checkbox is checked
    if ($("#mtrn_responsive").is(":checked")) {
      newShortcode += ' s-wrap="true"';
    }

    // Add group parameter if selected
    if (group) {
      newShortcode += ' group="' + group + '"';
    }

    // Add participant parameter if selected and not default "All"
    if (participant && participant !== '-1') {
      newShortcode += ' participant="' + participant + '"';
    }

    // Add gamenumbers parameter if specified
    if (matchNumber) {
      newShortcode += ' gamenumbers="' + matchNumber + '"';
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
