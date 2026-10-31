/**
 * Admin shortcode updater
 *
 * Builds the shortcode shown in the "Shortcode Generator" box from the form
 * fields. The option list comes from the PHP registry (mtrnMetaBoxConfig),
 * so the PHP generator and this script produce the same string.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

(function($) {
  'use strict';

  var config = window.mtrnMetaBoxConfig || {};
  var options = config.options || [];

  // Latest size reported by the preview embed (see frontend.js), falling back
  // to the preview iframe's attributes
  function getCurrentIframeDimensions() {
    var dimensions = { width: null, height: null };

    if (window.MTRN_IframeDimensions) {
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

    if (!dimensions.width || !dimensions.height) {
      var previewIframe = $("#mtrn-preview iframe[id^='" + (config.idPrefix || 'mtrn-') + "']").first();
      if (previewIframe.length) {
        dimensions.width = previewIframe.attr('width') || previewIframe.width();
        dimensions.height = previewIframe.attr('height') || previewIframe.height();
      }
    }

    return dimensions;
  }

  // Opacity percentage to a two-digit hex alpha, same integer rounding as PHP
  function opacityToHex(percent) {
    var p = Math.max(0, Math.min(100, Math.round(Number(percent) || 0)));
    var hex = Math.floor((p * 255 + 50) / 100).toString(16);
    return hex.length === 1 ? '0' + hex : hex;
  }

  function fieldValue(id) {
    var $field = $('#' + id);
    return $field.length ? ($field.val() || '') : '';
  }

  function colorValue(id, fallback) {
    return fieldValue(id).replace('#', '') || fallback;
  }

  // Global so the shared utilities and the resize script can call it
  window.updateShortcode = function() {
    var postId = config.postId || 0;
    var dimensions = getCurrentIframeDimensions();
    var width = dimensions.width || config.defaultWidth || '300';
    var height = dimensions.height || config.defaultHeight || '200';

    // Keep the hidden size fields in sync so they are saved with the post
    $('#mtrn_width').val(width);
    $('#mtrn_height').val(height);

    var parts = [
      '[' + (config.shortcode || 'mtrn-table'),
      'id="' + fieldValue('mtrn_tournament_id') + '"',
      'post_id="' + postId + '"',
      'lang="' + (fieldValue('mtrn_language') || 'en') + '"'
    ];

    options.forEach(function(option) {
      var $field = $('#' + option.field);
      var value;

      switch (option.kind) {
        case 'group':
        case 'match_number':
          value = fieldValue(option.field);
          if (value) {
            parts.push(option.attr + '="' + value + '"');
          }
          break;
        case 'participant':
          value = fieldValue(option.field);
          if (value && value !== '-1') {
            parts.push(option.attr + '="' + value + '"');
          }
          break;
        case 'flag':
          if ($field.is(':checked')) {
            parts.push(option.attr + '="1"');
          }
          break;
        case 'wrap':
          if ($field.is(':checked')) {
            parts.push(option.attr + '="true"');
          }
          break;
        case 'style':
          value = option.color ? colorValue(option.field, option.default) : (fieldValue(option.field) || option.default);
          parts.push(option.attr + '="' + value + '"');
          break;
        case 'color_opacity':
          var opacity = fieldValue(option.opacityField);
          if (opacity === '') {
            opacity = option.opacityDefault;
          }
          parts.push(option.attr + '="' + colorValue(option.field, option.colorDefault) + opacityToHex(opacity) + '"');
          break;
      }
    });

    parts.push('width="' + width + '"', 'height="' + height + '"');

    $('#mtrn_shortcode_field').val(parts.join(' ') + ']');
  };

  $(document).ready(function() {
    window.updateShortcode();
  });

})(jQuery);
