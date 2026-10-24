<?php

declare(strict_types=1);

namespace MTRN\Tests\Unit;

use Brain\Monkey\Functions;
use MTRN_Admin_Utilities;

/**
 * Tests for the JSON API helpers (fetch_tournament_groups / teams / option)
 * with a stubbed HTTP layer and an in-memory transient store.
 */
class TournamentApiTest extends TestCase {

  /** @var array<string, mixed> */
  private $transients = array();

  /** @var array<int, string> URLs requested through wp_remote_get */
  private $requests = array();

  protected function setUp(): void {
    parent::setUp();
    $this->transients = array();
    $this->requests = array();

    Functions\when('get_transient')->alias(function ($key) {
      return array_key_exists($key, $this->transients) ? $this->transients[$key] : false;
    });
    Functions\when('set_transient')->alias(function ($key, $value, $expiry = 0) {
      $this->transients[$key] = $value;
      return true;
    });
    Functions\when('delete_transient')->alias(function ($key) {
      unset($this->transients[$key]);
      return true;
    });
    Functions\when('is_wp_error')->alias(static function ($thing) {
      return is_object($thing) && isset($thing->is_error);
    });
    Functions\when('wp_remote_retrieve_response_code')->alias(static function ($response) {
      return is_array($response) ? $response['response']['code'] : '';
    });
    Functions\when('wp_remote_retrieve_body')->alias(static function ($response) {
      return is_array($response) ? $response['body'] : '';
    });
  }

  private function respondWith(int $code, $payload): void {
    Functions\when('wp_remote_get')->alias(function ($url) use ($code, $payload) {
      $this->requests[] = $url;
      return array('response' => array('code' => $code), 'body' => is_string($payload) ? $payload : json_encode($payload));
    });
  }

  private function failWithTransportError(): void {
    Functions\when('wp_remote_get')->alias(function ($url) {
      $this->requests[] = $url;
      return (object) array('is_error' => true);
    });
  }

  public function test_groups_and_final_round_are_read_from_a_multi_group_tournament(): void {
    $this->respondWith(200, array(
      'groups' => array(array('displayId' => 'A'), array('displayId' => 'B')),
      'finalMatches' => array(array('gameId' => '13')),
    ));

    $result = MTRN_Admin_Utilities::fetch_tournament_groups('t1', false, 'de');

    $this->assertSame(array(array('displayId' => 'A'), array('displayId' => 'B')), $result['groups']);
    $this->assertTrue($result['hasFinalRound']);
    $this->assertSame(array('https://www.meinturnierplan.de/json/json.php?id=t1'), $this->requests);
    $this->assertSame($result, $this->transients['mtrn_groups_de_t1'], 'Successful responses are cached');
  }

  public function test_single_group_tournament_has_no_groups_key(): void {
    $this->respondWith(200, array('participants' => array('1' => array('id' => 1, 'name' => 'A', 'displayId' => '1'))));

    $result = MTRN_Admin_Utilities::fetch_tournament_groups('t2');

    $this->assertSame(array(), $result['groups']);
    $this->assertFalse($result['hasFinalRound']);
  }

  public function test_final_round_detection_falls_back_to_rank_tables(): void {
    $this->respondWith(200, array('finalRankTables' => array(array(array('rank' => 1)))));
    $this->assertTrue(MTRN_Admin_Utilities::fetch_tournament_groups('t3')['hasFinalRound']);

    $this->respondWith(200, array('finalRankTable' => array(array('rank' => 1, 'participant' => 5))));
    $this->assertTrue(MTRN_Admin_Utilities::fetch_tournament_groups('t4')['hasFinalRound']);

    $this->respondWith(200, array('finalRankTable' => array(array('rank' => 1))));
    $this->assertFalse(MTRN_Admin_Utilities::fetch_tournament_groups('t5')['hasFinalRound'], 'Entries without a participant are not a final round');
  }

  public function test_error_responses_are_not_cached(): void {
    $this->respondWith(500, array('error' => 'Tournament not found for slug nope'));

    $result = MTRN_Admin_Utilities::fetch_tournament_groups('nope');

    $this->assertSame(array('groups' => array(), 'hasFinalRound' => false), $result);
    $this->assertArrayNotHasKey('mtrn_groups_en_nope', $this->transients);
  }

