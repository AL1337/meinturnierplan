<?php
/**
 * Admin Utilities Class
 *
 * @package MeinTurnierplan
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Admin Utilities Class
 *
 * Provides reusable utility functions for admin interfaces
 */
class MTP_Admin_Utilities {

  /**
   * Render a group header for admin forms
   *
   * @param string $title The title text for the group header
   * @param string $css_class Optional. Additional CSS class for the header. Default 'mtp-group-header'.
   */
  public static function render_group_header($title, $css_class = 'mtp-group-header') {
    echo '<tr>';
    echo '<td class="mtp-group-header-wrapper" colspan="2">';
    echo '<h4 class="' . esc_attr($css_class) . '">' . esc_html($title) . '</h4>';
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a text field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param string $value The field value
   * @param string $description Optional. Field description text
   * @param array $attributes Optional. Additional HTML attributes
   */
  public static function render_text_field($field_name, $label, $value, $description = '', $attributes = array()) {
    $default_attributes = array(
      'type' => 'text',
      'id' => $field_name,
      'name' => $field_name,
      'value' => $value,
      'class' => 'regular-text'
    );

    $attributes = array_merge($default_attributes, $attributes);

    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input';
    foreach ($attributes as $attr_name => $attr_value) {
      echo ' ' . esc_attr($attr_name) . '="' . esc_attr($attr_value) . '"';
    }
    echo ' />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a number field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param mixed $value The field value
   * @param string $description Optional. Field description text
   * @param int|null $min Optional. Minimum value
   * @param int|null $max Optional. Maximum value
   * @param int $step Optional. Step value. Default 1.
   */
  public static function render_number_field($field_name, $label, $value, $description = '', $min = null, $max = null, $step = 1) {
    $attributes = array(
      'type' => 'number',
      'step' => $step
    );

    if ($min !== null) {
      $attributes['min'] = $min;
    }
    if ($max !== null) {
      $attributes['max'] = $max;
    }

    self::render_text_field($field_name, $label, $value, $description, $attributes);
  }

  /**
   * Render a checkbox field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param mixed $value The field value (1 for checked, 0 for unchecked)
   * @param string $description Optional. Field description text
   */
  public static function render_checkbox_field($field_name, $label, $value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="checkbox" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" value="1"' . checked(1, $value, false) . ' />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a select field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param mixed $value The selected value
   * @param array $options Array of option value => label pairs
   * @param string $description Optional. Field description text
   */
  public static function render_select_field($field_name, $label, $value, $options, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<select id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" class="regular-text">';
    foreach ($options as $option_value => $option_label) {
      echo '<option value="' . esc_attr($option_value) . '"' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
    }
    echo '</select>';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a color field for admin forms
   *
   * @param string $field_name The field name/ID
   * @param string $label The field label
   * @param string $value The color value (without #)
   * @param string $description Optional. Field description text
   */
  public static function render_color_field($field_name, $label, $value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($field_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input type="text" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" value="#' . esc_attr($value) . '" class="mtp-color-picker" />';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Render a color field with opacity slider for admin forms
   *
   * @param string $color_field The color field name/ID
   * @param string $opacity_field The opacity field name/ID
   * @param string $label The field label
   * @param string $color_value The color value (without #)
   * @param int $opacity_value The opacity value (0-100)
   * @param string $description Optional. Field description text
   */
  public static function render_color_opacity_field($color_field, $opacity_field, $label, $color_value, $opacity_value, $description = '') {
    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($color_field) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 15px;">';
    echo '<input type="text" id="' . esc_attr($color_field) . '" name="' . esc_attr($color_field) . '" value="#' . esc_attr($color_value) . '" class="mtp-color-picker" style="width: 120px;" />';
    echo '<div style="display: flex; align-items: center; gap: 8px;">';
    echo '<label for="' . esc_attr($opacity_field) . '" style="margin: 0; font-weight: normal;">' . __('Opacity:', 'meinturnierplan') . '</label>';
    echo '<input type="range" id="' . esc_attr($opacity_field) . '" name="' . esc_attr($opacity_field) . '" value="' . esc_attr($opacity_value) . '" min="0" max="100" step="1" style="width: 100px;" />';
    echo '<span id="' . esc_attr($opacity_field) . '_value" style="min-width: 35px; font-size: 12px; color: #666;">' . esc_attr($opacity_value) . '%</span>';
    echo '</div>';
    echo '</div>';
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  /**
   * Sanitize meta value based on field type
   *
   * @param string $field The field name/type
   * @param mixed $value The value to sanitize
   * @return mixed The sanitized value
   */
  public static function sanitize_meta_value($field, $value) {
    // Checkbox fields
    if (in_array($field, array('suppress_wins', 'suppress_logos', 'suppress_num_matches', 'projector_presentation', 'navigation_for_groups'))) {
      return $value === '1' ? '1' : '0';
    }

    // Color fields
    if (in_array($field, array('text_color', 'main_color', 'bg_color', 'border_color', 'head_bottom_border_color', 'even_bg_color', 'odd_bg_color', 'hover_bg_color', 'head_bg_color'))) {
      $color = sanitize_hex_color($value);
      return $color ? ltrim($color, '#') : '';
    }

    // Opacity fields
    if (strpos($field, '_opacity') !== false) {
      $opacity = absint($value);
      return max(0, min(100, $opacity));
    }

    // Number fields
    if (in_array($field, array('font_size', 'header_font_size', 'bsizeh', 'bsizev', 'bsizeoh', 'bsizeov', 'bbsize', 'table_padding', 'inner_padding', 'logo_size'))) {
      return absint($value);
    }

    // Language field - validate against allowed languages
    if ($field === 'language') {
      $allowed_languages = array('en', 'de', 'es', 'fr', 'hr', 'it', 'pl', 'sl', 'tr');
      return in_array($value, $allowed_languages) ? $value : 'en';
    }

    // Text fields
    return sanitize_text_field($value);
  }

  /**
   * Combine color and opacity into a single hex value
   *
   * @param string $color The color value (without #)
   * @param int $opacity The opacity value (0-100)
   * @return string The combined color value with opacity
   */
  public static function combine_color_opacity($color, $opacity) {
    if ($opacity !== '' && $opacity !== null) {
      $opacity_hex = str_pad(dechex(round(($opacity / 100) * 255)), 2, '0', STR_PAD_LEFT);
      return $color . $opacity_hex;
    }
    return $color;
  }

  /**
   * Get available language options for the plugin
   *
   * @return array Array of language code => language name pairs
   */
  public static function get_language_options() {
    return array(
      'en' => __('English', 'meinturnierplan'),
      'de' => __('Deutsch / German', 'meinturnierplan'),
      'es' => __('Español / Spanish', 'meinturnierplan'),
      'fr' => __('Français / French', 'meinturnierplan'),
      'hr' => __('Hrvatski / Croatian', 'meinturnierplan'),
      'it' => __('Italiano / Italian', 'meinturnierplan'),
      'pl' => __('Polski / Polish', 'meinturnierplan'),
      'sl' => __('Slovenščina / Slovenian', 'meinturnierplan'),
      'tr' => __('Türkçe / Turkish', 'meinturnierplan'),
    );
  }

  /**
   * Get default language based on WordPress locale
   *
   * @return string The default language code
   */
  public static function get_default_language() {
    // First, try to get the current user's language preference
    $user_locale = '';
    if (is_user_logged_in()) {
      $user_id = get_current_user_id();
      $user_locale = get_user_meta($user_id, 'locale', true);
    }

    // Use user locale if available, otherwise fall back to site locale
    $wp_locale = !empty($user_locale) ? $user_locale : get_locale();

    // Define supported languages with their WordPress locale mappings
    $supported_languages = array(
      'en' => array('en_US', 'en_GB', 'en_CA', 'en_AU', 'en_NZ', 'en_ZA'),
      'de' => array('de_DE', 'de_AT', 'de_CH', 'de_DE_formal'),
      'es' => array('es_ES', 'es_MX', 'es_AR', 'es_CL', 'es_CO', 'es_PE', 'es_VE'),
      'fr' => array('fr_FR', 'fr_BE', 'fr_CA', 'fr_CH'),
      'hr' => array('hr', 'hr_HR'),
      'it' => array('it_IT'),
      'pl' => array('pl_PL'),
      'sl' => array('sl_SI'),
      'tr' => array('tr_TR'),
    );

    // Check if current locale matches any supported language
    foreach ($supported_languages as $lang_code => $locales) {
      if (in_array($wp_locale, $locales)) {
        return $lang_code;
      }
    }

    // Check for partial matches (e.g., 'de' from 'de_DE_formal')
    $wp_lang_code = substr($wp_locale, 0, 2);
    if (array_key_exists($wp_lang_code, $supported_languages)) {
      return $wp_lang_code;
    }

    // Default to English if no match found
    return 'en';
  }

  /**
   * Fetch tournament groups from external API
   *
   * @param string $tournament_id The tournament ID
   * @param bool $force_refresh Whether to force refresh the cache
   * @return array Array containing groups and hasFinalRound data
   */
  public static function fetch_tournament_groups($tournament_id, $force_refresh = false) {
    if (empty($tournament_id)) {
      return array('groups' => array(), 'hasFinalRound' => false);
    }

    $cache_key = 'mtp_groups_' . $tournament_id;
    $cache_expiry = 15 * MINUTE_IN_SECONDS; // Cache for 15 minutes

    // Try to get cached data first (unless force refresh is requested)
    if (!$force_refresh) {
      $cached_data = get_transient($cache_key);
      if ($cached_data !== false) {
        // Handle backwards compatibility - if cached data is old format (just array of groups)
        if (is_array($cached_data) && !isset($cached_data['groups'])) {
          // Old format - convert to new format
          return array(
            'groups' => $cached_data,
            'hasFinalRound' => false
          );
        }
        return $cached_data;
      }
    } else {
      // Force refresh - clear the cache first
      delete_transient($cache_key);
    }

    // Use WordPress HTTP API to fetch the JSON
    $url = 'https://tournej.com/json/json.php?id=' . urlencode($tournament_id);
    $response = wp_remote_get($url, array(
      'timeout' => 10,
      'sslverify' => true
    ));

    // Check for errors
    if (is_wp_error($response)) {
      // Return cached data if available, even if expired
      $cached_data = get_transient($cache_key);
      if ($cached_data !== false) {
        // Handle backwards compatibility
        if (is_array($cached_data) && !isset($cached_data['groups'])) {
          return array(
            'groups' => $cached_data,
            'hasFinalRound' => false
          );
        }
        return $cached_data;
      }
      return array('groups' => array(), 'hasFinalRound' => false);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $groups = array();
    $has_final_round = false;

    // Check if groups exist and are not empty
    if (isset($data['groups']) && is_array($data['groups']) && !empty($data['groups'])) {
      $groups = $data['groups'];
    }

    // Check if finalRankTable exists and has valid final ranking data
    if (isset($data['finalRankTable']) && is_array($data['finalRankTable']) && count($data['finalRankTable']) > 0) {
      // Verify it contains valid ranking objects with rank and teamId
      $first_entry = $data['finalRankTable'][0];
      if (is_array($first_entry) && isset($first_entry['rank']) && isset($first_entry['teamId'])) {
        $has_final_round = true;
      }
    }

    // Cache the result (even if empty)
    $result = array(
      'groups' => $groups,
      'hasFinalRound' => $has_final_round
    );
    set_transient($cache_key, $result, $cache_expiry);

    return $result;
  }

  /**
   * Render JavaScript utilities for admin forms
   *
   * This generates common JavaScript functions that can be reused across different
   * admin interfaces for consistent behavior with color pickers, opacity sliders,
   * tournament group loading, etc.
   *
   * @param array $config Optional configuration array with keys:
   *   - 'nonce_action': The nonce action name for AJAX calls (default: 'mtp_preview_nonce')
   *   - 'ajax_actions': Array of AJAX action names (default: ['mtp_get_groups', 'mtp_refresh_groups'])
   *   - 'field_prefix': Prefix for field IDs (default: 'mtp_')
   */
  public static function render_admin_javascript_utilities($config = array()) {
    $defaults = array(
      'nonce_action' => 'mtp_preview_nonce',
      'ajax_actions' => array('mtp_get_groups', 'mtp_refresh_groups'),
      'field_prefix' => 'mtp_'
    );
    $config = array_merge($defaults, $config);
    ?>
    <script>
    // MTP Admin Utilities - Reusable JavaScript functions for admin interfaces
    if (typeof MTPAdminUtils === 'undefined') {
      window.MTPAdminUtils = {

        // Initialize color picker fields with consistent behavior
        initColorPickers: function(updateCallback) {
          if (typeof updateCallback !== 'function') {
            updateCallback = function() {}; // Default no-op
          }

          jQuery(".mtp-color-picker").wpColorPicker({
            change: function(event, ui) {
              updateCallback();
            },
            clear: function() {
              updateCallback();
            }
          });

          // Lightweight polling solution for missed changes
          jQuery(".mtp-color-picker").each(function() {
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
          jQuery(document).on('click', '.iris-palette', function() {
            var $input = jQuery(this).closest('.wp-picker-container').find('.mtp-color-picker');
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

          jQuery("input[type='range']").on("input", function() {
            var fieldId = jQuery(this).attr('id');
            var opacity = jQuery(this).val();
            jQuery("#" + fieldId + "_value").text(opacity + "%");
            updateCallback();
          });
        },

        // Initialize form field change listeners
        initFormFieldListeners: function(fieldSelector, updateCallback) {
          if (typeof updateCallback !== 'function') {
            updateCallback = function() {};
          }

          // Handle checkbox changes
          jQuery("input[type='checkbox'][id^='" + fieldSelector + "']").on("change", function() {
            updateCallback();
          });

          // Handle select dropdown changes
          jQuery("select[id^='" + fieldSelector + "']").on("change", function() {
            updateCallback();
          });

          // Handle input field changes
          jQuery("input[id^='" + fieldSelector + "']").on("input change", function() {
            updateCallback();
          });
        },

        // Show temporary notification messages
        showTemporaryMessage: function(message, type, targetElement) {
          var messageClass = type === 'success' ? 'notice-success' : type === 'error' ? 'notice-error' : 'notice-info';
          var $message = jQuery('<div class="notice ' + messageClass + ' is-dismissible" style="margin: 10px 0;"><p>' + message + '</p></div>');

          if (targetElement) {
            jQuery(targetElement).after($message);
          } else {
            // Default to showing after the first form table
            jQuery('.form-table').first().after($message);
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
          var defaults = {
            preserveSelection: false,
            forceRefresh: false,
            groupRowSelector: "#<?php echo esc_js($config['field_prefix']); ?>group_field_row",
            groupSelectSelector: "#<?php echo esc_js($config['field_prefix']); ?>group, #<?php echo esc_js($config['field_prefix']); ?>group_select",
            refreshButtonSelector: "#<?php echo esc_js($config['field_prefix']); ?>refresh_groups",
            savedValueSelector: "#<?php echo esc_js($config['field_prefix']); ?>group_saved_value",
            ajaxActions: <?php echo json_encode($config['ajax_actions']); ?>,
            nonce: "<?php echo wp_create_nonce($config['nonce_action']); ?>"
          };
          options = jQuery.extend(defaults, options || {});

          var $groupRow = jQuery(options.groupRowSelector);
          var $groupSelect = jQuery(options.groupSelectSelector);
          var $refreshButton = jQuery(options.refreshButtonSelector);
          var currentSelection = options.preserveSelection ? $groupSelect.val() : '';
          var savedValue = jQuery(options.savedValueSelector).val();

          // Use saved value if no current selection and this isn't a forced refresh
          if (!currentSelection && savedValue && !options.forceRefresh) {
            currentSelection = savedValue;
          }

          if (!tournamentId) {
            $groupRow.hide();
            $groupSelect.prop("disabled", true).empty().append('<option value="">No groups available</option>');
            $refreshButton.prop("disabled", true);
            return;
          }

          $groupRow.show();
          $groupSelect.prop("disabled", true);
          $refreshButton.prop("disabled", true);

          if (options.forceRefresh) {
            $groupSelect.empty().append('<option value="">Refreshing groups...</option>');
            $refreshButton.find('.dashicons').addClass('dashicons-update-alt-rotating');
          } else {
            $groupSelect.empty().append('<option value="">Loading groups...</option>');
          }

          var ajaxAction = options.forceRefresh ? options.ajaxActions[1] : options.ajaxActions[0];

          jQuery.post(ajaxurl, {
            action: ajaxAction,
            tournament_id: tournamentId,
            force_refresh: options.forceRefresh,
            nonce: options.nonce
          }, function(response) {
            $refreshButton.find('.dashicons').removeClass('dashicons-update-alt-rotating');

            if (response.success && response.data.groups.length > 0) {
              $groupSelect.prop("disabled", false).empty();

              jQuery.each(response.data.groups, function(index, group) {
                var groupNumber = index + 1;
                var groupLabel = "Group " + group.displayId;
                var isSelected = false;

                if (!currentSelection && index === 0) {
                  isSelected = true;
                } else if (currentSelection && currentSelection == groupNumber) {
                  isSelected = true;
                }

                $groupSelect.append('<option value="' + groupNumber + '"' + (isSelected ? ' selected' : '') + '>' + groupLabel + '</option>');
              });

              if (response.data.hasFinalRound) {
                var finalRoundSelected = (currentSelection && currentSelection == '90') ? ' selected' : '';
                $groupSelect.append('<option value="90"' + finalRoundSelected + '>Final Round</option>');
              }

              if (currentSelection && $groupSelect.find('option[value="' + currentSelection + '"]').length === 0) {
                $groupSelect.find('option:first').prop('selected', true);
              }

              $refreshButton.prop("disabled", false);

              if (options.forceRefresh && response.data.refreshed) {
                MTPAdminUtils.showTemporaryMessage("Groups refreshed successfully!", "success", options.groupRowSelector);
              }
            } else {
              $groupSelect.prop("disabled", false).empty();

              if (response.data.hasFinalRound) {
                var finalRoundSelected = (currentSelection && currentSelection == '90') ? ' selected' : '';
                $groupSelect.append('<option value="90"' + finalRoundSelected + '>Final Round</option>');
              } else {
                $groupSelect.append('<option value="">Default</option>');
              }

              if (currentSelection && currentSelection !== '' && currentSelection !== '90' && !response.data.hasFinalRound) {
                $groupSelect.append('<option value="' + currentSelection + '" selected>Group ' + currentSelection + ' (saved)</option>');
              }

              $refreshButton.prop("disabled", false);

              if (options.forceRefresh) {
                MTPAdminUtils.showTemporaryMessage("No groups found for this tournament.", "info", options.groupRowSelector);
              }
            }
          }).fail(function() {
            $refreshButton.find('.dashicons').removeClass('dashicons-update-alt-rotating');
            $groupSelect.prop("disabled", false).empty();
            $groupSelect.append('<option value="">Default</option>');

            if (currentSelection && currentSelection !== '') {
              var label = currentSelection == '90' ? 'Final Round (saved)' : 'Group ' + currentSelection + ' (saved)';
              $groupSelect.append('<option value="' + currentSelection + '" selected>' + label + '</option>');
            }

            $refreshButton.prop("disabled", false);

            if (options.forceRefresh) {
              MTPAdminUtils.showTemporaryMessage("Error refreshing groups. Please try again.", "error", options.groupRowSelector);
            }
          });
        },

        // Initialize tournament ID field with group loading
        initTournamentIdField: function(tournamentIdSelector, previewCallback, groupLoadCallback) {
          var tournamentIdChangeTimeout;
          var previewUpdateTimeout;

          jQuery(tournamentIdSelector).on("input", function() {
            var tournamentId = jQuery(this).val();

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
          jQuery(document).on("click", refreshButtonSelector, function(e) {
            e.preventDefault();
            var tournamentId = jQuery(tournamentIdSelector).val();
            if (tournamentId && groupLoadCallback) {
              groupLoadCallback(tournamentId, {preserveSelection: true, forceRefresh: true});
            }
          });
        }
      };
    }
    </script>
    <?php
  }

  /**
   * Render shortcode generator interface
   *
   * This creates a reusable shortcode generator UI with copy functionality,
   * success messaging, and optional warning messages.
   *
   * @param string $shortcode The generated shortcode string
   * @param string $tournament_id Optional. Tournament ID for validation messaging
   * @param array $config Optional configuration array with keys:
   *   - 'field_id': ID for the textarea field (default: 'mtp_shortcode_field')
   *   - 'copy_button_id': ID for the copy button (default: 'mtp_copy_shortcode')
   *   - 'success_message_id': ID for success message (default: 'mtp_copy_success')
   *   - 'field_prefix': Prefix for field selectors (default: 'mtp_')
   *   - 'shortcode_updater_callback': Name of JavaScript function to call for live updates
   */
  public static function render_shortcode_generator($shortcode, $tournament_id = '', $config = array()) {
    $defaults = array(
      'field_id' => 'mtp_shortcode_field',
      'copy_button_id' => 'mtp_copy_shortcode',
      'success_message_id' => 'mtp_copy_success',
      'field_prefix' => 'mtp_',
      'shortcode_updater_callback' => 'updateShortcode'
    );
    $config = array_merge($defaults, $config);

    echo '<div class="mtp-generated-shortcode-wrapper">';
    echo '<label class="mtp-generated-shortcode__label" for="' . esc_attr($config['field_id']) . '">' . __('Generated Shortcode:', 'meinturnierplan') . '</label>';
    echo '<textarea class="mtp-generated-shortcode__field" id="' . esc_attr($config['field_id']) . '" readonly>' . esc_textarea($shortcode) . '</textarea>';
    echo '</div>';

    echo '<button type="button" id="' . esc_attr($config['copy_button_id']) . '" class="mtp-generated-shortcode__btn button button-secondary">';
    echo '<span class="mtp-generated-shortcode__btn-icon dashicons dashicons-admin-page"></span>';
    echo __('Copy Shortcode', 'meinturnierplan');
    echo '</button>';

    echo '<div id="' . esc_attr($config['success_message_id']) . '" class="mtp-generated-shortcode__copy-success" style="display: none;">';
    echo '<span class="mtp-generated-shortcode__copy-success-icon dashicons dashicons-yes-alt"></span> ';
    echo __('Shortcode copied to clipboard!', 'meinturnierplan');
    echo '</div>';

    if (empty($tournament_id)) {
      echo '<div class="mtp-generated-shortcode__message mtp-generated-shortcode__message--warning">';
      echo '<strong>' . __('Note:', 'meinturnierplan') . '</strong> ';
      echo __('Enter a Tournament ID above to display live tournament data. Without an ID, a placeholder will be shown.', 'meinturnierplan');
      echo '</div>';
    }

    // Add reusable JavaScript for copy functionality and field listeners
    self::render_shortcode_javascript($config);
  }

  /**
   * Render JavaScript for shortcode generator functionality
   *
   * This provides the basic copy-to-clipboard functionality and form field listeners
   * that can be extended with custom shortcode update logic.
   *
   * @param array $config Configuration array from render_shortcode_generator
   */
  public static function render_shortcode_javascript($config) {
    ?>
    <script>
    jQuery(document).ready(function($) {
      // Copy shortcode to clipboard functionality
      $("#<?php echo esc_js($config['copy_button_id']); ?>").on("click", function() {
        var shortcodeField = $("#<?php echo esc_js($config['field_id']); ?>");
        shortcodeField.select();
        document.execCommand("copy");

        $("#<?php echo esc_js($config['success_message_id']); ?>").fadeIn().delay(2000).fadeOut();
      });

      // Form field change listeners - calls custom update function if it exists
      if (typeof <?php echo esc_js($config['shortcode_updater_callback']); ?> === 'function') {
        // Input and select field changes
        $("input[id^='<?php echo esc_js($config['field_prefix']); ?>'], .mtp-color-picker, select[id^='<?php echo esc_js($config['field_prefix']); ?>']").on("input change", function() {
          <?php echo esc_js($config['shortcode_updater_callback']); ?>();
        });

        // Opacity slider changes
        $("input[type='range'][id^='<?php echo esc_js($config['field_prefix']); ?>']").on("input", function() {
          <?php echo esc_js($config['shortcode_updater_callback']); ?>();
        });
      }
    });
    </script>
    <?php
  }

  /**
   * Render conditional group field for tournament selection
   *
   * @param array $meta_values Array containing tournament_id and group values
   * @param string $field_prefix Optional. Prefix for field names. Default 'mtp_'.
   */
  public static function render_conditional_group_field($meta_values, $field_prefix = 'mtp_') {
    $tournament_id = $meta_values['tournament_id'];
    $saved_group = $meta_values['group'];
    $groups = array();
    $has_final_round = false;

    // Only fetch groups if tournament ID is provided
    if (!empty($tournament_id)) {
      $tournament_data = self::fetch_tournament_groups($tournament_id);
      $groups = $tournament_data['groups'];
      $has_final_round = $tournament_data['hasFinalRound'];
    }

    $group_field_id = $field_prefix . 'group';
    $refresh_button_id = $field_prefix . 'refresh_groups';
    $group_field_row_id = $field_prefix . 'group_field_row';
    $saved_value_field_id = $field_prefix . 'group_saved_value';

    // Always render the field, but populate it based on available groups
    echo '<tr id="' . esc_attr($group_field_row_id) . '">';
    echo '<th scope="row"><label for="' . esc_attr($group_field_id) . '">' . esc_html(__('Group', 'meinturnierplan')) . '</label></th>';
    echo '<td>';
    echo '<div style="display: flex; align-items: center; gap: 10px;">';
    echo '<select id="' . esc_attr($group_field_id) . '" name="' . esc_attr($group_field_id) . '" class="regular-text">';

    if (!empty($groups)) {
      // Populate with actual groups - never show "All Groups" option
      foreach ($groups as $index => $group) {
        $group_number = $index + 1;
        $is_selected = false;

        if (!empty($saved_group)) {
          // Use saved selection
          $is_selected = ($saved_group == $group_number);
        } else if ($index == 0) {
          // Auto-select first group as default (both single and multiple group cases)
          $is_selected = true;
        }

        $selected = $is_selected ? ' selected' : '';
        echo '<option value="' . esc_attr($group_number) . '"' . $selected . '>' . esc_html(sprintf(__('Group %s', 'meinturnierplan'), $group['displayId'])) . '</option>';
      }

      // Add Final Round option if it exists
      if ($has_final_round) {
        $is_final_selected = (!empty($saved_group) && $saved_group == '90');
        $final_selected = $is_final_selected ? ' selected' : '';
        echo '<option value="90"' . $final_selected . '>' . esc_html(__('Final Round', 'meinturnierplan')) . '</option>';
      }
    } else if (!empty($saved_group) && !empty($tournament_id)) {
      // Show a placeholder for the saved group if groups haven't loaded yet
      if ($saved_group == '90') {
        echo '<option value="90" selected>' . esc_html(__('Final Round (saved)', 'meinturnierplan')) . '</option>';
      } else {
        echo '<option value="' . esc_attr($saved_group) . '" selected>' . esc_html(sprintf(__('Group %s (saved)', 'meinturnierplan'), $saved_group)) . '</option>';
      }
    } else {
      // No groups available - check for Final Round only
      if ($has_final_round) {
        $is_final_selected = (!empty($saved_group) && $saved_group == '90');
        $final_selected = $is_final_selected ? ' selected' : '';
        echo '<option value="90"' . $final_selected . '>' . esc_html(__('Final Round', 'meinturnierplan')) . '</option>';
      } else {
        // No groups and no final round - show default option
        echo '<option value="">' . esc_html(__('Default', 'meinturnierplan')) . '</option>';
      }
    }

    echo '</select>';
    echo '<button type="button" id="' . esc_attr($refresh_button_id) . '" class="button button-secondary" title="' . esc_attr(__('Refresh Groups', 'meinturnierplan')) . '">';
    echo '<span class="dashicons dashicons-update-alt" style="vertical-align: middle;"></span>';
    echo '</button>';
    echo '</div>';

    // Static description that covers all scenarios
    echo '<p class="description">' . esc_html(__('Select a group to display from this tournament. Click refresh to update groups from server. Please note that some tournaments do not have groups.', 'meinturnierplan')) . '</p>';

    // Add hidden field to store the initially saved value for JavaScript
    echo '<input type="hidden" id="' . esc_attr($saved_value_field_id) . '" value="' . esc_attr($saved_group) . '" />';

    echo '</td>';
    echo '</tr>';

    // Show/hide the field based on whether we have a tournament ID
    if (empty($tournament_id)) {
      echo '<style>#' . esc_attr($group_field_row_id) . ' { display: none; }</style>';
    }
  }
}
