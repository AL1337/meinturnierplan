<?php

declare(strict_types=1);

namespace MTRN\Tests\Unit;

use Brain\Monkey\Functions;

/**
 * Golden (characterization) tests.
 *
 * For every case in tests/golden/cases.php this renders each output channel
 * of the plugin and compares it byte for byte with the stored expectation.
 * The expectations were generated from the 1.3.0 code and define the
 * behaviour the phase 2 refactor must preserve.
 *
 * Regenerate after an intentional change:
 *   MTRN_UPDATE_GOLDENS=1 vendor/bin/phpunit --filter GoldenTest
 */
class GoldenTest extends TestCase {

  private const EXPECTED_DIR = __DIR__ . '/../golden/expected';

  /** @var array<int, array{0:string,1:mixed}> update_post_meta calls recorded during a save */
  private $saved = array();

  /** @var mixed payload passed to wp_send_json_success */
  private $json = null;

  /** @var array<string, mixed> */
  private $transients = array();

  /** @var int */
  private $currentPostId = 0;

  /** @var string */
  private $currentPostType = '';

  protected function setUp(): void {
    parent::setUp();
    $this->saved = array();
    $this->json = null;
    $this->transients = array();

    Functions\when('checked')->alias(function ($checked, $current = true, $echo = true) {
      return $this->checkedSelected('checked', $checked, $current, $echo);
    });
    Functions\when('selected')->alias(function ($selected, $current = true, $echo = true) {
      return $this->checkedSelected('selected', $selected, $current, $echo);
    });
    Functions\when('wp_nonce_field')->alias(static function ($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
      $html = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="nonce" />';
      if ($echo) {
        echo $html;
      }
      return $html;
    });
    Functions\when('wp_verify_nonce')->justReturn(1);
    Functions\when('current_user_can')->justReturn(true);
    Functions\when('wp_unslash')->returnArg();
    Functions\when('absint')->alias(static function ($value) {
      return abs((int) $value);
    });
    Functions\when('sanitize_hex_color')->alias(static function ($color) {
      return preg_match('/^#([A-Fa-f0-9]{3}){1,2}$/', (string) $color) ? $color : '';
    });
    Functions\when('esc_textarea')->alias(static function ($value) {
      return htmlspecialchars((string) $value, ENT_QUOTES);
    });
    Functions\when('esc_js')->alias(static function ($value) {
      return addslashes((string) $value);
    });
    Functions\when('plugins_url')->alias(static function ($path = '', $plugin = '') {
      return 'https://example.test/wp-content/plugins/meinturnierplan/' . ltrim((string) $path, '/');
    });
    Functions\when('wp_enqueue_script')->justReturn(null);
    Functions\when('wp_localize_script')->justReturn(true);
    Functions\when('wp_enqueue_style')->justReturn(null);
    Functions\when('wp_register_style')->justReturn(true);
    Functions\when('is_user_logged_in')->justReturn(false);
    Functions\when('get_locale')->justReturn('en_US');
    Functions\when('is_singular')->justReturn(true);
    Functions\when('in_the_loop')->justReturn(true);
    Functions\when('is_main_query')->justReturn(true);
    Functions\when('get_post_type')->alias(function ($post = 0) {
      return $this->currentPostType;
    });
    Functions\when('get_the_ID')->alias(function () {
      return $this->currentPostId;
    });
    Functions\when('update_post_meta')->alias(function ($post_id, $key, $value) {
      $this->saved[] = array($key, $value);
      return true;
    });
    Functions\when('wp_send_json_success')->alias(function ($data = null) {
      $this->json = $data;
    });
    Functions\when('wp_die')->alias(static function ($message = '') {
      throw new \RuntimeException('wp_die: ' . $message);
    });

    // Deterministic tournament data instead of the live JSON API
    Functions\when('get_transient')->alias(function ($key) {
      return array_key_exists($key, $this->transients) ? $this->transients[$key] : false;
    });
    Functions\when('set_transient')->alias(function ($key, $value) {
      $this->transients[$key] = $value;
      return true;
    });
    Functions\when('delete_transient')->alias(function ($key) {
      unset($this->transients[$key]);
      return true;
    });
    Functions\when('is_wp_error')->justReturn(false);
    Functions\when('wp_remote_retrieve_response_code')->alias(static function ($response) {
      return $response['response']['code'];
    });
    Functions\when('wp_remote_retrieve_body')->alias(static function ($response) {
      return $response['body'];
    });
    Functions\when('wp_remote_get')->alias(static function ($url) {
      parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
      return self::tournamentResponse(isset($query['id']) ? $query['id'] : '');
    });
  }

