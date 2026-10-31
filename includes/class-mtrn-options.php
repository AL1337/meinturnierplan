<?php
/**
 * Option registry: the single definition of every embed option.
 *
 * Each option is described once (admin field, meta key, shortcode attribute,
 * URL parameter, defaults, sanitising) and the renderer, shortcodes, blocks,
 * widgets, admin forms, preview and JavaScript are driven from this list.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Option registry
 */
final class MTRN_Options {

  const META_PREFIX = '_mtrn_';
  const FIELD_PREFIX = 'mtrn_';

  /** @var array|null */
  private static $definitions = null;

  /** @var array|null */
  private static $types = null;

  /**
   * The two embed types and everything that differs between them.
   *
   * @return array<string, array>
   */
  public static function types() {
    if (self::$types === null) {
      self::$types = array(
        'table' => array(
          'post_type'      => 'mtrn_table',
          'shortcode'      => 'mtrn-table',
          'block'          => 'meinturnierplan/tournament-table',
          'widget_id'      => 'mtrn_table_widget',
          'endpoint'       => 'displayTable.php',
          'id_prefix'      => 'mtrn-table-',
          'message_type'   => 'iframeSizeMtpTable',
          'placeholder_title' => __('Tournament Table Preview', 'meinturnierplan'),
          'placeholder_text'  => __('Enter a Tournament ID above to display live tournament data.', 'meinturnierplan'),
          'settings_title' => __('Table Settings', 'meinturnierplan'),
          'meta_box_title' => __('Table Settings & Preview', 'meinturnierplan'),
          'meta_box_id'    => 'mtrn_table_settings',
          'shortcode_box_id' => 'mtrn_table_shortcode',
          'nonce_action'   => 'mtrn_table_meta_box',
          'nonce_name'     => 'mtrn_table_meta_box_nonce',
          'ajax'           => array(
            'preview'        => 'mtrn_preview_table',
            'groups'         => 'mtrn_get_groups',
            'refresh_groups' => 'mtrn_refresh_groups',
            'list'           => 'mtrn_get_tables',
          ),
          'preview_config'  => 'mtrnTablePreviewConfig',
          'meta_box_config' => 'mtrnTableMetaBoxConfig',
          'block_config'    => 'mtrnBlock',
          'block_nonce'     => 'mtrn_block_nonce',
          'block_json'      => 'blocks/tournament-table/block.json',
          'block_script'    => 'mtrn-tournament-table-block',
          'block_script_file' => 'assets/js/tournament-table-block.js',
          'select_placeholder' => __('Please select a Tournament Table.', 'meinturnierplan'),
          'select_label'    => __('Select a Tournament Table', 'meinturnierplan'),
          'widget_name'     => __('Tournament Table', 'meinturnierplan'),
          'widget_description' => __('Display a tournament table.', 'meinturnierplan'),
          'widget_classname' => 'mtrn-table-widget',
          'widget_select_label' => __('Select Tournament Table:', 'meinturnierplan'),
          'widget_select_empty' => __('-- Select Table --', 'meinturnierplan'),
          'widget_note'     => __('The widget will use all styling settings configured for the selected Tournament Table. Width and height are automatically determined.', 'meinturnierplan'),
        ),
        'matches' => array(
          'post_type'      => 'mtrn_match_list',
          'shortcode'      => 'mtrn-matches',
          'block'          => 'meinturnierplan/matches-table',
          'widget_id'      => 'mtrn_matches_widget',
          'endpoint'       => 'displayMatches.php',
          'id_prefix'      => 'mtrn-matches-',
          'message_type'   => 'iframeSizeMtpMatches',
          'placeholder_title' => __('Matches Preview', 'meinturnierplan'),
          'placeholder_text'  => __('Enter a Tournament ID above to display live matches data.', 'meinturnierplan'),
          'settings_title' => __('Matches Settings', 'meinturnierplan'),
          'meta_box_title' => __('Matches Settings & Preview', 'meinturnierplan'),
          'meta_box_id'    => 'mtrn_matches_settings',
          'shortcode_box_id' => 'mtrn_matches_shortcode',
          'nonce_action'   => 'mtrn_matches_meta_box',
          'nonce_name'     => 'mtrn_matches_meta_box_nonce',
          'ajax'           => array(
            'preview'        => 'mtrn_preview_matches',
            'groups'         => 'mtrn_get_matches_groups',
            'refresh_groups' => 'mtrn_refresh_matches_groups',
            'teams'          => 'mtrn_get_matches_teams',
            'refresh_teams'  => 'mtrn_refresh_matches_teams',
            'check_option'   => 'mtrn_check_tournament_option',
            'list'           => 'mtrn_get_matches',
          ),
          'preview_config'  => 'mtrnMatchesPreviewConfig',
          'meta_box_config' => 'mtrnMatchesMetaBoxConfig',
          'block_config'    => 'mtrnMatchesBlock',
          'block_nonce'     => 'mtrn_matches_block_nonce',
          'block_json'      => 'blocks/tournament-matches/block.json',
          'block_script'    => 'mtrn-tournament-matches-block',
          'block_script_file' => 'assets/js/tournament-matches-block.js',
          'select_placeholder' => __('Please select a Matches Table.', 'meinturnierplan'),
          'select_label'    => __('Select a Matches Table', 'meinturnierplan'),
          'widget_name'     => __('Matches Table', 'meinturnierplan'),
          'widget_description' => __('Display a matches table.', 'meinturnierplan'),
          'widget_classname' => 'mtrn-matches-widget',
          'widget_select_label' => __('Select Matches Table:', 'meinturnierplan'),
          'widget_select_empty' => __('-- Select Matches --', 'meinturnierplan'),
          'widget_note'     => __('The widget will use all styling settings configured for the selected Matches Table. Width and height are automatically determined.', 'meinturnierplan'),
        ),
      );
    }
    return self::$types;
  }

