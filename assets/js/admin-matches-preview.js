/**
 * Admin Matches Preview JavaScript
 *
 * @package MeinTurnierplan
 * @since   1.0.0
 * @version 1.1.0
 */

(function($) {
  'use strict';

  // Function to check tournament data and show/hide conditional fields
  function checkConditionalFields(tournamentId) {
    var config = window.mtrnMatchesPreviewConfig || {};
    var nonce = config.checkOptionNonce || '';
    var selectedLanguage = $('#mtrn_language').val() || 'en';

    if (!tournamentId) {
      // Hide all conditional fields if no tournament ID
      $('#mtrn_sf_row').hide();
      $('#mtrn_sg_row').hide();
      $('#mtrn_sr_row').hide();
      $('#mtrn_se_row').hide();
      $('#mtrn_sp_row').hide();
      $('#mtrn_sh_row').hide();
      return;
    }

    // Fetch tournament data for showCourts via WordPress AJAX to avoid CORS issues
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'mtrn_check_tournament_option',
        tournament_id: tournamentId,
        language: selectedLanguage,
        option_name: 'showCourts',
        nonce: nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          var showCourtsValue = response.data.value;

          // Show/hide Suppress Court field based on showCourts
          if (showCourtsValue === true) {
            $('#mtrn_sf_row').show();
          } else {
            $('#mtrn_sf_row').hide();
          }
        } else {
          $('#mtrn_sf_row').hide();
        }
      },
      error: function(xhr, status, error) {
        // On error, hide the field
        $('#mtrn_sf_row').hide();
      }
    });

    // Fetch tournament data for showGroups via WordPress AJAX to avoid CORS issues
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'mtrn_check_tournament_option',
        tournament_id: tournamentId,
        language: selectedLanguage,
        option_name: 'showGroups',
        nonce: nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          var showGroupsValue = response.data.value;

          // Show/hide Suppress Group field based on showGroups
          if (showGroupsValue === true) {
            $('#mtrn_sg_row').show();
          } else {
            $('#mtrn_sg_row').hide();
          }
        } else {
          $('#mtrn_sg_row').hide();
        }
      },
      error: function(xhr, status, error) {
        // On error, hide the field
        $('#mtrn_sg_row').hide();
      }
    });

    // Fetch tournament data for showReferees via WordPress AJAX to avoid CORS issues
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'mtrn_check_tournament_option',
        tournament_id: tournamentId,
        language: selectedLanguage,
        option_name: 'showReferees',
        nonce: nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          var showRefereesValue = response.data.value;

          // Show/hide Suppress Referee field based on showReferees
          if (showRefereesValue === true) {
            $('#mtrn_sr_row').show();
          } else {
            $('#mtrn_sr_row').hide();
          }
        } else {
          $('#mtrn_sr_row').hide();
        }
      },
      error: function(xhr, status, error) {
        // On error, hide the field
        $('#mtrn_sr_row').hide();
      }
    });

    // Fetch tournament data for finalMatches via WordPress AJAX to avoid CORS issues
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'mtrn_check_tournament_option',
        tournament_id: tournamentId,
        language: selectedLanguage,
        option_name: 'finalMatches',
        nonce: nonce
      },
      success: function(response) {
        if (response.success && response.data) {
          var finalMatchesValue = response.data.value;

          // Show Suppress Extra Time, Suppress Penalties, and Suppress Headlines fields if finalMatches exists
          if (finalMatchesValue !== null && finalMatchesValue !== undefined) {
            $('#mtrn_se_row').show();
            $('#mtrn_sp_row').show();
            $('#mtrn_sh_row').show();
          } else {
            $('#mtrn_se_row').hide();
            $('#mtrn_sp_row').hide();
            $('#mtrn_sh_row').hide();
          }
        } else {
          $('#mtrn_se_row').hide();
          $('#mtrn_sp_row').hide();
          $('#mtrn_sh_row').hide();
        }
      },
      error: function(xhr, status, error) {
        // On error, hide the fields
        $('#mtrn_se_row').hide();
        $('#mtrn_sp_row').hide();
        $('#mtrn_sh_row').hide();
      }
    });
  }

  // Function to update preview
  function updatePreview() {
    var config = window.mtrnMatchesPreviewConfig || {};
    var postId = config.postId || 0;
    var previewNonce = config.previewNonce || '';

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
      ehrsize: $("#mtrn_ehrsize").val(),
      ehrtop: $("#mtrn_ehrtop").val(),
      ehrbottom: $("#mtrn_ehrbottom").val(),
      table_padding: $("#mtrn_table_padding").val(),
      inner_padding: $("#mtrn_inner_padding").val(),
      text_color: $("#mtrn_text_color").val().replace("#", ""),
      main_color: $("#mtrn_main_color").val().replace("#", ""),
      bg_color: $("#mtrn_bg_color").val().replace("#", ""),
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
      projector_presentation: $("#mtrn_projector_presentation").is(":checked") ? "1" : "0",
      si: $("#mtrn_si").is(":checked") ? "1" : "0",
      sf: ($("#mtrn_sf").length && $("#mtrn_sf").is(":visible") && $("#mtrn_sf").is(":checked")) ? "1" : "0",
      st: $("#mtrn_st").is(":checked") ? "1" : "0",
      sg: ($("#mtrn_sg").length && $("#mtrn_sg").is(":visible") && $("#mtrn_sg").is(":checked")) ? "1" : "0",
      sr: ($("#mtrn_sr").length && $("#mtrn_sr").is(":visible") && $("#mtrn_sr").is(":checked")) ? "1" : "0",
      se: ($("#mtrn_se").length && $("#mtrn_se").is(":visible") && $("#mtrn_se").is(":checked")) ? "1" : "0",
      sp: ($("#mtrn_sp").length && $("#mtrn_sp").is(":visible") && $("#mtrn_sp").is(":checked")) ? "1" : "0",
      sh: ($("#mtrn_sh").length && $("#mtrn_sh").is(":visible") && $("#mtrn_sh").is(":checked")) ? "1" : "0",
      responsive: $("#mtrn_responsive").is(":checked") ? "1" : "0",
      language: $("#mtrn_language").val(),
      group: $("#mtrn_group").val(),
      participant: $("#mtrn_participant").val(),
      match_number: $("#mtrn_match_number").val(),
      action: "mtrn_preview_matches",
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
    // Initialize reusable utilities with preview update callback
    MTRNAdminUtils.initColorPickers(updatePreview);
    MTRNAdminUtils.initOpacitySliders(updatePreview);
    MTRNAdminUtils.initFormFieldListeners('mtrn_', updatePreview);

    // Initialize tournament ID field with group and team loading
    MTRNAdminUtils.initTournamentIdField('#mtrn_tournament_id', updatePreview, function(tournamentId) {
      MTRNAdminUtils.loadTournamentGroups(tournamentId, {context: 'matches'});
      MTRNAdminUtils.loadTournamentTeams(tournamentId);
      // Check tournament data for conditional fields
      checkConditionalFields(tournamentId);
    });

    // Initialize group refresh button
    MTRNAdminUtils.initGroupRefreshButton('#mtrn_refresh_groups', '#mtrn_tournament_id', function(tournamentId, options) {
      options = options || {};
      options.context = 'matches';
      MTRNAdminUtils.loadTournamentGroups(tournamentId, options);
    });

    // Initialize participant refresh button
    MTRNAdminUtils.initParticipantRefreshButton('#mtrn_refresh_participants', '#mtrn_tournament_id', function(tournamentId, options) {
      MTRNAdminUtils.loadTournamentTeams(tournamentId, options);
    });

    // Load groups and teams on page load if tournament ID exists
    var initialTournamentId = $("#mtrn_tournament_id").val();

    if (initialTournamentId) {
      MTRNAdminUtils.loadTournamentGroups(initialTournamentId, {preserveSelection: false, context: 'matches'});
      MTRNAdminUtils.loadTournamentTeams(initialTournamentId, {preserveSelection: false});
      checkConditionalFields(initialTournamentId);
    } else {
      // Hide conditional fields if no tournament ID on load
      $('#mtrn_sf_row').hide();
      $('#mtrn_sg_row').hide();
      $('#mtrn_sr_row').hide();
      $('#mtrn_se_row').hide();
      $('#mtrn_sp_row').hide();
      $('#mtrn_sh_row').hide();
    }

    // Additional explicit listeners for checkboxes to ensure they work even when dynamically shown/hidden
    // Use event delegation to handle dynamically shown fields
    $(document).on('change', 'input[type="checkbox"][id^="mtrn_"]', function() {
      updatePreview();
    });
  });

})(jQuery);
