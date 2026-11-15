<?php

declare(strict_types=1);

namespace MTRN\Tests\Unit;

use Brain\Monkey\Functions;
use MTRN_Options;

class ColorHelpersTest extends TestCase {

  public function test_combine_color_opacity(): void {
    $this->assertSame('ff000080', MTRN_Options::combine_color_opacity('#ff0000', 50));
    $this->assertSame('ff0000ff', MTRN_Options::combine_color_opacity('ff0000', ''));
    $this->assertSame('ff0000ff', MTRN_Options::combine_color_opacity('ff0000', null));
    $this->assertSame('ff000000', MTRN_Options::combine_color_opacity('ff0000', 0));
    $this->assertSame('ff000080', MTRN_Options::combine_color_opacity('ff000080', 10), 'An 8-digit color already carries its alpha');
  }

  public function test_combine_color_opacity_is_exact_for_every_percentage(): void {
    foreach (range(0, 100) as $percent) {
      $expected = str_pad(dechex(intdiv($percent * 255 + 50, 100)), 2, '0', STR_PAD_LEFT);
      $this->assertSame('abcdef' . $expected, MTRN_Options::combine_color_opacity('abcdef', (string) $percent), "opacity $percent%");
    }

    // The percentages where round($o * 2.55) differs between PHP 8.3 and 8.4
    $this->assertSame('abcdef80', MTRN_Options::combine_color_opacity('abcdef', 50));
    $this->assertSame('abcdefe6', MTRN_Options::combine_color_opacity('abcdef', 90));
    $this->assertSame('abcdefff', MTRN_Options::combine_color_opacity('abcdef', 100));

    // Out-of-range values are clamped instead of producing a broken hex string
    $this->assertSame('abcdefff', MTRN_Options::combine_color_opacity('abcdef', 150));
    $this->assertSame('abcdef00', MTRN_Options::combine_color_opacity('abcdef', -5));
  }

  public function test_sanitize_by_option_type(): void {
    Functions\when('absint')->alias(static function ($value) {
      return abs((int) $value);
    });
    Functions\when('sanitize_hex_color')->alias(static function ($color) {
      return preg_match('/^#([A-Fa-f0-9]{3}){1,2}$/', (string) $color) ? $color : '';
    });

    $this->assertSame('1', MTRN_Options::sanitize('suppress_wins', '1'));
    $this->assertSame('0', MTRN_Options::sanitize('suppress_wins', 'yes'));
    $this->assertSame('abcdef', MTRN_Options::sanitize('main_color', '#abcdef'));
    $this->assertSame('', MTRN_Options::sanitize('main_color', 'red'));
    $this->assertSame(100, MTRN_Options::sanitize('bg_opacity', '150'));
    $this->assertSame(12, MTRN_Options::sanitize('font_size', '12px'));
    $this->assertSame('de', MTRN_Options::sanitize('language', 'de'));
    $this->assertSame('en', MTRN_Options::sanitize('language', 'xx'));
    $this->assertSame('abc', MTRN_Options::sanitize('tournament_id', ' <b>abc</b> '));
  }
}