  /**
   * Configuration of one embed type.
   *
   * @param string $type 'table' or 'matches'.
   * @return array
   */
  public static function type($type) {
    $types = self::types();
    return isset($types[$type]) ? $types[$type] : $types['table'];
  }

  /**
   * Type key for a post type slug, or '' when it is not one of ours.
   */
  public static function type_for_post_type($post_type) {
    foreach (self::types() as $key => $config) {
      if ($config['post_type'] === $post_type) {
        return $key;
      }
    }
    return '';
  }

  /**
   * Admin form groups in display order.
   *
   * @return array<string, string> group key => header label
   */
  public static function groups() {
    return array(
      'basic'       => __('Basic Settings', 'meinturnierplan'),
      'display'     => __('Display Options', 'meinturnierplan'),
      'responsive'  => __('Responsive', 'meinturnierplan'),
      'typography'  => __('Typography', 'meinturnierplan'),
      'spacing'     => __('Spacing & Layout', 'meinturnierplan'),
      'border'      => __('Border Settings', 'meinturnierplan'),
      'colors'      => __('Colors', 'meinturnierplan'),
      'backgrounds' => __('Background Colors', 'meinturnierplan'),
    );
  }

  /**
   * Every option, in admin form order. This order is also used for the embed
   * URL and the generated shortcode.
   *
   * Keys of a definition:
   *  - kind: source | language | group | participant | match_number | flag | wrap | style | color_opacity | size
   *  - type: text | language | number | bool | color | opacity-color (sanitising)
   *  - applies: 'both' | 'table' | 'matches'
   *  - group: admin group key
   *  - label, description: admin texts (description may be an array keyed by type)
   *  - attr: shortcode attribute; attr_aliases: further accepted attributes
   *  - param: URL parameter (style key inside s[...], bare flag name, or query key)
   *  - default: renderer default; admin_default when the admin form differs
   *  - min, max: number field limits
   *  - condition: tournament JSON field that must be truthy for the row to show
   *  - opacity_key, opacity_default, color_default: for color_opacity options
   *
   * @return array<string, array>
   */
  public static function definitions() {
    if (self::$definitions !== null) {
      return self::$definitions;
    }

    $d = array();

    // Basic settings
    $d['tournament_id'] = array(
      'kind' => 'source', 'type' => 'text', 'applies' => 'both', 'group' => 'basic', 'attr' => 'id', 'param' => 'id', 'default' => '',
      'label' => __('Tournament ID', 'meinturnierplan'),
      'description' => __('Enter the tournament ID from your language domain (e.g., 1753883027)', 'meinturnierplan'),
    );
    $d['language'] = array(
      'kind' => 'language', 'type' => 'language', 'applies' => 'both', 'group' => 'basic', 'attr' => 'lang', 'attr_aliases' => array('setlang'), 'param' => 'setlang', 'default' => 'en',
      'label' => __('Language', 'meinturnierplan'),
      'description' => __('Select the language for the tournament table display.', 'meinturnierplan'),
    );

    // Display options
    $d['group'] = array(
      'kind' => 'group', 'type' => 'text', 'applies' => 'both', 'group' => 'display', 'attr' => 'group', 'param' => 'gr', 'default' => '',
      'label' => __('Group', 'meinturnierplan'),
    );
    $d['participant'] = array(
      'kind' => 'participant', 'type' => 'text', 'applies' => 'matches', 'group' => 'display', 'attr' => 'participant', 'param' => 'tm', 'default' => '', 'admin_default' => '-1',
      'label' => __('Participant', 'meinturnierplan'),
    );
    $d['match_number'] = array(
      'kind' => 'match_number', 'type' => 'text', 'applies' => 'matches', 'group' => 'display', 'attr' => 'gamenumbers', 'attr_aliases' => array('match_number'), 'param' => 'mn', 'default' => '',
      'label' => __('Match number (from-to)', 'meinturnierplan'),
      'description' => __('Enter a single match number (e.g., "8") or a range (e.g., "2-7"). Leave empty to display all matches.', 'meinturnierplan'),
    );
    $d['suppress_wins'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'table', 'group' => 'display', 'attr' => 'sw', 'param' => 'sw', 'default' => '0',
      'label' => __('Suppress Num Wins, Losses, etc.', 'meinturnierplan'),
      'description' => __('Hide the number of wins, losses, and other statistical columns from the tournament table.', 'meinturnierplan'),
    );
    $d['suppress_logos'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'table', 'group' => 'display', 'attr' => 'sl', 'param' => 'sl', 'default' => '0',
      'label' => __('Suppress Logos', 'meinturnierplan'),
      'description' => __('Hide the logos from the tournament table.', 'meinturnierplan'),
    );
    $d['suppress_num_matches'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'table', 'group' => 'display', 'attr' => 'sn', 'param' => 'sn', 'default' => '0',
      'label' => __('Suppress Num Matches', 'meinturnierplan'),
      'description' => __('Hide the number of matches from the tournament table.', 'meinturnierplan'),
    );
    $d['projector_presentation'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'both', 'group' => 'display', 'attr' => 'bm', 'param' => 'bm', 'default' => '0',
      'label' => __('Projector Presentation', 'meinturnierplan'),
      'description' => array(
        'table'   => __('Enable projector presentation mode for the tournament table.', 'meinturnierplan'),
        'matches' => __('Enable projector presentation mode for the matches table.', 'meinturnierplan'),
      ),
    );
    $d['navigation_for_groups'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'table', 'group' => 'display', 'attr' => 'nav', 'param' => 'nav', 'default' => '0',
      'label' => __('Navigation for Groups', 'meinturnierplan'),
      'description' => __('Enable navigation for groups in the tournament table.', 'meinturnierplan'),
    );
    $d['si'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'matches', 'group' => 'display', 'attr' => 'si', 'param' => 'si', 'default' => '0',
      'label' => __('Suppress Match Number', 'meinturnierplan'),
      'description' => __('Enable suppression of match numbers in the matches table.', 'meinturnierplan'),
    );
    $d['sf'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'matches', 'group' => 'display', 'attr' => 'sf', 'param' => 'sf', 'default' => '0', 'condition' => 'showCourts',
      'label' => __('Suppress Court', 'meinturnierplan'),
      'description' => __('Enable suppression of court information in the matches table.', 'meinturnierplan'),
    );
    $d['sg'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'matches', 'group' => 'display', 'attr' => 'sg', 'param' => 'sg', 'default' => '0', 'condition' => 'showGroups',
      'label' => __('Suppress Group', 'meinturnierplan'),
      'description' => __('Enable suppression of group information in the matches table.', 'meinturnierplan'),
    );
    $d['sr'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'matches', 'group' => 'display', 'attr' => 'sr', 'param' => 'sr', 'default' => '0', 'condition' => 'showReferees',
      'label' => __('Suppress Referee', 'meinturnierplan'),
      'description' => __('Enable suppression of referee information in the matches table.', 'meinturnierplan'),
    );
    $d['st'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'matches', 'group' => 'display', 'attr' => 'st', 'param' => 'st', 'default' => '0',
      'label' => __('Suppress Times', 'meinturnierplan'),
      'description' => __('Enable suppression of match times in the matches table.', 'meinturnierplan'),
    );
    $d['se'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'matches', 'group' => 'display', 'attr' => 'se', 'param' => 'se', 'default' => '0', 'condition' => 'finalMatches',
      'label' => __('Suppress Extra Time', 'meinturnierplan'),
      'description' => __('Enable suppression of extra time information in the final matches.', 'meinturnierplan'),
    );
    $d['sp'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'matches', 'group' => 'display', 'attr' => 'sp', 'param' => 'sp', 'default' => '0', 'condition' => 'finalMatches',
      'label' => __('Suppress Penalties', 'meinturnierplan'),
      'description' => __('Enable suppression of penalty information in the final matches.', 'meinturnierplan'),
    );
    $d['sh'] = array(
      'kind' => 'flag', 'type' => 'bool', 'applies' => 'matches', 'group' => 'display', 'attr' => 'sh', 'param' => 'sh', 'default' => '0', 'condition' => 'finalMatches',
      'label' => __('Suppress Headlines in Final Matches', 'meinturnierplan'),
      'description' => __('Enable suppression of headlines in the final matches.', 'meinturnierplan'),
    );

    // Responsive
    $d['responsive'] = array(
      'kind' => 'wrap', 'type' => 'bool', 'applies' => 'both', 'group' => 'responsive', 'attr' => 's-wrap', 'param' => 'wrap', 'default' => '0',
      'label' => __('Responsive Layout', 'meinturnierplan'),
      'description' => array(
        'table'   => __('Wrap long team names and let the table scroll horizontally instead of being clipped on narrow screens (e.g. mobile).', 'meinturnierplan'),
        'matches' => __('Wrap long names and let the table scroll horizontally instead of being clipped on narrow screens (e.g. mobile).', 'meinturnierplan'),
      ),
    );

    // Typography
    $d['font_size'] = array(
      'kind' => 'style', 'type' => 'number', 'applies' => 'both', 'group' => 'typography', 'attr' => 's-size', 'param' => 'size', 'default' => '9', 'min' => 6, 'max' => 24,
      'label' => __('Content Font Size (pt)', 'meinturnierplan'),
      'description' => array(
        'table'   => __('Set the font size of the tournament table content. 9pt is the default value.', 'meinturnierplan'),
        'matches' => __('Set the font size of the matches table content. 9pt is the default value.', 'meinturnierplan'),
      ),
    );
    $d['header_font_size'] = array(
      'kind' => 'style', 'type' => 'number', 'applies' => 'both', 'group' => 'typography', 'attr' => 's-sizeheader', 'param' => 'sizeheader', 'default' => '10', 'min' => 6, 'max' => 24,
      'label' => __('Header Font Size (pt)', 'meinturnierplan'),
      'description' => array(
        'table'   => __('Set the font size of the tournament table headers. 10pt is the default value.', 'meinturnierplan'),
        'matches' => __('Set the font size of the matches table headers. 10pt is the default value.', 'meinturnierplan'),
      ),
    );
    $d['logo_size'] = array(
      'kind' => 'style', 'type' => 'number', 'applies' => 'table', 'group' => 'typography', 'attr' => 's-logosize', 'param' => 'logosize', 'default' => '20', 'min' => 6, 'max' => 24,
      'label' => __('Logo Size (pt)', 'meinturnierplan'),
      'description' => __('Set the font size of the tournament table logo. 20pt is the default value.', 'meinturnierplan'),
    );
    $d['ehrsize'] = array(
      'kind' => 'style', 'type' => 'number', 'applies' => 'matches', 'group' => 'typography', 'attr' => 's-ehrsize', 'param' => 'ehrsize', 'default' => '10', 'min' => 6, 'max' => 24,
      'label' => __('Headlines Font Size (pt)', 'meinturnierplan'),
      'description' => __('Set the font size of the matches table headlines. 10pt is the default value.', 'meinturnierplan'),
    );

    // Spacing & layout
    $d['table_padding'] = array(
      'kind' => 'style', 'type' => 'number', 'applies' => 'both', 'group' => 'spacing', 'attr' => 's-padding', 'param' => 'padding', 'default' => '2', 'min' => 0, 'max' => 50,
      'label' => __('Table Padding (px)', 'meinturnierplan'),
      'description' => array(
        'table'   => __('Set the padding around the tournament table. 2px is the default value.', 'meinturnierplan'),
        'matches' => __('Set the padding around the matches table. 2px is the default value.', 'meinturnierplan'),
      ),
    );
    $d['inner_padding'] = array(
      'kind' => 'style', 'type' => 'number', 'applies' => 'both', 'group' => 'spacing', 'attr' => 's-innerpadding', 'param' => 'innerpadding', 'default' => '5', 'min' => 0, 'max' => 20,
      'label' => __('Inner Padding (px)', 'meinturnierplan'),
      'description' => array(
        'table'   => __('Set the padding inside the tournament table cells. 5px is the default value.', 'meinturnierplan'),
        'matches' => __('Set the padding inside the matches table cells. 5px is the default value.', 'meinturnierplan'),
      ),
    );
    $d['ehrtop'] = array(
      'kind' => 'style', 'type' => 'number', 'applies' => 'matches', 'group' => 'spacing', 'attr' => 's-ehrtop', 'param' => 'ehrtop', 'default' => '9', 'min' => 0, 'max' => 20,
      'label' => __('Headlines Top Padding (px)', 'meinturnierplan'),
      'description' => __('Set the top padding of the headlines. 9px is the default value.', 'meinturnierplan'),
    );
    $d['ehrbottom'] = array(
      'kind' => 'style', 'type' => 'number', 'applies' => 'matches', 'group' => 'spacing', 'attr' => 's-ehrbottom', 'param' => 'ehrbottom', 'default' => '3', 'min' => 0, 'max' => 20,
      'label' => __('Headlines Bottom Padding (px)', 'meinturnierplan'),
      'description' => __('Set the bottom padding of the headlines. 3px is the default value.', 'meinturnierplan'),
    );

    // Border settings
    $border = array(
      'bsizeh'  => array('bsizeh', '1', __('Border Horizontal Size (px)', 'meinturnierplan'), __('Set the border horizontal size of the tournament table. 1px is the default value.', 'meinturnierplan'), __('Set the border horizontal size of the matches table. 1px is the default value.', 'meinturnierplan')),
      'bsizev'  => array('bsizev', '1', __('Border Vertical Size (px)', 'meinturnierplan'), __('Set the border vertical size of the tournament table. 1px is the default value.', 'meinturnierplan'), __('Set the border vertical size of the matches table. 1px is the default value.', 'meinturnierplan')),
      'bsizeoh' => array('bsizeoh', '1', __('Table Top and Bottom Border Size (px)', 'meinturnierplan'), __('Set the top and bottom border size of the tournament table. 1px is the default value.', 'meinturnierplan'), __('Set the top and bottom border size of the matches table. 1px is the default value.', 'meinturnierplan')),
      'bsizeov' => array('bsizeov', '1', __('Table Left and Right Border Size (px)', 'meinturnierplan'), __('Set the left and right border size of the tournament table. 1px is the default value.', 'meinturnierplan'), __('Set the left and right border size of the matches table. 1px is the default value.', 'meinturnierplan')),
      'bbsize'  => array('bbsize', '2', __('Table Head Border Bottom Size (px)', 'meinturnierplan'), __('Set the head border bottom size of the tournament table. 2px is the default value.', 'meinturnierplan'), __('Set the head border bottom size of the matches table. 2px is the default value.', 'meinturnierplan')),
    );
    foreach ($border as $key => $row) {
      $d[$key] = array(
        'kind' => 'style', 'type' => 'number', 'applies' => 'both', 'group' => 'border', 'attr' => 's-' . $row[0], 'param' => $row[0], 'default' => $row[1], 'min' => 1, 'max' => 10,
        'label' => $row[2], 'description' => array('table' => $row[3], 'matches' => $row[4]),
      );
    }

    // Colors
    $d['text_color'] = array(
      'kind' => 'style', 'type' => 'color', 'applies' => 'both', 'group' => 'colors', 'attr' => 's-color', 'param' => 'color', 'default' => '000000',
      'label' => __('Text Color', 'meinturnierplan'),
      'description' => __('Set the color of the tournament table text. Black (#000000) is the default value.', 'meinturnierplan'),
    );
    $d['main_color'] = array(
      'kind' => 'style', 'type' => 'color', 'applies' => 'both', 'group' => 'colors', 'attr' => 's-maincolor', 'param' => 'maincolor', 'default' => '173f75',
      'label' => __('Link & Navigation Color', 'meinturnierplan'),
      'description' => __('Set the color for links (e.g., "Show Full Tournament") and navigation arrows between groups. Blue (#173f75) is the default value.', 'meinturnierplan'),
    );
    $d['border_color'] = array(
      'kind' => 'style', 'type' => 'color', 'applies' => 'both', 'group' => 'colors', 'attr' => 's-bcolor', 'param' => 'bcolor', 'default' => 'bbbbbb',
      'label' => __('Border Color', 'meinturnierplan'),
      'description' => __('Set the border color of the tournament table. Light gray (#bbbbbb) is the default value.', 'meinturnierplan'),
    );
    $d['head_bottom_border_color'] = array(
      'kind' => 'style', 'type' => 'color', 'applies' => 'both', 'group' => 'colors', 'attr' => 's-bbcolor', 'param' => 'bbcolor', 'default' => 'bbbbbb',
      'label' => __('Table Head Bottom Border Color', 'meinturnierplan'),
      'description' => __('Set the bottom border color of the table header. Light gray (#bbbbbb) is the default value.', 'meinturnierplan'),
    );

    // Background colors (color + opacity pairs)
    $backgrounds = array(
      'bg_color'       => array('bgcolor', 'bg_opacity', '000000', '0', __('Background Color', 'meinturnierplan'), __('Set the background color and opacity of the tournament table. Use opacity 0% for transparent background.', 'meinturnierplan')),
      'head_bg_color'  => array('bghead', 'head_bg_opacity', 'eeeeff', '100', __('Head Background Color', 'meinturnierplan'), __('Set the background color and opacity for table head. Use opacity 0% for transparent background.', 'meinturnierplan')),
      'even_bg_color'  => array('bgeven', 'even_bg_opacity', 'f0f8ff', '69', __('Even Rows Background Color', 'meinturnierplan'), __('Set the background color and opacity for even-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan')),
      'odd_bg_color'   => array('bgodd', 'odd_bg_opacity', 'ffffff', '69', __('Odd Rows Background Color', 'meinturnierplan'), __('Set the background color and opacity for odd-numbered table rows. Use opacity 0% for transparent background.', 'meinturnierplan')),
      'hover_bg_color' => array('bgover', 'hover_bg_opacity', 'eeeeff', '69', __('Row Hover Background Color', 'meinturnierplan'), __('Set the background color and opacity for table rows hover. Use opacity 0% for transparent background.', 'meinturnierplan')),
    );
    foreach ($backgrounds as $key => $row) {
      $d[$key] = array(
        'kind' => 'color_opacity', 'type' => 'color', 'applies' => 'both', 'group' => 'backgrounds', 'attr' => 's-' . $row[0], 'param' => $row[0],
        'opacity_key' => $row[1], 'color_default' => $row[2], 'opacity_default' => $row[3],
        'default' => self::combine_color_opacity($row[2], $row[3]),
        'label' => $row[4], 'description' => $row[5],
      );
    }

    // Iframe size (hidden fields, written back by the resize script)
    $d['width'] = array('kind' => 'size', 'type' => 'number', 'applies' => 'both', 'group' => 'hidden', 'attr' => 'width', 'param' => 'width', 'default' => '300', 'admin_default' => '300');
    $d['height'] = array('kind' => 'size', 'type' => 'number', 'applies' => 'both', 'group' => 'hidden', 'attr' => 'height', 'param' => 'height', 'default' => '200', 'admin_default' => '152');

    self::$definitions = $d;
    return $d;
  }

