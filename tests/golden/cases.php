<?php
/**
 * Golden (characterization) cases for the phase 2 refactor.
 *
 * Each case describes a saved post (meta) and, optionally, shortcode
 * attributes, a live-preview POST and a save POST. GoldenTest renders every
 * applicable channel and compares the output with tests/golden/expected/.
 * Regenerate with:  MTRN_UPDATE_GOLDENS=1 vendor/bin/phpunit --filter GoldenTest
 */

$table_full_meta = array(
  '_mtrn_tournament_id' => 'golden1', '_mtrn_language' => 'de', '_mtrn_group' => '2',
  '_mtrn_width' => '640', '_mtrn_height' => '480',
  '_mtrn_font_size' => '11', '_mtrn_header_font_size' => '12', '_mtrn_logo_size' => '24',
  '_mtrn_table_padding' => '4', '_mtrn_inner_padding' => '6',
  '_mtrn_bsizeh' => '2', '_mtrn_bsizev' => '3', '_mtrn_bsizeoh' => '4', '_mtrn_bsizeov' => '5', '_mtrn_bbsize' => '6',
  '_mtrn_text_color' => '111111', '_mtrn_main_color' => '222222', '_mtrn_border_color' => '333333', '_mtrn_head_bottom_border_color' => '444444',
  '_mtrn_bg_color' => '555555', '_mtrn_bg_opacity' => '50', '_mtrn_head_bg_color' => '666666', '_mtrn_head_bg_opacity' => '0',
  '_mtrn_even_bg_color' => '777777', '_mtrn_even_bg_opacity' => '100', '_mtrn_odd_bg_color' => '888888', '_mtrn_odd_bg_opacity' => '25',
  '_mtrn_hover_bg_color' => '999999', '_mtrn_hover_bg_opacity' => '75',
  '_mtrn_suppress_wins' => '1', '_mtrn_suppress_logos' => '1', '_mtrn_suppress_num_matches' => '1',
  '_mtrn_projector_presentation' => '1', '_mtrn_navigation_for_groups' => '1', '_mtrn_responsive' => '1',
);

$matches_full_meta = array(
  '_mtrn_tournament_id' => 'golden1', '_mtrn_language' => 'it', '_mtrn_group' => '90',
  '_mtrn_participant' => '3', '_mtrn_match_number' => '2-7',
  '_mtrn_width' => '700', '_mtrn_height' => '900',
  '_mtrn_font_size' => '13', '_mtrn_header_font_size' => '14',
  '_mtrn_ehrsize' => '15', '_mtrn_ehrtop' => '7', '_mtrn_ehrbottom' => '1',
  '_mtrn_table_padding' => '3', '_mtrn_inner_padding' => '8',
  '_mtrn_bsizeh' => '2', '_mtrn_bsizev' => '2', '_mtrn_bsizeoh' => '3', '_mtrn_bsizeov' => '3', '_mtrn_bbsize' => '4',
  '_mtrn_text_color' => 'aaaaaa', '_mtrn_main_color' => 'bbbbb1', '_mtrn_border_color' => 'cccccc', '_mtrn_head_bottom_border_color' => 'dddddd',
  '_mtrn_bg_color' => 'eeeeee', '_mtrn_bg_opacity' => '10', '_mtrn_head_bg_color' => 'f1f1f1', '_mtrn_head_bg_opacity' => '90',
  '_mtrn_even_bg_color' => 'a1a1a1', '_mtrn_even_bg_opacity' => '69', '_mtrn_odd_bg_color' => 'b2b2b2', '_mtrn_odd_bg_opacity' => '0',
  '_mtrn_hover_bg_color' => 'c3c3c3', '_mtrn_hover_bg_opacity' => '100',
  '_mtrn_projector_presentation' => '1', '_mtrn_si' => '1', '_mtrn_sf' => '1', '_mtrn_st' => '1', '_mtrn_sg' => '1',
  '_mtrn_sr' => '1', '_mtrn_se' => '1', '_mtrn_sp' => '1', '_mtrn_sh' => '1', '_mtrn_responsive' => '1',
);

