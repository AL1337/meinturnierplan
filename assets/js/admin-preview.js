/**
 * Admin live preview
 *
 * Posts the current form values to the preview AJAX action and swaps the
 * preview embed, loads the tournament's groups / participants, and shows the
 * conditional rows (court, group, referee, final-round options) only when
 * the tournament has that feature. Driven by mtrnPreviewConfig.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

(function($) {
  'use strict';

  var config = window.mtrnPreviewConfig || {};
  var options = config.options || [];
  var isMatches = config.type === 'matches';
  var listContext = isMatches ? 'matches' : 'tables';

  function fieldValue(id) {
    var $field = $('#' + id);
    return $field.length ? ($field.val() || '') : '';
  }

  // Same integer rounding as PHP (MTRN_Options::combine_color_opacity)
  function opacityToHex(percent) {
    var p = Math.max(0, Math.min(100, Math.round(Number(percent) || 0)));
    var hex = Math.floor((p * 255 + 50) / 100).toString(16);
    return hex.length === 1 ? '0' + hex : hex;
  }

  function conditionalOptions() {
    return options.filter(function(option) {
      return !!option.condition;
    });
  }

  function rowsFor(condition) {
    return conditionalOptions().filter(function(option) {
      return option.condition === condition;
    }).map(function(option) {
      return $('#' + option.field + '_row');
    });
  }

  function hideConditionalRows() {
    conditionalOptions().forEach(function(option) {
      $('#' + option.field + '_row').hide();
    });
  }

  // A tournament option "applies" when it is true (showCourts, ...) or a list (finalMatches)
  function conditionMet(value) {
    return value === true || (value !== null && typeof value === 'object');
  }

  function checkConditionalFields(tournamentId) {
    var conditional = conditionalOptions();
    if (!conditional.length) {
      return;
    }
    if (!tournamentId) {
      hideConditionalRows();
      return;
    }

    var language = fieldValue('mtrn_language') || 'en';
    var conditions = [];
    conditional.forEach(function(option) {
      if (conditions.indexOf(option.condition) === -1) {
        conditions.push(option.condition);
      }
    });

    conditions.forEach(function(condition) {
      var toggle = function(show) {
        rowsFor(condition).forEach(function($row) {
          if (show) {
            $row.show();
          } else {
            $row.hide();
          }
        });
      };

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: config.checkOptionAction,
          tournament_id: tournamentId,
          language: language,
          option_name: condition,
          nonce: config.checkOptionNonce || ''
        },
        success: function(response) {
          toggle(!!(response.success && response.data && conditionMet(response.data.value)));
        },
        error: function() {
          toggle(false);
        }
      });
    });
  }

  function updatePreview() {
    var data = {
      post_id: config.postId || 0,
      action: config.previewAction,
      nonce: config.previewNonce || ''
    };

    options.forEach(function(option) {
      var $field = $('#' + option.field);

      switch (option.kind) {
        case 'source':
          data.tournament_id = fieldValue(option.field);
          break;
        case 'language':
          data.language = fieldValue(option.field);
          break;
        case 'group':
        case 'participant':
        case 'match_number':
          data[option.key] = fieldValue(option.field);
          break;
        case 'flag':
          // Conditional flags only count while their row is shown
          data[option.key] = ($field.length && (!option.condition || $field.is(':visible')) && $field.is(':checked')) ? '1' : '0';
          break;
        case 'wrap':
          data[option.key] = $field.is(':checked') ? '1' : '0';
          break;
        case 'style':
          data[option.key] = option.color ? fieldValue(option.field).replace('#', '') : fieldValue(option.field);
          break;
        case 'color_opacity':
          data[option.key] = fieldValue(option.field).replace('#', '') + opacityToHex(fieldValue(option.opacityField));
          break;
      }
    });

    $.post(ajaxurl, data, function(response) {
      if (response.success) {
        $('#mtrn-preview').html(response.data);
      }
    });
  }

  function loadLists(tournamentId, loadOptions) {
    MTRNAdminUtils.loadTournamentGroups(tournamentId, $.extend({}, loadOptions || {}, { context: listContext }));
    if (isMatches) {
      MTRNAdminUtils.loadTournamentTeams(tournamentId, $.extend({}, loadOptions || {}));
    }
    checkConditionalFields(tournamentId);
  }

  $(document).ready(function() {
    MTRNAdminUtils.initColorPickers(updatePreview);
    MTRNAdminUtils.initOpacitySliders(updatePreview);
    MTRNAdminUtils.initFormFieldListeners('mtrn_', updatePreview);

    MTRNAdminUtils.initTournamentIdField('#mtrn_tournament_id', updatePreview, function(tournamentId) {
      loadLists(tournamentId);
    });

    MTRNAdminUtils.initGroupRefreshButton('#mtrn_refresh_groups', '#mtrn_tournament_id', function(tournamentId, loadOptions) {
      MTRNAdminUtils.loadTournamentGroups(tournamentId, $.extend({}, loadOptions || {}, { context: listContext }));
    });

    if (isMatches) {
      MTRNAdminUtils.initParticipantRefreshButton('#mtrn_refresh_participants', '#mtrn_tournament_id', function(tournamentId, loadOptions) {
        MTRNAdminUtils.loadTournamentTeams(tournamentId, loadOptions);
      });
    }

    var initialTournamentId = fieldValue('mtrn_tournament_id');
    if (initialTournamentId) {
      loadLists(initialTournamentId, { preserveSelection: false });
    } else {
      hideConditionalRows();
    }

    // Rows can be shown or hidden after load; delegate so their checkboxes still update the preview
    $(document).on('change', 'input[type="checkbox"][id^="mtrn_"]', function() {
      updatePreview();
    });
  });

})(jQuery);
