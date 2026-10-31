<?php

declare(strict_types=1);

namespace MTRN\Tests\Unit;

use MTRN_Embed_Renderer;
use MTRN_Shortcodes;

class MatchesShortcodeTest extends TestCase {

  private function render(array $atts): string {
    $shortcodes = new MTRN_Shortcodes(new MTRN_Embed_Renderer());
    return $shortcodes->render('matches', $atts);
  }

  public function test_id_only_renders_english_embed_with_defaults(): void {
    $html = $this->render(array('id' => 'm1'));
    $query = $this->query($html);

    $this->assertSame('tournamentbase.co.uk', $this->host($html));
    $this->assertSame('/displayMatches.php', $this->path($html));
    $this->assertSame('m1', $query['id']);
    $this->assertArrayNotHasKey('setlang', $query);
    $this->assertArrayNotHasKey('gr', $query);
    $this->assertArrayNotHasKey('tm', $query);
    $this->assertArrayNotHasKey('mn', $query);
    $this->assertSame(array(), $this->flags($html));
    $this->assertSame('10', $query['s']['ehrsize']);
    $this->assertSame('9', $query['s']['ehrtop']);
    $this->assertSame('3', $query['s']['ehrbottom']);
    $this->assertMatchesRegularExpression('/<iframe id="mtrn-matches-m1-[0-9a-f]{8}"/', $html);
    $this->assertHasNoResponsiveWrapper($html);
  }

  public function test_filters_and_suppress_flags(): void {
    $html = $this->render(array(
      'id' => 'm1', 'lang' => 'pl', 'group' => '90', 'participant' => '3', 'gamenumbers' => '2-7',
      'sf' => '1', 'se' => '1', 'sh' => '1', 'si' => '0', 's-wrap' => 'true',
    ));
    $query = $this->query($html);

    $this->assertSame('turniej.pl', $this->host($html));
    $this->assertSame('pl', $query['setlang']);
    $this->assertSame('90', $query['gr']);
    $this->assertSame('3', $query['tm']);
    $this->assertSame('2-7', $query['mn']);
    $this->assertSame(array('se', 'sf', 'sh'), $this->flags($html));
    $this->assertHasResponsiveWrapper($html);
  }

  public function test_participant_all_is_not_sent(): void {
    $this->assertArrayNotHasKey('tm', $this->query($this->render(array('id' => 'm1', 'participant' => '-1'))));
    $this->assertArrayNotHasKey('tm', $this->query($this->render(array('id' => 'm1', 'participant' => ''))));
  }

  public function test_post_id_only_uses_the_saved_settings(): void {
    $this->meta[7] = array(
      '_mtrn_tournament_id' => 'saved7',
      '_mtrn_language' => 'it',
      '_mtrn_group' => '',
      '_mtrn_participant' => '7',
      '_mtrn_match_number' => '4',
      '_mtrn_responsive' => '1',
      '_mtrn_sr' => '1',
      '_mtrn_projector_presentation' => '1',
    );

    $html = $this->render(array('post_id' => '7'));
    $query = $this->query($html);

    $this->assertSame('saved7', $query['id']);
    $this->assertSame('torneo.it', $this->host($html));
    $this->assertSame('it', $query['setlang']);
    $this->assertArrayNotHasKey('gr', $query, 'Empty saved group means all matches');
    $this->assertSame('7', $query['tm']);
    $this->assertSame('4', $query['mn']);
    $this->assertSame(array('bm', 'sr'), $this->flags($html));
    $this->assertHasResponsiveWrapper($html);
  }

  public function test_saved_participant_all_is_not_sent(): void {
    $this->meta[7] = array('_mtrn_tournament_id' => 'saved7', '_mtrn_participant' => '-1');

    $this->assertArrayNotHasKey('tm', $this->query($this->render(array('post_id' => '7'))));
  }

  public function test_fallback_content_links_to_the_tournament(): void {
    $html = $this->render(array('id' => 'm1'));

    $this->assertStringContainsString(
      '<p>Your browser does not support the tournament widget. <a href="https://tournamentbase.co.uk/showit.php?id=m1">Go to Tournament.</a></p>',
      $html
    );
  }

  public function test_without_tournament_id_a_placeholder_is_rendered(): void {
    $html = $this->render(array());

    $this->assertStringContainsString('mtrn-empty-preview', $html);
    $this->assertStringNotContainsString('<iframe', $html);
  }
}