  /**
   * Options that apply to one embed type, in canonical order.
   *
   * @return array<string, array>
   */
  public static function for_type($type) {
    $out = array();
    foreach (self::definitions() as $key => $def) {
      if ($def['applies'] === 'both' || $def['applies'] === $type) {
        $out[$key] = $def;
      }
    }
    return $out;
  }

  /**
   * Description of an option for one type.
   */
  public static function description($def, $type) {
    if (!isset($def['description'])) {
      return '';
    }
    if (is_array($def['description'])) {
      return isset($def['description'][$type]) ? $def['description'][$type] : reset($def['description']);
    }
    return $def['description'];
  }

  /**
   * Accepted shortcode attributes with empty defaults. Empty means "fall back
   * to the settings saved on post_id, then to the built-in default".
   *
   * @return array<string, string>
   */
  public static function shortcode_defaults($type) {
    $defaults = array('id' => '', 'post_id' => '');
    foreach (self::for_type($type) as $key => $def) {
      if ($def['kind'] === 'source') {
        continue;
      }
      $defaults[$def['attr']] = '';
      if (!empty($def['attr_aliases'])) {
        foreach ($def['attr_aliases'] as $alias) {
          if ($alias !== 'setlang') { // setlang is internal, not a public attribute
            $defaults[$alias] = '';
          }
        }
      }
    }
    return $defaults;
  }

