/**
 * MTP Admin Utilities
 * 
 * Reusable JavaScript functions for admin interfaces
 * 
 * @package MeinTurnierplan
 * @since   1.0.0
 */

(function($) {
  'use strict';

  // MTP Admin Utilities - Reusable JavaScript functions for admin interfaces
  window.MTPAdminUtils = {

    // Initialize color picker fields with consistent behavior
    initColorPickers: function(updateCallback) {
      if (typeof updateCallback !== 'function') {
        updateCallback = function() {}; // Default no-op
      }

      $(".mtp-color-picker").wpColorPicker({
        change: function(event, ui) {
          updateCallback();
        },
        clear: function() {
          updateCallback();
        }
      });

      // Lightweight polling solution for missed changes
      $(".mtp-color-picker").each(function() {
        var input = this;
        var lastValue = input.value;

        setInterval(function() {
          if (input.value !== lastValue) {
            lastValue = input.value;
            updateCallback();
          }
        }, 500);
      });

      // Immediate response for color picker interactions
      $(document).on('click', '.iris-palette', function() {
        var $input = $(this).closest('.wp-picker-container').find('.mtp-color-picker');
        var currentValue = $input.val();

        setTimeout(function() { if ($input.val() !== currentValue) updateCallback(); }, 50);
        setTimeout(function() { if ($input.val() !== currentValue) updateCallback(); }, 100);
        setTimeout(function() { if ($input.val() !== currentValue) updateCallback(); }, 200);
      });
    },

    // Initialize opacity slider handlers
    initOpacitySliders: function(updateCallback) {
      if (typeof updateCallback !== 'function') {
        updateCallback = function() {};
      }

      $("input[type='range']").on("input", function() {
        var fieldId = $(this).attr('id');
        var opacity = $(this).val();
        $("#" + fieldId + "_value").text(opacity + "%");
        updateCallback();
      });
    },

    // Initialize form field change listeners
    initFormFieldListeners: function(fieldSelector, updateCallback) {
      if (typeof updateCallback !== 'function') {
        updateCallback = function() {};
      }

      // Handle checkbox changes
      $("input[type='checkbox'][id^='" + fieldSelector + "']").on("change", function() {
        updateCallback();
      });

      // Handle select dropdown changes
      $("select[id^='" + fieldSelector + "']").on("change", function() {
        updateCallback();
      });

      // Handle input field changes
      $("input[id^='" + fieldSelector + "']").on("input change", function() {
        updateCallback();
      });
    },

    // Show temporary notification messages
    showTemporaryMessage: function(message, type, targetElement) {
      var messageClass = type === 'success' ? 'notice-success' : type === 'error' ? 'notice-error' : 'notice-info';
      var $message = $('<div class="notice ' + messageClass + ' is-dismissible" style="margin: 10px 0;"><p>' + message + '</p></div>');

      if (targetElement) {
        $(targetElement).after($message);
      } else {
        // Default to showing after the first form table
        $('.form-table').first().after($message);
      }

      // Auto-dismiss after 3 seconds
      setTimeout(function() {
        $message.fadeOut(function() {
          $message.remove();
        });
      }, 3000);
    },

    // Load tournament groups with sophisticated error handling and caching
    loadTournamentGroups: function(tournamentId, options) {
      var config = window.mtpAdminUtilsConfig || {};
      var defaults = {
        preserveSelection: false,
        forceRefresh: false,
        context: 'tables', // 'matches' or 'tables' - determines if "All Matches" option is shown
        groupRowSelector: "#" + config.fieldPrefix + "group_field_row",
        groupSelectSelector: "#" + config.fieldPrefix + "group, #" + config.fieldPrefix + "group_select",
        refreshButtonSelector: "#" + config.fieldPrefix + "refresh_groups",
        savedValueSelector: "#" + config.fieldPrefix + "group_saved_value",
        ajaxActions: config.ajaxActions || ['mtp_get_groups', 'mtp_refresh_groups'],
        nonce: config.nonce || ''
      };
      options = $.extend(defaults, options || {});
      var showAllOption = (options.context === 'matches');

      var $groupRow = $(options.groupRowSelector);
      var $groupSelect = $(options.groupSelectSelector);
      var $refreshButton = $(options.refreshButtonSelector);
      var currentSelection = options.preserveSelection ? $groupSelect.val() : '';
      var savedValue = $(options.savedValueSelector).val();

      // Use saved value if no current selection and this isn't a forced refresh
      if (!currentSelection && savedValue && !options.forceRefresh) {
        currentSelection = savedValue;
      }

      if (!tournamentId) {
        $groupRow.hide();
        $groupSelect.prop("disabled", true).empty().append('<option value="">' + (config.i18n?.noGroupsAvailable || 'No groups available') + '</option>');
        $refreshButton.prop("disabled", true);
        return;
      }

      $groupRow.show();
      $groupSelect.prop("disabled", true);
      $refreshButton.prop("disabled", true);

      if (options.forceRefresh) {
        $groupSelect.empty().append('<option value="">' + (config.i18n?.refreshingGroups || 'Refreshing groups...') + '</option>');
        $refreshButton.find('.dashicons').addClass('dashicons-update-alt-rotating');
      } else {
        $groupSelect.empty().append('<option value="">' + (config.i18n?.loadingGroups || 'Loading groups...') + '</option>');
      }

      var ajaxAction = options.forceRefresh ? options.ajaxActions[1] : options.ajaxActions[0];

      $.post(ajaxurl, {
        action: ajaxAction,
        tournament_id: tournamentId,
        force_refresh: options.forceRefresh,
        nonce: options.nonce
      }, function(response) {
        $refreshButton.find('.dashicons').removeClass('dashicons-update-alt-rotating');

        if (response.success && response.data.groups.length > 0) {
          $groupSelect.prop("disabled", false).empty();

          // Add "All Matches" option first (only for matches context)
          if (showAllOption) {
            var isAllSelected = !currentSelection || currentSelection === '';
            $groupSelect.append('<option value=""' + (isAllSelected ? ' selected' : '') + '>' + (config.i18n?.allMatches || 'All Matches') + '</option>');
          }

          $.each(response.data.groups, function(index, group) {
            var groupNumber = index + 1;
            var groupLabel = "Group " + group.displayId;
            var isSelected = false;

            if (currentSelection && currentSelection == groupNumber) {
              isSelected = true;
            } else if (!currentSelection && !showAllOption && index === 0) {
              // Auto-select first group as default for tables (when no "All Matches" option)
              isSelected = true;
            }

            $groupSelect.append('<option value="' + groupNumber + '"' + (isSelected ? ' selected' : '') + '>' + groupLabel + '</option>');
          });

          if (response.data.hasFinalRound) {
            var finalRoundSelected = (currentSelection && currentSelection == '90') ? ' selected' : '';
            $groupSelect.append('<option value="90"' + finalRoundSelected + '>' + (config.i18n?.finalRound || 'Final Round') + '</option>');
          }

          if (currentSelection && $groupSelect.find('option[value="' + currentSelection + '"]').length === 0) {
            $groupSelect.find('option:first').prop('selected', true);
          }

          $refreshButton.prop("disabled", false);

          if (options.forceRefresh && response.data.refreshed) {
            MTPAdminUtils.showTemporaryMessage(config.i18n?.groupsRefreshed || 'Groups refreshed successfully!', "success", options.groupRowSelector);
          }
        } else {
          $groupSelect.prop("disabled", false).empty();

          // For matches: Add "All Matches" option
          // For tables: Show "Default" option
          if (showAllOption) {
            var isAllSelected = !currentSelection || currentSelection === '';
            $groupSelect.append('<option value=""' + (isAllSelected ? ' selected' : '') + '>' + (config.i18n?.allMatches || 'All Matches') + '</option>');
          } else {
            $groupSelect.append('<option value="">' + (config.i18n?.default || 'Default') + '</option>');
          }

          if (response.data.hasFinalRound) {
            var finalRoundSelected = (currentSelection && currentSelection == '90') ? ' selected' : '';
            $groupSelect.append('<option value="90"' + finalRoundSelected + '>' + (config.i18n?.finalRound || 'Final Round') + '</option>');
          }

          if (currentSelection && currentSelection !== '' && currentSelection !== '90' && !response.data.hasFinalRound) {
            $groupSelect.append('<option value="' + currentSelection + '" selected>' + (config.i18n?.group || 'Group') + ' ' + currentSelection + ' ' + (config.i18n?.saved || '(saved)') + '</option>');
          }

          $refreshButton.prop("disabled", false);

          if (options.forceRefresh) {
            MTPAdminUtils.showTemporaryMessage(config.i18n?.noGroupsFound || 'No groups found for this tournament.', "info", options.groupRowSelector);
          }
        }
      }).fail(function() {
        $refreshButton.find('.dashicons').removeClass('dashicons-update-alt-rotating');
        $groupSelect.prop("disabled", false).empty();

        // For matches: Add "All Matches" option
        // For tables: Show "Default" option
        if (showAllOption) {
          var isAllSelected = !currentSelection || currentSelection === '';
          $groupSelect.append('<option value=""' + (isAllSelected ? ' selected' : '') + '>' + (config.i18n?.allMatches || 'All Matches') + '</option>');
        } else {
          $groupSelect.append('<option value="">' + (config.i18n?.default || 'Default') + '</option>');
        }

        if (currentSelection && currentSelection !== '') {
          var label = currentSelection == '90' ? (config.i18n?.finalRoundSaved || 'Final Round (saved)') : (config.i18n?.group || 'Group') + ' ' + currentSelection + ' ' + (config.i18n?.saved || '(saved)');
          $groupSelect.append('<option value="' + currentSelection + '" selected>' + label + '</option>');
        }
        
        $refreshButton.prop("disabled", false);

        if (options.forceRefresh) {
          MTPAdminUtils.showTemporaryMessage(config.i18n?.errorRefreshing || 'Error refreshing groups. Please try again.', "error", options.groupRowSelector);
        }
      });
    },

    // Initialize tournament ID field with group loading
    initTournamentIdField: function(tournamentIdSelector, previewCallback, groupLoadCallback) {
      var tournamentIdChangeTimeout;
      var previewUpdateTimeout;

      $(tournamentIdSelector).on("input", function() {
        var tournamentId = $(this).val();

        clearTimeout(tournamentIdChangeTimeout);
        clearTimeout(previewUpdateTimeout);

        // Load groups after user stops typing for 500ms
        if (groupLoadCallback) {
          tournamentIdChangeTimeout = setTimeout(function() {
            groupLoadCallback(tournamentId);
          }, 500);
        }

        // Update preview after user stops typing for 300ms (faster than group loading)
        if (previewCallback) {
          previewUpdateTimeout = setTimeout(function() {
            previewCallback();
          }, 300);
        }
      });
    },

    // Initialize group refresh button
    initGroupRefreshButton: function(refreshButtonSelector, tournamentIdSelector, groupLoadCallback) {
      $(document).on("click", refreshButtonSelector, function(e) {
        e.preventDefault();
        var tournamentId = $(tournamentIdSelector).val();
        if (tournamentId && groupLoadCallback) {
          groupLoadCallback(tournamentId, {preserveSelection: true, forceRefresh: true});
        }
      });
    },

    // Load tournament teams (participants)
    loadTournamentTeams: function(tournamentId, options) {
      var config = window.mtpAdminUtilsConfig || {};
      var defaults = {
        preserveSelection: false,
        forceRefresh: false,
        participantSelectSelector: "#" + config.fieldPrefix + "participant",
        refreshButtonSelector: "#" + config.fieldPrefix + "refresh_participants",
        savedValueSelector: "#" + config.fieldPrefix + "participant_saved_value",
        ajaxActions: config.ajaxActionsTeams || ['mtp_get_teams', 'mtp_refresh_teams'],
        nonce: config.nonce || ''
      };
      options = $.extend(defaults, options || {});

      var $participantSelect = $(options.participantSelectSelector);
      var $refreshButton = $(options.refreshButtonSelector);
      var currentSelection = options.preserveSelection ? $participantSelect.val() : '';
      var savedValue = $(options.savedValueSelector).val();

      // Use saved value if no current selection and this isn't a forced refresh
      if (!currentSelection && savedValue && !options.forceRefresh) {
        currentSelection = savedValue;
      }

      if (!tournamentId) {
        $participantSelect.prop("disabled", false).empty().append('<option value="-1">' + (config.i18n?.all || 'All') + '</option>');
        $refreshButton.prop("disabled", true);
        return;
      }

      $participantSelect.prop("disabled", true);
      $refreshButton.prop("disabled", true);

      if (options.forceRefresh) {
        $participantSelect.empty().append('<option value="-1">' + (config.i18n?.refreshingParticipants || 'Refreshing participants...') + '</option>');
        $refreshButton.find('.dashicons').addClass('dashicons-update-alt-rotating');
      } else {
        $participantSelect.empty().append('<option value="-1">' + (config.i18n?.loadingParticipants || 'Loading participants...') + '</option>');
      }

      var ajaxAction = options.forceRefresh ? options.ajaxActions[1] : options.ajaxActions[0];

      $.post(ajaxurl, {
        action: ajaxAction,
        tournament_id: tournamentId,
        force_refresh: options.forceRefresh,
        nonce: options.nonce
      }, function(response) {
        $refreshButton.find('.dashicons').removeClass('dashicons-update-alt-rotating');

        if (response.success && response.data.teams && response.data.teams.length > 0) {
          $participantSelect.prop("disabled", false).empty();

          // Add "All" option first as default
          var isAllSelected = !currentSelection || currentSelection === '-1';
          $participantSelect.append('<option value="-1"' + (isAllSelected ? ' selected' : '') + '>' + (config.i18n?.all || 'All') + '</option>');

          $.each(response.data.teams, function(index, team) {
            var teamId = team.displayId || '';
            var teamName = team.name || '';

            if (teamId && teamName) {
              var isSelected = (currentSelection && currentSelection == teamId);
              $participantSelect.append('<option value="' + teamId + '"' + (isSelected ? ' selected' : '') + '>' + teamName + '</option>');
            }
          });

          if (currentSelection && currentSelection !== '-1' && $participantSelect.find('option[value="' + currentSelection + '"]').length === 0) {
            $participantSelect.find('option:first').prop('selected', true);
          }

          $refreshButton.prop("disabled", false);

          if (options.forceRefresh && response.data.refreshed) {
            MTPAdminUtils.showTemporaryMessage(config.i18n?.participantsRefreshed || 'Participants refreshed successfully!', "success", options.participantSelectSelector);
          }
        } else {
          $participantSelect.prop("disabled", false).empty();
          $participantSelect.append('<option value="-1"' + (!currentSelection || currentSelection === '-1' ? ' selected' : '') + '>' + (config.i18n?.all || 'All') + '</option>');

          if (currentSelection && currentSelection !== '-1') {
            $participantSelect.append('<option value="' + currentSelection + '" selected>' + (config.i18n?.team || 'Team') + ' ' + currentSelection + ' ' + (config.i18n?.saved || '(saved)') + '</option>');
          }

          $refreshButton.prop("disabled", false);

          if (options.forceRefresh) {
            MTPAdminUtils.showTemporaryMessage(config.i18n?.noParticipantsFound || 'No participants found for this tournament.', "info", options.participantSelectSelector);
          }
        }
      }).fail(function() {
        $refreshButton.find('.dashicons').removeClass('dashicons-update-alt-rotating');
        $participantSelect.prop("disabled", false).empty();
        $participantSelect.append('<option value="-1"' + (!currentSelection || currentSelection === '-1' ? ' selected' : '') + '>' + (config.i18n?.all || 'All') + '</option>');

        if (currentSelection && currentSelection !== '-1') {
          $participantSelect.append('<option value="' + currentSelection + '" selected>' + (config.i18n?.team || 'Team') + ' ' + currentSelection + ' ' + (config.i18n?.saved || '(saved)') + '</option>');
        }

        $refreshButton.prop("disabled", false);

        if (options.forceRefresh) {
          MTPAdminUtils.showTemporaryMessage(config.i18n?.errorRefreshingParticipants || 'Error refreshing participants. Please try again.', "error", options.participantSelectSelector);
        }
      });
    },

    // Initialize participant refresh button
    initParticipantRefreshButton: function(refreshButtonSelector, tournamentIdSelector, teamLoadCallback) {
      $(document).on("click", refreshButtonSelector, function(e) {
        e.preventDefault();
        var tournamentId = $(tournamentIdSelector).val();
        if (tournamentId && teamLoadCallback) {
          teamLoadCallback(tournamentId, {preserveSelection: true, forceRefresh: true});
        }
      });
    }
  };

})(jQuery);
