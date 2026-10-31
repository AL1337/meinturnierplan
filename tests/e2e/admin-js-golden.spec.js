// Browser-side golden (characterization) tests for the admin scripts.
//
// For every case under tests/golden/expected/ the captured admin form and
// shortcode box (PHP output) are loaded into a page together with jQuery and
// the real admin scripts. AJAX is answered with the same fixture data the PHP
// golden tests use. The test records what the scripts produce: the generated
// shortcode, the rebuilt group / participant selects, which conditional rows
// are visible, the live-preview payload and the AJAX contract.
//
// Regenerate after an intentional change:
//   MTRN_UPDATE_GOLDENS=1 npm run test:e2e -- tests/e2e/admin-js-golden.spec.js
const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '../..');
const EXPECTED = path.join(ROOT, 'tests/golden/expected');
const UPDATE = !!process.env.MTRN_UPDATE_GOLDENS;
const read = (p) => fs.readFileSync(p, 'utf8');

const JQUERY = read(path.join(ROOT, 'node_modules/jquery/dist/jquery.min.js'));
const STYLE_CSS = read(path.join(ROOT, 'assets/css/style.css'));
const ADMIN_UTILS = read(path.join(ROOT, 'assets/js/admin-utilities.js'));
const SHORTCODE_GEN = read(path.join(ROOT, 'assets/js/shortcode-generator.js'));
const SCRIPTS = {
  table: { metaBoxes: read(path.join(ROOT, 'assets/js/admin-table-meta-boxes.js')), preview: read(path.join(ROOT, 'assets/js/admin-table-preview.js')) },
  matches: { metaBoxes: read(path.join(ROOT, 'assets/js/admin-matches-meta-boxes.js')), preview: read(path.join(ROOT, 'assets/js/admin-matches-preview.js')) }
};

// Mirrors the field list the table meta box localizes for admin-table-preview.js
const TABLE_FIELD_LIST = [
  'mtrn_tournament_id', 'mtrn_font_size', 'mtrn_header_font_size',
  'mtrn_bsizeh', 'mtrn_bsizev', 'mtrn_bsizeoh', 'mtrn_bsizeov', 'mtrn_bbsize',
  'mtrn_table_padding', 'mtrn_inner_padding', 'mtrn_text_color', 'mtrn_main_color',
  'mtrn_bg_color', 'mtrn_logo_size', 'mtrn_bg_opacity', 'mtrn_border_color',
  'mtrn_head_bottom_border_color', 'mtrn_even_bg_color', 'mtrn_even_bg_opacity',
  'mtrn_odd_bg_color', 'mtrn_odd_bg_opacity', 'mtrn_hover_bg_color', 'mtrn_hover_bg_opacity',
  'mtrn_head_bg_color', 'mtrn_head_bg_opacity', 'mtrn_suppress_wins', 'mtrn_suppress_logos',
  'mtrn_suppress_num_matches', 'mtrn_projector_presentation', 'mtrn_navigation_for_groups',
  'mtrn_responsive', 'mtrn_language', 'mtrn_group'
];

