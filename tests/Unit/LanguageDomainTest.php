<?php

declare(strict_types=1);

namespace MTRN\Tests\Unit;

use Brain\Monkey\Functions;
use MTRN_Admin_Utilities;

class LanguageDomainTest extends TestCase {

  /**
   * @dataProvider languageDomains
   */
  public function test_each_language_maps_to_its_service_domain(string $language, string $domain): void {
    $this->assertSame($domain, MTRN_Admin_Utilities::get_api_base_url($language));
  }

  public function languageDomains(): array {
    return array(
      'German' => array('de', 'https://www.meinturnierplan.de'),
      'English' => array('en', 'https://tournamentbase.co.uk'),
      'French' => array('fr', 'https://tournoiplus.fr'),
      'Polish' => array('pl', 'https://turniej.pl'),
      'Spanish' => array('es', 'https://torneo.es'),
      'Italian' => array('it', 'https://torneo.it'),
      'Slovenian falls back to German' => array('sl', 'https://www.meinturnierplan.de'),
      'Turkish falls back to German' => array('tr', 'https://www.meinturnierplan.de'),
      'Croatian falls back to German' => array('hr', 'https://www.meinturnierplan.de'),
      'Unknown falls back to German' => array('xx', 'https://www.meinturnierplan.de'),
    );
  }

  public function test_json_url_uses_the_language_domain_and_encodes_the_id(): void {
    $this->assertSame('https://turniej.pl/json/json.php?id=abc', MTRN_Admin_Utilities::build_tournament_json_url('abc', 'pl'));
    $this->assertSame('https://www.meinturnierplan.de/json/json.php?id=a%26b%20c', MTRN_Admin_Utilities::build_tournament_json_url('a&b c', 'de'));
  }

  public function test_default_language_follows_the_site_locale(): void {
    Functions\when('is_user_logged_in')->justReturn(false);

    Functions\when('get_locale')->justReturn('de_AT');
    $this->assertSame('de', MTRN_Admin_Utilities::get_default_language());

    Functions\when('get_locale')->justReturn('sl_SI');
    $this->assertSame('sl', MTRN_Admin_Utilities::get_default_language());

    Functions\when('get_locale')->justReturn('pt_BR');
    $this->assertSame('en', MTRN_Admin_Utilities::get_default_language());
  }

  public function test_default_language_prefers_the_user_locale(): void {
    Functions\when('is_user_logged_in')->justReturn(true);
    Functions\when('get_current_user_id')->justReturn(5);
    Functions\when('get_user_meta')->justReturn('fr_CA');
    Functions\when('get_locale')->justReturn('de_DE');

    $this->assertSame('fr', MTRN_Admin_Utilities::get_default_language());
  }
}