  public function test_transport_errors_return_an_empty_result_without_caching(): void {
    $this->failWithTransportError();

    $this->assertSame(array('groups' => array(), 'hasFinalRound' => false), MTRN_Admin_Utilities::fetch_tournament_groups('t6'));
    $this->assertSame(array(), $this->transients);
  }

  public function test_cached_groups_are_served_without_a_request(): void {
    $this->transients['mtrn_groups_en_t7'] = array('groups' => array(array('displayId' => 'C')), 'hasFinalRound' => false);
    $this->respondWith(200, array('groups' => array()));

    $result = MTRN_Admin_Utilities::fetch_tournament_groups('t7');

    $this->assertSame('C', $result['groups'][0]['displayId']);
    $this->assertSame(array(), $this->requests);
  }

  public function test_force_refresh_bypasses_the_cache(): void {
    $this->transients['mtrn_groups_en_t8'] = array('groups' => array(), 'hasFinalRound' => false);
    $this->respondWith(200, array('groups' => array(array('displayId' => 'A'))));

    $result = MTRN_Admin_Utilities::fetch_tournament_groups('t8', true);

    $this->assertCount(1, $result['groups']);
    $this->assertCount(1, $this->requests);
  }

  public function test_legacy_cache_format_is_upgraded(): void {
    $this->transients['mtrn_groups_en_t9'] = array(array('displayId' => 'A'));

    $this->assertSame(
      array('groups' => array(array('displayId' => 'A')), 'hasFinalRound' => false),
      MTRN_Admin_Utilities::fetch_tournament_groups('t9')
    );
  }

  public function test_teams_are_read_from_the_keyed_participants_object(): void {
    $this->respondWith(200, array('participants' => array(
      '6208922' => array('id' => 6208922, 'name' => '7a', 'displayId' => '1'),
      '6208923' => array('id' => 6208923, 'name' => '7b', 'displayId' => '2'),
    )));

    $teams = MTRN_Admin_Utilities::fetch_tournament_teams('t1', false, 'fr');

    $this->assertSame(array('7a', '7b'), array_column($teams, 'name'));
    $this->assertSame(array('1', '2'), array_column($teams, 'displayId'));
    $this->assertSame(array('https://tournoiplus.fr/json/json.php?id=t1'), $this->requests);
    $this->assertSame($teams, $this->transients['mtrn_teams_fr_t1']);
  }

  public function test_teams_error_response_is_not_cached(): void {
    $this->respondWith(500, array('error' => 'nope'));

    $this->assertSame(array(), MTRN_Admin_Utilities::fetch_tournament_teams('nope'));
    $this->assertSame(array(), $this->transients);
  }

  public function test_tournament_option_is_read_and_the_payload_cached(): void {
    $this->respondWith(200, array('showCourts' => true, 'showReferees' => false));

    $this->assertTrue(MTRN_Admin_Utilities::fetch_tournament_option('t1', 'showCourts'));
    $this->assertFalse(MTRN_Admin_Utilities::fetch_tournament_option('t1', 'showReferees'));
    $this->assertNull(MTRN_Admin_Utilities::fetch_tournament_option('t1', 'showGroups'));
    $this->assertCount(1, $this->requests, 'The second and third lookups come from the cache');
  }

  public function test_tournament_option_returns_null_on_errors_without_caching(): void {
    $this->respondWith(500, array('error' => 'nope'));

    $this->assertNull(MTRN_Admin_Utilities::fetch_tournament_option('nope', 'showCourts'));
    $this->assertSame(array(), $this->transients);
  }

  public function test_empty_arguments_short_circuit(): void {
    Functions\expect('wp_remote_get')->never();

    $this->assertSame(array('groups' => array(), 'hasFinalRound' => false), MTRN_Admin_Utilities::fetch_tournament_groups(''));
    $this->assertSame(array(), MTRN_Admin_Utilities::fetch_tournament_teams(''));
    $this->assertNull(MTRN_Admin_Utilities::fetch_tournament_option('', 'showCourts'));
    $this->assertNull(MTRN_Admin_Utilities::fetch_tournament_option('t1', ''));
  }
}