  /**
   * Effective value of every option for one embed.
   *
   * Precedence: explicit attribute (non-empty, or any '0'/'1' for flags),
   * then the meta saved on $post_id, then the built-in default. Keys with
   * 'applies' other than $type are not resolved, so a matches embed never
   * carries table-only options and vice versa.
   *
   * @param string $type    'table' or 'matches'.
   * @param array  $atts    Shortcode / render attributes.
   * @param int    $post_id Post whose settings act as fallback, or 0.
   * @return array<string, mixed> option key => value; flags and 'responsive' are bool, width/height int
   */
  public static function resolve($type, array $atts, $post_id = 0) {
    $post_id = $post_id ? (int) $post_id : 0;
    $values = array('type' => $type);

    foreach (self::for_type($type) as $key => $def) {
      $attr = self::attribute_value($def, $atts);
      $meta = $post_id ? get_post_meta($post_id, self::META_PREFIX . $key, true) : '';

      switch ($def['kind']) {
        case 'source':
          $values[$key] = $attr !== '' ? $attr : (string) $meta;
          break;

        case 'language':
          $language = $attr !== '' ? $attr : (string) $meta;
          $values[$key] = $language !== '' ? sanitize_text_field($language) : 'en';
          break;

        case 'group':
        case 'participant':
        case 'match_number':
          $values[$key] = $attr !== '' ? $attr : (string) $meta;
          break;

        case 'size':
          // Attribute, then the size saved by the admin preview, then the default
          $size = $attr !== '' ? intval($attr) : intval($meta);
          $values[$key] = $size > 0 ? $size : intval($def['default']);
          break;

        case 'flag':
        case 'wrap':
          if ($attr !== '') {
            $lower = strtolower($attr);
            $values[$key] = ($lower === '1' || $lower === 'true');
          } else {
            $values[$key] = ((string) $meta === '1');
          }
          break;

        case 'style':
          if ($attr !== '') {
            $values[$key] = $attr;
          } elseif ((string) $meta !== '') {
            $values[$key] = (string) $meta;
          } else {
            $values[$key] = $def['default'];
          }
          break;

        case 'color_opacity':
          if ($attr !== '') {
            $values[$key] = $attr;
          } elseif ((string) $meta !== '') {
            $opacity = get_post_meta($post_id, self::META_PREFIX . $def['opacity_key'], true);
            $values[$key] = self::combine_color_opacity((string) $meta, (string) $opacity !== '' ? $opacity : $def['opacity_default']);
          } else {
            $values[$key] = $def['default'];
          }
          break;
      }
    }

    return $values;
  }

