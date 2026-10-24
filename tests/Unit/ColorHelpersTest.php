<?php

declare(strict_types=1);

namespace MTRN\Tests\Unit;

use Brain\Monkey\Functions;
use MTRN_Admin_Utilities;

class ColorHelpersTest extends TestCase {

  public function test_combine_color_opacity(): void {
    $this->assertSame('ff000080', MTRN_Admin_Utilities::combine_color_opacity('#ff0000', 50));
    $this->assertSame('ff0000ff', MTRN_Admin_Utilities::combine_color_opacity('ff0000', ''));
    $this->assertSame('ff0000ff', MTRN_Admin_Utilities::combine_color_opacity('ff0000', null));
    $this->assertSame('ff000000', MTRN_Admin_Utilities::combine_color_opacity('ff0000', 0));
    $this->assertSame('ff000080', MTRN_Admin_Utilities::combine_color_opacity('ff000080', 10), 'An 8-digit color already carries its alpha');
  }

  public function test_hex_to_rgba(): void {
    $this->assertSame('rgba(255, 0, 0, 0.5)', MTRN_Admin_Utilities::hex_to_rgba('#ff000080'));
    $this->assertSame('rgb(255, 0, 0)', MTRN_Admin_Utilities::hex_to_rgba('ff0000'));
    $this->assertSame('rgb(255, 0, 0)', MTRN_Admin_Utilities::hex_to_rgba('f00'));
    $this->assertSame('transparent', MTRN_Admin_Utilities::hex_to_rgba('zz'));
  }

  public function test_background_color_with_opacity_from_meta(): void {
    $this->assertSame('00000000', MTRN_Admin_Utilities::get_bg_color_with_opacity(0, '_mtrn_bg_color'), 'No post: transparent');

    $this->meta[1] = array('_mtrn_bg_color' => 'ffffff', '_mtrn_even_bg_color' => 'f0f8ff', '_mtrn_odd_bg_color' => 'ffffff', '_mtrn_odd_bg_opacity' => '0');
    $this->assertSame('ffffff00', MTRN_Admin_Utilities::get_bg_color_with_opacity(1, '_mtrn_bg_color'), 'Main background defaults to fully transparent');
    $this->assertSame('f0f8ffb0', MTRN_Admin_Utilities::get_bg_color_with_opacity(1, '_mtrn_even_bg_color'), 'Row colors default to 69% opacity');
    $this->assertSame('ffffff00', MTRN_Admin_Utilities::get_bg_color_with_opacity(1, '_mtrn_odd_bg_color'), 'A saved opacity of 0 is respected');

    $this->meta[2] = array('_mtrn_hover_bg_color' => '#112233', '_mtrn_hover_bg_opacity' => '100');
    $this->assertSame('112233ff', MTRN_Admin_Utilities::get_bg_color_with_opacity(2, '_mtrn_hover_bg_color'));
  }

  public function test_sanitize_meta_value(): void {
    Functions\when('absint')->alias(static function ($value) {
      return abs((int) $value);
    });
    Functions\when('sanitize_hex_color')->alias(static function ($color) {
      return preg_match('/^#([A-Fa-f0-9]{3}){1,2}$/', (string) $color) ? $color : '';
    });

    $this->assertSame('1', MTRN_Admin_Utilities::sanitize_meta_value('suppress_wins', '1'));
    $this->assertSame('0', MTRN_Admin_Utilities::sanitize_meta_value('suppress_wins', 'yes'));
    $this->assertSame('abcdef', MTRN_Admin_Utilities::sanitize_meta_value('main_color', '#abcdef'));
    $this->assertSame('', MTRN_Admin_Utilities::sanitize_meta_value('main_color', 'red'));
    $this->assertSame(100, MTRN_Admin_Utilities::sanitize_meta_value('bg_opacity', '150'));
    $this->assertSame(12, MTRN_Admin_Utilities::sanitize_meta_value('font_size', '12px'));
    $this->assertSame('de', MTRN_Admin_Utilities::sanitize_meta_value('language', 'de'));
    $this->assertSame('en', MTRN_Admin_Utilities::sanitize_meta_value('language', 'xx'));
    $this->assertSame('abc', MTRN_Admin_Utilities::sanitize_meta_value('tournament_id', ' <b>abc</b> '));
  }
}
