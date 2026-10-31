<?php

declare(strict_types=1);

namespace MTRN\Tests\Unit;

use MTRN_Embed_Renderer;
use MTRN_Shortcodes;

class TableShortcodeTest extends TestCase {

  private function render(array $atts): string {
    $shortcodes = new MTRN_Shortcodes(new MTRN_Embed_Renderer());
    return $shortcodes->render('table', $atts);
  }

  public function test_id_only_renders_english_embed_with_defaults(): void {
    $html = $this->render(array('id' => 'abc123'));
    $query = $this->query($html);

    $this->assertSame('tournamentbase.co.uk', $this->host($html));
    $this->assertSame('/displayTable.php', $this->path($html));
    $this->assertSame('abc123', $query['id']);
    $this->assertArrayNotHasKey('setlang', $query, 'English is the service default and must not be sent');
    $this->assertArrayNotHasKey('gr', $query);
    $this->assertSame(array(), $this->flags($html));
    $this->assertSame('9', $query['s']['size']);
    $this->assertSame('10', $query['s']['sizeheader']);
    $this->assertSame('173f75', $query['s']['maincolor']);
    $this->assertSame('20', $query['s']['logosize']);
    $this->assertSame('f0f8ffb0', $query['s']['bgeven']);
    $this->assertHasNoResponsiveWrapper($html);
  }

  public function test_markup_has_the_id_prefix_the_frontend_script_relies_on(): void {
    $html = $this->render(array('id' => 'abc123'));

    $this->assertMatchesRegularExpression('/<iframe id="mtrn-table-abc123-[0-9a-f]{8}" class="mtrn-embed-frame"/', $html);
    $this->assertStringContainsString('width="300" height="200"', $html);
  }

  public function test_fallback_content_links_to_the_tournament(): void {
    $html = $this->render(array('id' => 'abc123', 'lang' => 'de'));

    $this->assertStringContainsString(
      '<p>Your browser does not support the tournament widget. <a href="https://www.meinturnierplan.de/showit.php?id=abc123">Go to Tournament.</a></p>',
      $html
    );
  }

  public function test_language_group_and_flags(): void {
    $html = $this->render(array('id' => 'abc123', 'lang' => 'de', 'group' => '90', 'sw' => '1', 'nav' => '1', 'bm' => '1', 'sl' => '0'));
    $query = $this->query($html);

    $this->assertSame('www.meinturnierplan.de', $this->host($html));
    $this->assertSame('de', $query['setlang']);
    $this->assertSame('90', $query['gr']);
    $this->assertSame(array('bm', 'nav', 'sw'), $this->flags($html));
  }

  public function test_styling_attributes_are_forwarded(): void {
    $html = $this->render(array('id' => 'abc123', 's-color' => 'ff0000', 's-bgcolor' => '11223344', 's-logosize' => '30', 's-bbsize' => '4'));
    $query = $this->query($html);

    $this->assertSame('ff0000', $query['s']['color']);
    $this->assertSame('11223344', $query['s']['bgcolor']);
    $this->assertSame('30', $query['s']['logosize']);
    $this->assertSame('4', $query['s']['bbsize']);
  }

  public function test_width_and_height_attributes(): void {
    $html = $this->render(array('id' => 'abc123', 'width' => '500', 'height' => '400'));

    $this->assertStringContainsString('width="500" height="400"', $html);
  }

  public function test_responsive_attribute_accepts_true_and_1(): void {
    $this->assertHasResponsiveWrapper($this->render(array('id' => 'abc123', 's-wrap' => 'true')));
    $this->assertHasResponsiveWrapper($this->render(array('id' => 'abc123', 's-wrap' => '1')));
    $this->assertHasResponsiveWrapper($this->render(array('id' => 'abc123', 's-wrap' => 'TRUE')));
    $this->assertHasNoResponsiveWrapper($this->render(array('id' => 'abc123', 's-wrap' => 'false')));
  }

  public function test_post_id_only_uses_the_saved_settings(): void {
    $this->meta[42] = array(
      '_mtrn_tournament_id' => 'saved1',
      '_mtrn_language' => 'de',
      '_mtrn_group' => '2',
      '_mtrn_responsive' => '1',
      '_mtrn_suppress_wins' => '1',
      '_mtrn_main_color' => 'abcdef',
    );

    $html = $this->render(array('post_id' => '42'));
    $query = $this->query($html);

    $this->assertSame('saved1', $query['id']);
    $this->assertSame('www.meinturnierplan.de', $this->host($html));
    $this->assertSame('de', $query['setlang']);
    $this->assertSame('2', $query['gr']);
    $this->assertSame('abcdef', $query['s']['maincolor']);
    $this->assertSame(array('sw'), $this->flags($html));
    $this->assertHasResponsiveWrapper($html);
  }

  public function test_explicit_attributes_override_the_saved_settings(): void {
    $this->meta[42] = array(
      '_mtrn_tournament_id' => 'saved1',
      '_mtrn_language' => 'de',
      '_mtrn_group' => '2',
      '_mtrn_responsive' => '1',
    );

    $html = $this->render(array('post_id' => '42', 'id' => 'other', 'lang' => 'en', 'group' => '1', 's-wrap' => 'false'));
    $query = $this->query($html);

    $this->assertSame('other', $query['id']);
    $this->assertSame('tournamentbase.co.uk', $this->host($html));
    $this->assertArrayNotHasKey('setlang', $query);
    $this->assertSame('1', $query['gr']);
    $this->assertHasNoResponsiveWrapper($html);
  }

  public function test_explicit_zero_flag_and_styling_override_the_saved_settings(): void {
    $this->meta[42] = array('_mtrn_tournament_id' => 'saved1', '_mtrn_suppress_wins' => '1', '_mtrn_main_color' => 'abcdef');

    $html = $this->render(array('post_id' => '42', 'sw' => '0', 's-maincolor' => '123456'));

    $this->assertSame(array(), $this->flags($html), 'An explicit sw="0" (as the admin preview sends) wins over the saved setting');
    $this->assertSame('123456', $this->query($html)['s']['maincolor']);
  }

  public function test_without_tournament_id_a_placeholder_is_rendered(): void {
    $html = $this->render(array());

    $this->assertStringContainsString('mtrn-empty-preview', $html);
    $this->assertStringNotContainsString('<iframe', $html);
  }
}