return array(

  'table-defaults' => array(
    'type' => 'table', 'post_id' => 101,
    'meta' => array('_mtrn_tournament_id' => 'golden1'),
    'atts' => array('id' => 'golden1'),
  ),

  'table-customized' => array(
    'type' => 'table', 'post_id' => 102,
    'meta' => $table_full_meta,
    'atts' => array('id' => 'golden1', 'lang' => 'de', 'group' => '2', 'sw' => '1', 'nav' => '1', 's-maincolor' => '222222', 's-bgeven' => '777777ff', 's-wrap' => 'true', 'width' => '640', 'height' => '480'),
    'preview_post' => array(
      'tournament_id' => 'golden1', 'font_size' => '11', 'header_font_size' => '12', 'bsizeh' => '2', 'bsizev' => '3', 'bsizeoh' => '4', 'bsizeov' => '5', 'bbsize' => '6',
      'table_padding' => '4', 'inner_padding' => '6', 'text_color' => '111111', 'main_color' => '222222', 'bg_color' => '55555580', 'logo_size' => '24',
      'border_color' => '333333', 'head_bottom_border_color' => '444444', 'even_bg_color' => '777777ff', 'odd_bg_color' => '88888840', 'hover_bg_color' => '999999bf', 'head_bg_color' => '66666600',
      'suppress_wins' => '1', 'suppress_logos' => '1', 'suppress_num_matches' => '0', 'projector_presentation' => '1', 'navigation_for_groups' => '0', 'responsive' => '1',
      'language' => 'de', 'group' => '2',
    ),
    'save_post' => array(
      'mtrn_table_meta_box_nonce' => 'nonce', 'post_type' => 'mtrn_table',
      'mtrn_tournament_id' => ' golden1 ', 'mtrn_width' => '640', 'mtrn_height' => '480',
      'mtrn_font_size' => '11px', 'mtrn_header_font_size' => '12', 'mtrn_logo_size' => '24',
      'mtrn_table_padding' => '4', 'mtrn_inner_padding' => '6',
      'mtrn_bsizeh' => '2', 'mtrn_bsizev' => '3', 'mtrn_bsizeoh' => '4', 'mtrn_bsizeov' => '5', 'mtrn_bbsize' => '6',
      'mtrn_text_color' => '#111111', 'mtrn_main_color' => '#222222', 'mtrn_border_color' => 'not-a-color', 'mtrn_head_bottom_border_color' => '#444444',
      'mtrn_bg_color' => '#555555', 'mtrn_bg_opacity' => '50', 'mtrn_head_bg_color' => '#666666', 'mtrn_head_bg_opacity' => '150',
      'mtrn_even_bg_color' => '#777777', 'mtrn_even_bg_opacity' => '100', 'mtrn_odd_bg_color' => '#888888', 'mtrn_odd_bg_opacity' => '25',
      'mtrn_hover_bg_color' => '#999999', 'mtrn_hover_bg_opacity' => '75',
      'mtrn_suppress_wins' => '1', 'mtrn_projector_presentation' => '1', 'mtrn_responsive' => '1',
      'mtrn_language' => 'xx', 'mtrn_group' => '2',
    ),
  ),

  'table-final-round' => array(
    'type' => 'table', 'post_id' => 103,
    'meta' => array('_mtrn_tournament_id' => 'golden1', '_mtrn_language' => 'fr', '_mtrn_group' => '90', '_mtrn_navigation_for_groups' => '1', '_mtrn_suppress_logos' => '1'),
    'atts' => array('id' => 'golden1', 'lang' => 'fr', 'group' => '90', 'nav' => '1', 'sl' => '1'),
  ),

  'table-attrs-override-saved' => array(
    'type' => 'table', 'post_id' => 102,
    'meta' => $table_full_meta,
    'atts' => array('post_id' => '102', 'lang' => 'en', 's-wrap' => 'false', 'sw' => '0', 'group' => '1', 's-maincolor' => '123456'),
  ),

  'table-single-group-tournament' => array(
    'type' => 'table', 'post_id' => 104,
    'meta' => array('_mtrn_tournament_id' => 'single1', '_mtrn_language' => 'pl'),
    'atts' => array('id' => 'single1', 'lang' => 'pl'),
  ),

  'table-no-tournament' => array(
    'type' => 'table', 'post_id' => 105,
    'meta' => array(),
    'atts' => array(),
  ),

  'matches-defaults' => array(
    'type' => 'matches', 'post_id' => 201,
    'meta' => array('_mtrn_tournament_id' => 'golden1'),
    'atts' => array('id' => 'golden1'),
  ),

  'matches-customized' => array(
    'type' => 'matches', 'post_id' => 202,
    'meta' => $matches_full_meta,
    'atts' => array('id' => 'golden1', 'lang' => 'it', 'group' => '90', 'participant' => '3', 'gamenumbers' => '2-7', 'si' => '1', 'sf' => '1', 'se' => '1', 's-ehrsize' => '15', 's-wrap' => 'true'),
    'preview_post' => array(
      'tournament_id' => 'golden1', 'font_size' => '13', 'header_font_size' => '14', 'bsizeh' => '2', 'bsizev' => '2', 'bsizeoh' => '3', 'bsizeov' => '3', 'bbsize' => '4',
      'ehrsize' => '15', 'ehrtop' => '7', 'ehrbottom' => '1', 'table_padding' => '3', 'inner_padding' => '8',
      'text_color' => 'aaaaaa', 'main_color' => 'bbbbb1', 'bg_color' => 'eeeeee1a', 'border_color' => 'cccccc', 'head_bottom_border_color' => 'dddddd',
      'even_bg_color' => 'a1a1a1b0', 'odd_bg_color' => 'b2b2b200', 'hover_bg_color' => 'c3c3c3ff', 'head_bg_color' => 'f1f1f1e6',
      'projector_presentation' => '1', 'si' => '1', 'sf' => '1', 'st' => '0', 'sg' => '1', 'sr' => '0', 'se' => '1', 'sp' => '0', 'sh' => '1', 'responsive' => '1',
      'language' => 'it', 'group' => '90', 'participant' => '3', 'match_number' => '2-7',
    ),
    'save_post' => array(
      'mtrn_matches_meta_box_nonce' => 'nonce', 'post_type' => 'mtrn_match_list',
      'mtrn_tournament_id' => 'golden1', 'mtrn_width' => '700', 'mtrn_height' => '900',
      'mtrn_font_size' => '13', 'mtrn_header_font_size' => '14', 'mtrn_ehrsize' => '15px', 'mtrn_ehrtop' => '7', 'mtrn_ehrbottom' => '1',
      'mtrn_table_padding' => '3', 'mtrn_inner_padding' => '8',
      'mtrn_bsizeh' => '2', 'mtrn_bsizev' => '2', 'mtrn_bsizeoh' => '3', 'mtrn_bsizeov' => '3', 'mtrn_bbsize' => '4',
      'mtrn_text_color' => '#aaaaaa', 'mtrn_main_color' => '#bbbbb1', 'mtrn_border_color' => '#cccccc', 'mtrn_head_bottom_border_color' => '#dddddd',
      'mtrn_bg_color' => '#eeeeee', 'mtrn_bg_opacity' => '10', 'mtrn_head_bg_color' => '#f1f1f1', 'mtrn_head_bg_opacity' => '90',
      'mtrn_even_bg_color' => '#a1a1a1', 'mtrn_even_bg_opacity' => '69', 'mtrn_odd_bg_color' => '#b2b2b2', 'mtrn_odd_bg_opacity' => '0',
      'mtrn_hover_bg_color' => '#c3c3c3', 'mtrn_hover_bg_opacity' => '100',
      'mtrn_projector_presentation' => '1', 'mtrn_si' => '1', 'mtrn_sf' => '1', 'mtrn_se' => '1', 'mtrn_sh' => '1', 'mtrn_responsive' => '1',
      'mtrn_language' => 'it', 'mtrn_group' => '90', 'mtrn_participant' => '3', 'mtrn_match_number' => '2-7',
    ),
  ),

  'matches-all-participants' => array(
    'type' => 'matches', 'post_id' => 203,
    'meta' => array('_mtrn_tournament_id' => 'golden1', '_mtrn_language' => 'es', '_mtrn_group' => '', '_mtrn_participant' => '-1', '_mtrn_st' => '1', '_mtrn_sr' => '1'),
    'atts' => array('id' => 'golden1', 'lang' => 'es', 'participant' => '-1', 'st' => '1', 'sr' => '1'),
  ),

  'matches-attrs-override-saved' => array(
    'type' => 'matches', 'post_id' => 202,
    'meta' => $matches_full_meta,
    'atts' => array('post_id' => '202', 'lang' => 'en', 'participant' => '1', 'gamenumbers' => '4', 'group' => '1', 'si' => '0', 's-wrap' => 'false'),
  ),

  'matches-single-group-tournament' => array(
    'type' => 'matches', 'post_id' => 204,
    'meta' => array('_mtrn_tournament_id' => 'single1'),
    'atts' => array('id' => 'single1'),
  ),

  'matches-no-tournament' => array(
    'type' => 'matches', 'post_id' => 205,
    'meta' => array(),
    'atts' => array(),
  ),
);
