/**
 * Admin Table Preview JavaScript
 *
 * @package MeinTurnierplan
 * @since   1.0.0
 */

(function($) {
  'use strict';

  // Function to update preview
  function updatePreview() {
    var config = window.mtpTablePreviewConfig || {};
    var postId = config.postId || 0;
    var previewNonce = config.previewNonce || '';
    var fieldList = config.fieldList || [];

    // Get all field values
    var data = {
      post_id: postId,
      tournament_id: $("#mtp_tournament_id").val(),
      font_size: $("#mtp_font_size").val(),
      header_font_size: $("#mtp_header_font_size").val(),
      bsizeh: $("#mtp_bsizeh").val(),
      bsizev: $("#mtp_bsizev").val(),
      bsizeoh: $("#mtp_bsizeoh").val(),
      bsizeov: $("#mtp_bsizeov").val(),
      bbsize: $("#mtp_bbsize").val(),
      table_padding: $("#mtp_table_padding").val(),
      inner_padding: $("#mtp_inner_padding").val(),
      text_color: $("#mtp_text_color").val().replace("#", ""),
      main_color: $("#mtp_main_color").val().replace("#", ""),
      bg_color: $("#mtp_bg_color").val().replace("#", ""),
      logo_size: $("#mtp_logo_size").val(),
      bg_opacity: $("#mtp_bg_opacity").val(),
      border_color: $("#mtp_border_color").val().replace("#", ""),
      head_bottom_border_color: $("#mtp_head_bottom_border_color").val().replace("#", ""),
      even_bg_color: $("#mtp_even_bg_color").val().replace("#", ""),
      even_bg_opacity: $("#mtp_even_bg_opacity").val(),
      odd_bg_color: $("#mtp_odd_bg_color").val().replace("#", ""),
      odd_bg_opacity: $("#mtp_odd_bg_opacity").val(),
      hover_bg_color: $("#mtp_hover_bg_color").val().replace("#", ""),
      hover_bg_opacity: $("#mtp_hover_bg_opacity").val(),
      head_bg_color: $("#mtp_head_bg_color").val().replace("#", ""),
      head_bg_opacity: $("#mtp_head_bg_opacity").val(),
      suppress_wins: $("#mtp_suppress_wins").is(":checked") ? "1" : "0",
      suppress_logos: $("#mtp_suppress_logos").is(":checked") ? "1" : "0",
      suppress_num_matches: $("#mtp_suppress_num_matches").is(":checked") ? "1" : "0",
      projector_presentation: $("#mtp_projector_presentation").is(":checked") ? "1" : "0",
      navigation_for_groups: $("#mtp_navigation_for_groups").is(":checked") ? "1" : "0",
      language: $("#mtp_language").val(),
      group: $("#mtp_group").val(),
      action: "mtp_preview_table",
      nonce: previewNonce
    };

    // Convert opacity to hex and combine with colors
    data.bg_color = data.bg_color + Math.round((data.bg_opacity / 100) * 255).toString(16).padStart(2, "0");
    data.even_bg_color = data.even_bg_color + Math.round((data.even_bg_opacity / 100) * 255).toString(16).padStart(2, "0");
    data.odd_bg_color = data.odd_bg_color + Math.round((data.odd_bg_opacity / 100) * 255).toString(16).padStart(2, "0");
    data.hover_bg_color = data.hover_bg_color + Math.round((data.hover_bg_opacity / 100) * 255).toString(16).padStart(2, "0");
    data.head_bg_color = data.head_bg_color + Math.round((data.head_bg_opacity / 100) * 255).toString(16).padStart(2, "0");

    $.post(ajaxurl, data, function(response) {
      if (response.success) {
        $("#mtp-preview").html(response.data);
      }
    });
  }

  // Initialize on document ready
  $(document).ready(function() {
    var config = window.mtpTablePreviewConfig || {};
    var fieldList = config.fieldList || [];

    // Initialize reusable utilities with preview update callback
    MTPAdminUtils.initColorPickers(updatePreview);
    MTPAdminUtils.initOpacitySliders(updatePreview);
    MTPAdminUtils.initFormFieldListeners('mtp_', updatePreview);

    // Initialize tournament ID field with group loading
    MTPAdminUtils.initTournamentIdField('#mtp_tournament_id', updatePreview, function(tournamentId) {
      MTPAdminUtils.loadTournamentGroups(tournamentId);
    });

    // Initialize group refresh button
    MTPAdminUtils.initGroupRefreshButton('#mtp_refresh_groups', '#mtp_tournament_id', function(tournamentId, options) {
      MTPAdminUtils.loadTournamentGroups(tournamentId, options);
    });

    // Load groups on page load if tournament ID exists
    var initialTournamentId = $("#mtp_tournament_id").val();
    if (initialTournamentId) {
      MTPAdminUtils.loadTournamentGroups(initialTournamentId, {preserveSelection: false});
    }

    // Add specific field listeners for all form fields
    if (fieldList.length > 0) {
      var fieldSelector = '#' + fieldList.join(', #');
      $(fieldSelector).on("input change", function() {
        updatePreview();
      });
    }
  });

})(jQuery);