  private static function tournamentResponse(string $id): array {
    if ($id === 'golden1') {
      $body = array(
        'id' => '1', 'slug' => 'golden1', 'name' => 'Golden Cup',
        'showCourts' => true, 'showGroups' => true, 'showReferees' => false,
        'groups' => array(array('displayId' => 'A'), array('displayId' => 'B')),
        'participants' => array(
          '11' => array('id' => 11, 'name' => 'Team One', 'displayId' => '1'),
          '12' => array('id' => 12, 'name' => 'Team Two', 'displayId' => '2'),
          '13' => array('id' => 13, 'name' => 'Team Three', 'displayId' => '3'),
          '14' => array('id' => 14, 'name' => 'Team Four', 'displayId' => '4'),
        ),
        'finalMatches' => array(array('gameId' => '9', 'displayId' => '9')),
        'finalRankTable' => array(array('rank' => 1, 'participant' => 11)),
      );
      return array('response' => array('code' => 200), 'body' => json_encode($body));
    }
    if ($id === 'single1') {
      $body = array(
        'id' => '2', 'slug' => 'single1', 'name' => 'Single Group',
        'showCourts' => false, 'showGroups' => false, 'showReferees' => false,
        'participants' => array(
          '21' => array('id' => 21, 'name' => 'Solo A', 'displayId' => '1'),
          '22' => array('id' => 22, 'name' => 'Solo B', 'displayId' => '2'),
        ),
      );
      return array('response' => array('code' => 200), 'body' => json_encode($body));
    }
    return array('response' => array('code' => 500), 'body' => json_encode(array('error' => 'Tournament not found for slug ' . $id)));
  }

  /**
   * The 8-character suffix of an embed's iframe id only makes the id unique
   * per instance; the resize script matches on the prefix. It is derived from
   * the attributes and changes whenever the internal representation does, so
   * it is masked before comparing.
   */
  private static function normalize(string $html): string {
    return preg_replace('/(id="mtrn-(?:table|matches)-.*?-)[0-9a-f]{8}"/', '$1HASH"', $html);
  }

  private function checkedSelected(string $type, $helper, $current, bool $echo): string {
    $result = ((string) $helper === (string) $current) ? " $type='$type'" : '';
    if ($echo) {
      echo $result;
    }
    return $result;
  }

  public static function cases(): array {
    $cases = require __DIR__ . '/../golden/cases.php';
    $out = array();
    foreach ($cases as $name => $case) {
      $out[$name] = array($name, $case);
    }
    return $out;
  }

  /**
   * @dataProvider cases
   */
  public function test_output_matches_golden(string $name, array $case): void {
    $this->meta = array();
    if (!empty($case['meta'])) {
      $this->meta[$case['post_id']] = $case['meta'];
    }

    $actual = $this->renderChannels($case);
    $update = (bool) getenv('MTRN_UPDATE_GOLDENS');
    $dir = self::EXPECTED_DIR . '/' . $name;

    if ($update) {
      if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
      }
      // Remove stale PHP goldens; js-* files belong to the Playwright golden spec
      foreach (glob($dir . '/*') as $stale) {
        if (strpos(basename($stale), 'js-') !== 0) {
          unlink($stale);
        }
      }
    }

    foreach ($actual as $channel => $output) {
      $file = $dir . '/' . $channel;
      if ($update) {
        file_put_contents($file, $output);
        $this->addToAssertionCount(1);
        continue;
      }
      $this->assertFileExists($file, "Missing golden file for $name/$channel. Run MTRN_UPDATE_GOLDENS=1 vendor/bin/phpunit --filter GoldenTest");
      $this->assertSame(self::normalize(file_get_contents($file)), self::normalize($output), "Output of channel '$channel' changed for case '$name'");
    }