// Globals WordPress would localize, plus jQuery AJAX stand-ins answering with
// the fixture tournaments "golden1" (groups A/B, final round, courts, groups,
// no referees) and "single1" (one group, nothing else).
function harness(kind, postId) {
  const groupActions = kind === 'table' ? ['mtrn_get_groups', 'mtrn_refresh_groups'] : ['mtrn_get_matches_groups', 'mtrn_refresh_matches_groups'];
  const teamActions = kind === 'table' ? ['mtrn_get_teams', 'mtrn_refresh_teams'] : ['mtrn_get_matches_teams', 'mtrn_refresh_matches_teams'];
  return `
    window.ajaxurl = '/wp-admin/admin-ajax.php';
    window.__mtrnCalls = [];
    window.__mtrnLastPreview = null;
    window.mtrnAdminUtilsConfig = { fieldPrefix: 'mtrn_', ajaxActions: ${JSON.stringify(groupActions)}, ajaxActionsTeams: ${JSON.stringify(teamActions)}, nonce: 'nonce', i18n: {} };
    window.mtrnShortcodeConfig = { fieldId: 'mtrn_shortcode_field', copyButtonId: 'mtrn_copy_shortcode', successMessageId: 'mtrn_copy_success', fieldPrefix: 'mtrn_', updateCallback: 'updateShortcode' };
    (function () {
      var w = document.getElementById('mtrn_width'), h = document.getElementById('mtrn_height');
      var metaBoxConfig = { postId: ${postId}, defaultWidth: w ? w.value : '300', defaultHeight: h ? h.value : '152' };
      window.mtrnTableMetaBoxConfig = metaBoxConfig;
      window.mtrnMatchesMetaBoxConfig = metaBoxConfig;
    })();
    window.mtrnTablePreviewConfig = { postId: ${postId}, previewNonce: 'nonce', fieldList: ${JSON.stringify(TABLE_FIELD_LIST)} };
    window.mtrnMatchesPreviewConfig = { postId: ${postId}, checkOptionNonce: 'nonce', previewNonce: 'nonce' };
    (function ($) {
      $.fn.wpColorPicker = function () { return this; };
      function thenable() { var t = { done: function () { return t; }, fail: function () { return t; }, always: function () { return t; } }; return t; }
      function fixture(id) {
        if (id === 'golden1') return { groups: [{ displayId: 'A' }, { displayId: 'B' }], hasFinalRound: true, teams: [{ displayId: '1', name: 'Team One' }, { displayId: '2', name: 'Team Two' }, { displayId: '3', name: 'Team Three' }, { displayId: '4', name: 'Team Four' }], options: { showCourts: true, showGroups: true, showReferees: false, finalMatches: [{ gameId: '9' }] } };
        if (id === 'single1') return { groups: [], hasFinalRound: false, teams: [{ displayId: '1', name: 'Solo A' }, { displayId: '2', name: 'Solo B' }], options: { showCourts: false, showGroups: false, showReferees: false, finalMatches: null } };
        return null;
      }
      $.post = function (url, data, cb) {
        var action = data.action || '';
        window.__mtrnCalls.push({ via: 'post', action: action, keys: Object.keys(data).sort() });
        var f = fixture(data.tournament_id);
        var response;
        if (/groups$/.test(action)) response = f ? { success: true, data: { groups: f.groups, hasFinalRound: f.hasFinalRound } } : { success: true, data: { groups: [], hasFinalRound: false } };
        else if (/teams$/.test(action)) response = f ? { success: true, data: { teams: f.teams } } : { success: true, data: { teams: [] } };
        else if (/^mtrn_preview_/.test(action)) { window.__mtrnLastPreview = data; response = { success: false }; }
        if (response && typeof cb === 'function') cb(response);
        return thenable();
      };
      $.ajax = function (opts) {
        var d = opts.data || {};
        window.__mtrnCalls.push({ via: 'ajax', action: d.action, option: d.option_name });
        var f = fixture(d.tournament_id);
        if (opts.success) opts.success({ success: true, data: { value: f ? f.options[d.option_name] : null } });
        return thenable();
      };
    })(jQuery);
  `;
}

const cases = fs.readdirSync(EXPECTED).filter((d) => fs.existsSync(path.join(EXPECTED, d, 'case.json'))).sort();

for (const name of cases) {
  test(`admin scripts golden: ${name}`, async ({ page }) => {
    const dir = path.join(EXPECTED, name);
    const info = JSON.parse(read(path.join(dir, 'case.json')));
    const kind = info.type === 'table' ? 'table' : 'matches';
    const html = `<!doctype html><html><head><style>${STYLE_CSS}</style></head><body><div id="poststuff">${read(path.join(dir, 'admin-form.html'))}${read(path.join(dir, 'admin-shortcode-box.html'))}</div></body></html>`;

    // Nothing external may load: the captured form contains the embed iframe
    await page.route(/^https?:\/\//, (route) => route.abort());
    await page.setContent(html);
    for (const content of [JQUERY, harness(kind, info.post_id), ADMIN_UTILS, SCRIPTS[kind].metaBoxes, SHORTCODE_GEN, SCRIPTS[kind].preview]) {
      await page.addScriptTag({ content });
    }
    // Let every document-ready handler of the scripts run
    await page.evaluate(() => new Promise((resolve) => jQuery(resolve)));

    const result = await page.evaluate(() => {
      window.updateShortcode();
      const shortcode = document.getElementById('mtrn_shortcode_field').value;
      const outer = (id) => { const el = document.getElementById(id); return el ? el.outerHTML : null; };
      const rows = Array.from(document.querySelectorAll('tr[id$="_row"]')).map((tr) => tr.id + ': ' + (getComputedStyle(tr).display === 'none' ? 'hidden' : 'visible'));
      window.__mtrnLastPreview = null;
      jQuery('#mtrn_font_size').trigger('change');
      return {
        shortcode,
        state: {
          groupSelect: outer('mtrn_group'),
          participantSelect: outer('mtrn_participant'),
          conditionalRows: rows,
          previewPayload: window.__mtrnLastPreview,
          ajaxCalls: window.__mtrnCalls
        }
      };
    });

    const shortcodeFile = path.join(dir, 'js-shortcode.txt');
    const stateFile = path.join(dir, 'js-state.json');
    const stateJson = JSON.stringify(result.state, null, 2) + '\n';
    if (UPDATE) {
      fs.writeFileSync(shortcodeFile, result.shortcode + '\n');
      fs.writeFileSync(stateFile, stateJson);
      return;
    }
    expect(fs.existsSync(shortcodeFile), `missing ${shortcodeFile}; run with MTRN_UPDATE_GOLDENS=1`).toBe(true);
    expect(result.shortcode + '\n').toBe(read(shortcodeFile));
    expect(stateJson).toBe(read(stateFile));
  });
}
