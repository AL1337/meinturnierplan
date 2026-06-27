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
    var config = window.mtrnTablePreviewConfig || {};
    var postId = config.postId || 0;
    var previewNonce = config.previewNonce || '';
    var fieldList = config.fieldList || [];

    // Get all field values
    var data = {
      post_id: postId,
      tournament_id: $("#mtrn_tournament_id").val(),
      font_size: $("#mtrn_font_size").val(),
      header_font_size: $("#mtrn_header_font_size").val(),
      bsizeh: $("#mtrn_bsizeh").val(),
      bsizev: $("#mtrn_bsizev").val(),
      bsizeoh: $("#mtrn_bsizeoh").val(),
      bsizeov: $("#mtrn_bsizeov").val(),
      bbsize: $("#mtrn_bbsize").val(),
      table_padding: $("#mtrn_table_padding").val(),
      inner_padding: $("#mtrn_inner_padding").val(),
      text_color: $("#mtrn_text_color").val().replace("#", ""),
      main_color: $("#mtrn_main_color").val().replace("#", ""),
      bg_color: $("#mtrn_bg_color").val().replace("#", ""),
      logo_size: $("#mtrn_logo_size").val(),
      bg_opacity: $("#mtrn_bg_opacity").val(),
      border_color: $("#mtrn_border_color").val().replace("#", ""),
      head_bottom_border_color: $("#mtrn_head_bottom_border_color").val().replace("#", ""),
      even_bg_color: $("#mtrn_even_bg_color").val().replace("#", ""),
      even_bg_opacity: $("#mtrn_even_bg_opacity").val(),
      odd_bg_color: $("#mtrn_odd_bg_color").val().replace("#", ""),
      odd_bg_opacity: $("#mtrn_odd_bg_opacity").val(),
      hover_bg_color: $("#mtrn_hover_bg_color").val().replace("#", ""),
      hover_bg_opacity: $("#mtrn_hover_bg_opacity").val(),
      head_bg_color: $("#mtrn_head_bg_color").val().replace("#", ""),
      head_bg_opacity: $("#mtrn_head_bg_opacity").val(),
      suppress_wins: $("#mtrn_suppress_wins").is(":checked") ? "1" : "0",
      suppress_logos: $("#mtrn_suppress_logos").is(":checked") ? "1" : "0",
      suppress_num_matches: $("#mtrn_suppress_num_matches").is(":checked") ? "1" : "0",
      projector_presentation: $("#mtrn_projector_presentation").is(":checked") ? "1" : "0",
      navigation_for_groups: $("#mtrn_navigation_for_groups").is(":checked") ? "1" : "0",
      responsive: $("#mtrn_responsive").is(":checked") ? "1" : "0",
      language: $("#mtrn_language").val(),
      group: $("#mtrn_group").val(),
      action: "mtrn_preview_table",
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
        $("#mtrn-preview").html(response.data);
      }
    });
  }

  // Initialize on document ready
  $(document).ready(function() {
    var config = window.mtrnTablePreviewConfig || {};
    var fieldList = config.fieldList || [];

    // Initialize reusable utilities with preview update callback
    MTRNAdminUtils.initColorPickers(updatePreview);
    MTRNAdminUtils.initOpacitySliders(updatePreview);
    MTRNAdminUtils.initFormFieldListeners('mtrn_', updatePreview);

    // Initialize tournament ID field with group loading
    MTRNAdminUtils.initTournamentIdField('#mtrn_tournament_id', updatePreview, function(tournamentId) {
      MTRNAdminUtils.loadTournamentGroups(tournamentId);
    });

    // Initialize group refresh button
    MTRNAdminUtils.initGroupRefreshButton('#mtrn_refresh_groups', '#mtrn_tournament_id', function(tournamentId, options) {
      MTRNAdminUtils.loadTournamentGroups(tournamentId, options);
    });

    // Load groups on page load if tournament ID exists
    var initialTournamentId = $("#mtrn_tournament_id").val();
    if (initialTournamentId) {
      MTRNAdminUtils.loadTournamentGroups(initialTournamentId, {preserveSelection: false});
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