    if (!$update) {
      // js-* files are written by tests/e2e/admin-js-golden.spec.js, not by this test
      $expectedChannels = array_values(array_filter(array_map('basename', glob($dir . '/*')), static function ($file) {
        return strpos($file, 'js-') !== 0;
      }));
      sort($expectedChannels);
      $actualChannels = array_keys($actual);
      sort($actualChannels);
      $this->assertSame($expectedChannels, $actualChannels, "Channel set changed for case '$name'");
    }
  }

  /**
   * Render every channel that applies to a case.
   *
   * @return array<string, string> channel file name => output
   */
  private function renderChannels(array $case): array {
    $isTable = $case['type'] === 'table';
    $postId = (int) $case['post_id'];
    $out = array();

    // Case descriptor for the browser-side golden (tests/e2e/admin-js-golden.spec.js)
    $out['case.json'] = json_encode(array('type' => $case['type'], 'post_id' => $postId, 'tournament_id' => isset($case['meta']['_mtrn_tournament_id']) ? $case['meta']['_mtrn_tournament_id'] : ''), JSON_PRETTY_PRINT) . "\n";

    $renderer = $isTable ? new \MTRN_Table_Renderer() : new \MTRN_Matches_Renderer();
    $shortcode = $isTable ? new \MTRN_Table_Shortcode($renderer) : new \MTRN_Matches_Shortcode($renderer);

    if (isset($case['atts'])) {
      $out['shortcode.html'] = $shortcode->shortcode_callback($case['atts']);
    }

    $out['shortcode-post-only.html'] = $shortcode->shortcode_callback(array('post_id' => (string) $postId));

    $block = $isTable ? new \MTRN_Table_Gutenberg_Block($renderer) : new \MTRN_Matches_Gutenberg_Block($renderer);
    $out['block.html'] = $block->render_block(array('tableId' => (string) $postId));

    $widget = $isTable ? new \MTRN_Table_Widget() : new \MTRN_Matches_Widget();
    ob_start();
    $widget->widget(
      array('before_widget' => '<section class="widget">', 'after_widget' => '</section>', 'before_title' => '<h2>', 'after_title' => '</h2>'),
      array('title' => 'Golden widget', 'table_id' => $postId)
    );
    $out['widget.html'] = ob_get_clean();

    $this->currentPostId = $postId;
    $this->currentPostType = $isTable ? 'mtrn_table' : 'mtrn_match_list';
    $GLOBALS['post'] = (object) array('ID' => $postId, 'post_type' => $this->currentPostType);
    $filter = new \MTRN_Single_Content_Filter(new \MTRN_Table_Renderer(), new \MTRN_Matches_Renderer());
    $out['single.html'] = $filter->populate_single_cpt_content('<p>Body</p>');

    $metaBox = $isTable ? new \MTRN_Admin_Table_Meta_Boxes($renderer) : new \MTRN_Admin_Matches_Meta_Boxes($renderer);
    $post = (object) array('ID' => $postId, 'post_type' => $this->currentPostType);
    ob_start();
    $metaBox->meta_box_callback($post);
    $out['admin-form.html'] = ob_get_clean();
    ob_start();
    $metaBox->shortcode_meta_box_callback($post);
    $out['admin-shortcode-box.html'] = ob_get_clean();

    if (isset($case['preview_post'])) {
      $ajax = $isTable ? new \MTRN_Table_Ajax_Handler($renderer) : new \MTRN_Matches_Ajax_Handler($renderer);
      $_POST = $case['preview_post'] + array('nonce' => 'nonce', 'post_id' => (string) $postId);
      $this->json = null;
      if ($isTable) {
        $ajax->ajax_preview_table();
      } else {
        $ajax->ajax_preview_matches();
      }
      $_POST = array();
      $out['preview-ajax.html'] = (string) $this->json;
    }

    if (isset($case['save_post'])) {
      $_POST = $case['save_post'];
      $this->saved = array();
      $metaBox->save_meta_boxes($postId);
      $_POST = array();
      $out['save.json'] = json_encode($this->saved, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }

    return $out;
  }
}
