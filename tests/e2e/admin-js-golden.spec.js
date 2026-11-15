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
const META_BOXES_JS = read(path.join(ROOT, 'assets/js/admin-meta-boxes.js'));
const PREVIEW_JS = read(path.join(ROOT, 'assets/js/admin-preview.js'));

// Globals WordPress would localize, plus jQuery AJAX stand-ins answering with
// the fixture tournaments "golden1" (groups A/B, final round, courts, groups,
// no referees) and "single1" (one group, nothing else).
function harness(jsConfig) {
  return `
    window.ajaxurl = '/wp-admin/admin-ajax.php';
    window.__mtrnCalls = [];
    window.__mtrnLastPreview = null;
    window.mtrnAdminUtilsConfig = Object.assign({ nonce: 'nonce', i18n: {} }, ${JSON.stringify(jsConfig.adminUtils)});
    window.mtrnShortcodeConfig = { fieldId: 'mtrn_shortcode_field', copyButtonId: 'mtrn_copy_shortcode', successMessageId: 'mtrn_copy_success', fieldPrefix: 'mtrn_', updateCallback: 'updateShortcode' };
    window.mtrnMetaBoxConfig = ${JSON.stringify(jsConfig.metaBox)};
    window.mtrnPreviewConfig = ${JSON.stringify(jsConfig.preview)};
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

const cases = fs.readdirSync(EXPECTED).filter((d) => fs.existsSync(path.join(EXPECTED, d, 'admin-js-config.json'))).sort();

for (const name of cases) {
  test(`admin scripts golden: ${name}`, async ({ page }) => {
    const dir = path.join(EXPECTED, name);
    const jsConfig = JSON.parse(read(path.join(dir, 'admin-js-config.json')));
    const html = `<!doctype html><html><head><style>${STYLE_CSS}</style></head><body><div id="poststuff">${read(path.join(dir, 'admin-form.html'))}${read(path.join(dir, 'admin-shortcode-box.html'))}</div></body></html>`;

    // Nothing external may load: the captured form contains the embed iframe
    await page.route(/^https?:\/\//, (route) => route.abort());
    await page.setContent(html);
    for (const content of [JQUERY, harness(jsConfig), ADMIN_UTILS, META_BOXES_JS, SHORTCODE_GEN, PREVIEW_JS]) {
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
