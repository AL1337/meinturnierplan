<?php

declare(strict_types=1);

namespace MTRN\Tests\Unit;

use MTRN_Blocks;
use MTRN_Embed_Renderer;

/**
 * The blocks only store a post ID; everything else comes from the post meta.
 * These tests make sure the saved settings reach the embed URL.
 */
class BlockRenderTest extends TestCase {

  public function test_table_block_without_a_post_shows_a_placeholder(): void {
    $block = new MTRN_Blocks(new MTRN_Embed_Renderer());

    $this->assertStringContainsString('mtrn-block-placeholder', $block->render_block('table', array()));
    $this->assertStringContainsString('mtrn-block-placeholder', $block->render_block('table', array('tableId' => '')));
    $this->assertStringContainsString('Please select a Matches Table.', $block->render_block('matches', array('tableId' => '0')));
  }

  public function test_table_block_renders_the_saved_settings(): void {
    $this->meta[42] = array(
      '_mtrn_tournament_id' => 'saved42',
      '_mtrn_language' => 'es',
      '_mtrn_group' => '3',
      '_mtrn_responsive' => '1',
      '_mtrn_navigation_for_groups' => '1',
      '_mtrn_suppress_logos' => '1',
      '_mtrn_text_color' => '112233',
      '_mtrn_head_bg_color' => 'eeeeff',
      '_mtrn_head_bg_opacity' => '50',
    );
    $block = new MTRN_Blocks(new MTRN_Embed_Renderer());

    $html = $block->render_block('table', array('tableId' => '42'));
    $query = $this->query($html);

    $this->assertSame('torneo.es', $this->host($html));
    $this->assertSame('/displayTable.php', $this->path($html));
    $this->assertSame('saved42', $query['id']);
    $this->assertSame('es', $query['setlang']);
    $this->assertSame('3', $query['gr']);
    $this->assertSame('112233', $query['s']['color']);
    $this->assertSame('eeeeff80', $query['s']['bghead']);
    $this->assertSame(array('nav', 'sl'), $this->flags($html));
    $this->assertHasResponsiveWrapper($html);
  }

  public function test_matches_block_renders_the_saved_settings(): void {
    $this->meta[7] = array(
      '_mtrn_tournament_id' => 'saved7',
      '_mtrn_language' => 'de',
      '_mtrn_participant' => '4',
      '_mtrn_match_number' => '1-5',
      '_mtrn_sf' => '1',
      '_mtrn_st' => '1',
      '_mtrn_ehrsize' => '14',
    );
    $block = new MTRN_Blocks(new MTRN_Embed_Renderer());

    $html = $block->render_block('matches', array('tableId' => '7'));
    $query = $this->query($html);

    $this->assertSame('www.meinturnierplan.de', $this->host($html));
    $this->assertSame('/displayMatches.php', $this->path($html));
    $this->assertSame('saved7', $query['id']);
    $this->assertSame('de', $query['setlang']);
    $this->assertSame('4', $query['tm']);
    $this->assertSame('1-5', $query['mn']);
    $this->assertSame('14', $query['s']['ehrsize']);
    $this->assertSame(array('sf', 'st'), $this->flags($html));
    $this->assertHasNoResponsiveWrapper($html);
  }
}
