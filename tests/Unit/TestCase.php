<?php

declare(strict_types=1);

namespace MTRN\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case: boots Brain Monkey and stubs the handful of WordPress
 * functions the plugin's rendering code relies on.
 */
abstract class TestCase extends PHPUnitTestCase {

  use MockeryPHPUnitIntegration;

  /**
   * Fake post meta: [post_id => [meta_key => value]].
   *
   * @var array<int, array<string, string>>
   */
  protected $meta = array();

  protected function setUp(): void {
    parent::setUp();
    Monkey\setUp();

    Functions\stubTranslationFunctions();
    Functions\when('esc_attr')->alias(static function ($value) {
      return htmlspecialchars((string) $value, ENT_QUOTES);
    });
    Functions\when('esc_html')->alias(static function ($value) {
      return htmlspecialchars((string) $value, ENT_QUOTES);
    });
    Functions\when('esc_url')->returnArg();
    Functions\when('wp_json_encode')->alias(static function ($data) {
      return json_encode($data);
    });
    Functions\when('sanitize_text_field')->alias(static function ($value) {
      return trim(strip_tags((string) $value));
    });
    Functions\when('add_shortcode')->justReturn(null);
    Functions\when('wp_create_nonce')->justReturn('nonce');
    Functions\when('admin_url')->alias(static function ($path = '') {
      return 'https://example.test/wp-admin/' . $path;
    });
    Functions\when('shortcode_atts')->alias(static function (array $pairs, $atts, $shortcode = '') {
      $atts = (array) $atts;
      $out = array();
      foreach ($pairs as $name => $default) {
        $out[$name] = array_key_exists($name, $atts) ? $atts[$name] : $default;
      }
      return $out;
    });
    Functions\when('get_post_meta')->alias(function ($post_id, $key = '', $single = false) {
      return isset($this->meta[$post_id][$key]) ? $this->meta[$post_id][$key] : '';
    });
  }

  protected function tearDown(): void {
    Monkey\tearDown();
    parent::tearDown();
  }

  /**
   * Extract the iframe src from rendered embed HTML.
   */
  protected function embedUrl(string $html): string {
    $this->assertSame(1, preg_match('/<iframe[^>]*\ssrc="([^"]+)"/', $html, $m), 'No iframe src found in: ' . $html);
    return html_entity_decode($m[1], ENT_QUOTES);
  }

  protected function host(string $html): string {
    return (string) parse_url($this->embedUrl($html), PHP_URL_HOST);
  }

  protected function path(string $html): string {
    return (string) parse_url($this->embedUrl($html), PHP_URL_PATH);
  }

  /**
   * Query parameters of the embed URL as an array (s[...] becomes nested).
   */
  protected function query(string $html): array {
    parse_str((string) parse_url($this->embedUrl($html), PHP_URL_QUERY), $query);
    return $query;
  }

  /**
   * Bare flag parameters (those sent without "=value"), sorted.
   */
  protected function flags(string $html): array {
    $parts = explode('&', (string) parse_url($this->embedUrl($html), PHP_URL_QUERY));
    $flags = array_values(array_filter($parts, static function ($part) {
      return $part !== '' && strpos($part, '=') === false;
    }));
    sort($flags);
    return $flags;
  }

  protected function assertHasResponsiveWrapper(string $html): void {
    $this->assertStringContainsString('<div class="mtrn-embed-responsive">', $html);
    $this->assertSame('true', $this->query($html)['s']['wrap']);
  }

  protected function assertHasNoResponsiveWrapper(string $html): void {
    $this->assertStringNotContainsString('mtrn-embed-responsive', $html);
    $this->assertSame('false', $this->query($html)['s']['wrap']);
  }
}