  /**
   * The attribute value for an option: its attribute or any alias, '' when
   * absent or empty.
   */
  private static function attribute_value($def, array $atts) {
    $names = array($def['attr']);
    if (!empty($def['attr_aliases'])) {
      // Aliases win when set: 'setlang' is what the shortcode handler maps 'lang' to,
      // 'match_number' is what the admin preview sends.
      $names = array_merge($def['attr_aliases'], $names);
    }
    foreach ($names as $name) {
      if (isset($atts[$name]) && (string) $atts[$name] !== '') {
        return (string) $atts[$name];
      }
    }
    return '';
  }

  /**
   * Query string of the embed URL for resolved values. Flags are sent as bare
   * parameters ("&sw"), exactly as the service documents them.
   */
  public static function embed_query($type, array $values) {
    $parts = array('id=' . rawurlencode($values['tournament_id']));
    $flags = array();
    $filters = array();

    foreach (self::for_type($type) as $key => $def) {
      switch ($def['kind']) {
        case 'style':
        case 'color_opacity':
          if ((string) $values[$key] !== '') {
            $parts[] = urlencode('s[' . $def['param'] . ']') . '=' . urlencode((string) $values[$key]);
          }
          break;
        case 'wrap':
          $parts[] = urlencode('s[wrap]') . '=' . ($values[$key] ? 'true' : 'false');
          break;
        case 'flag':
          if ($values[$key]) {
            $flags[] = urlencode($def['param']);
          }
          break;
        case 'language':
          if ($values[$key] !== '' && $values[$key] !== 'en') {
            $filters[] = 'setlang=' . urlencode($values[$key]);
          }
          break;
        case 'group':
        case 'match_number':
          if ((string) $values[$key] !== '') {
            $filters[] = $def['param'] . '=' . urlencode((string) $values[$key]);
          }
          break;
        case 'participant':
          if ((string) $values[$key] !== '' && (string) $values[$key] !== '-1') {
            $filters[] = $def['param'] . '=' . urlencode((string) $values[$key]);
          }
          break;
      }
    }

    return implode('&', array_merge($parts, $flags, $filters));
  }

  /**
   * Combine a hex colour and an opacity percentage into an 8-digit hex value.
   * Integer arithmetic (round half up), identical on every PHP version and to
   * the JavaScript updater.
   */
  public static function combine_color_opacity($hex_color, $opacity_percent) {
    $hex_color = ltrim((string) $hex_color, '#');
    if (strlen($hex_color) === 8) {
      return $hex_color;
    }
    if ($opacity_percent === '' || $opacity_percent === null) {
      $opacity_percent = 100;
    }
    $percent = max(0, min(100, (int) round((float) $opacity_percent)));
    return $hex_color . str_pad(dechex(intdiv($percent * 255 + 50, 100)), 2, '0', STR_PAD_LEFT);
  }

  /**
   * Admin form defaults: option key => default, plus the opacity keys.
   *
   * @return array<string, string>
   */
  public static function meta_defaults($type) {
    $defaults = array();
    foreach (self::for_type($type) as $key => $def) {
      switch ($def['kind']) {
        case 'language':
          $defaults[$key] = MTRN_Admin_Utilities::get_default_language();
          break;
        case 'color_opacity':
          $defaults[$key] = $def['color_default'];
          $defaults[$def['opacity_key']] = $def['opacity_default'];
          break;
        default:
          $defaults[$key] = isset($def['admin_default']) ? $def['admin_default'] : $def['default'];
      }
    }
    return $defaults;
  }

  /**
   * Meta keys (without prefix) the admin form saves for a type.
   *
   * @return string[]
   */
  public static function save_keys($type) {
    return array_keys(self::meta_defaults($type));
  }

  /**
   * Whether an admin field is a checkbox (absent from POST when unchecked).
   */
  public static function is_checkbox($key) {
    $defs = self::definitions();
    return isset($defs[$key]) && $defs[$key]['type'] === 'bool';
  }

  /**
   * Sanitise a value for saving, by option type.
   */
  public static function sanitize($key, $value) {
    $defs = self::definitions();

    if (substr($key, -8) === '_opacity') {
      return max(0, min(100, absint($value)));
    }
    if (!isset($defs[$key])) {
      return sanitize_text_field($value);
    }

    switch ($defs[$key]['type']) {
      case 'bool':
        return $value === '1' ? '1' : '0';
      case 'number':
        return absint($value);
      case 'color':
        $color = sanitize_hex_color($value);
        return $color ? ltrim($color, '#') : '';
      case 'language':
        return array_key_exists($value, MTRN_Admin_Utilities::get_language_options()) ? $value : 'en';
      default:
        return sanitize_text_field($value);
    }
  }
}
