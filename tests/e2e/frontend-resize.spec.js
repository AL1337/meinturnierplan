// Frontend embed resize tests for assets/js/frontend.js.
//
// The real script is injected into a blank page. Requests to the service's
// displayTable.php / displayMatches.php are intercepted and answered with a
// tiny fake embed that posts the same size message the real embed sends
// ({ type: "iframeSizeMtpTable" | "iframeSizeMtpMatches", width, height }).
// A frozen fake clock (page.clock) drives the 5-second fallback and the observer delay.
const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const FRONTEND_JS = fs.readFileSync(path.resolve(__dirname, '../../assets/js/frontend.js'), 'utf8');
const FRONTEND_CSS = fs.readFileSync(path.resolve(__dirname, '../../assets/css/frontend.css'), 'utf8');
const SERVICE = 'https://www.meinturnierplan.de';

function fakeEmbed(type, width, height) {
  return `<!doctype html><html><body style="margin:0"><div style="width:${width}px;height:${height}px"></div>` +
    `<script>parent.postMessage({ type: ${JSON.stringify(type)}, width: ${width}, height: ${height} }, "*");</script></body></html>`;
}

function embedSrc(kind, params) {
  const url = new URL(`${SERVICE}/${kind === 'matches' ? 'displayMatches' : 'displayTable'}.php`);
  url.searchParams.set('id', 'test');
  Object.entries(params || {}).forEach(([k, v]) => url.searchParams.set(k, String(v)));
  return url.toString();
}

function iframeHtml(id, src, attrs = 'width="300" height="200"') {
  return `<iframe id="${id}" class="mtrn-embed-frame" src="${src}" ${attrs} allowtransparency="true" frameborder="0"></iframe>`;
}

async function setupPage(page, bodyHtml) {
  await page.route(/\/display(Table|Matches)\.php/, (route) => {
    const url = new URL(route.request().url());
    const type = url.pathname.endsWith('displayMatches.php') ? 'iframeSizeMtpMatches' : 'iframeSizeMtpTable';
    if (url.searchParams.get('silent') === '1') {
      // An embed that never reports its size (e.g. the service's error page)
      return route.fulfill({ contentType: 'text/html', body: '<!doctype html><html><body></body></html>' });
    }
    const w = Number(url.searchParams.get('w') || 600);
    const h = Number(url.searchParams.get('h') || 400);
    return route.fulfill({ contentType: 'text/html', body: fakeEmbed(type, w, h) });
  });
  // Fake, frozen clock: timers only advance through page.clock.runFor(), so the
  // 5-second fallback and the observer delay are deterministic.
  await page.clock.install({ time: new Date('2026-01-01T12:00:00Z') });
  await page.clock.pauseAt(new Date('2026-01-01T12:00:01Z'));
  // The script is part of the page, as WordPress prints it in the footer: it must
  // register its message listener before the embeds finish loading.
  await page.setContent(`<!doctype html><html><head><style>${FRONTEND_CSS}</style></head><body>${bodyHtml}<script>${FRONTEND_JS}</script></body></html>`);
}

async function expectSize(locator, width, height) {
  await expect(locator).toHaveAttribute('width', String(width));
  await expect(locator).toHaveAttribute('height', String(height));
  await expect(locator).toHaveCSS('width', `${width}px`);
  await expect(locator).toHaveCSS('height', `${height}px`);
}

test('resizes table and matches embeds from their size messages', async ({ page }) => {
  await setupPage(page,
    iframeHtml('mtrn-table-t1-aaaaaaaa', embedSrc('table', { w: 620, h: 410 })) +
    iframeHtml('mtrn-matches-t1-bbbbbbbb', embedSrc('matches', { w: 900, h: 1500 })));

  await expectSize(page.locator('#mtrn-table-t1-aaaaaaaa'), 620, 410);
  await expectSize(page.locator('#mtrn-matches-t1-bbbbbbbb'), 900, 1500);

  const stored = await page.evaluate(() => window.MTRN_IframeDimensions);
  expect(stored['mtrn-table-t1-aaaaaaaa']).toMatchObject({ width: 620, height: 410 });
  expect(stored['mtrn-matches-t1-bbbbbbbb']).toMatchObject({ width: 900, height: 1500 });
});

test('clamps reported sizes to the minimum width and height', async ({ page }) => {
  await setupPage(page, iframeHtml('mtrn-table-t2-aaaaaaaa', embedSrc('table', { w: 100, h: 120 })));
  await expectSize(page.locator('#mtrn-table-t2-aaaaaaaa'), 300, 150);
});

test('falls back to 200px after 5 seconds when an embed never reports its size', async ({ page }) => {
  await setupPage(page, iframeHtml('mtrn-table-t3-aaaaaaaa', embedSrc('table', { silent: 1 }), 'width="300" height="333"'));
  const iframe = page.locator('#mtrn-table-t3-aaaaaaaa');

  await expect(iframe).toHaveAttribute('data-mtrn-fallback-pending', 'true');
  await page.clock.runFor(4900);
  await expect(iframe).toHaveAttribute('height', '333');

  await page.clock.runFor(200);
  await expect(iframe).toHaveAttribute('height', '200');
  await expect(iframe).toHaveCSS('height', '200px');
  await expect(iframe).not.toHaveAttribute('data-mtrn-fallback-pending', /.*/);
});

test('does not re-arm sized embeds when an unrelated iframe is added later', async ({ page }) => {
  await setupPage(page,
    iframeHtml('mtrn-table-t4-aaaaaaaa', embedSrc('table', { w: 620, h: 410 })) +
    iframeHtml('mtrn-matches-t4-bbbbbbbb', embedSrc('matches', { w: 900, h: 1500 })));
  const table = page.locator('#mtrn-table-t4-aaaaaaaa');
  const matches = page.locator('#mtrn-matches-t4-bbbbbbbb');
  await expectSize(table, 620, 410);
  await expectSize(matches, 900, 1500);

  // A cookie banner, chat widget or ad adds its own iframe
  await page.evaluate(() => {
    const foreign = document.createElement('iframe');
    foreign.id = 'foreign';
    foreign.src = 'about:blank';
    document.body.appendChild(foreign);
  });
  await page.clock.runFor(6000);

  await expectSize(table, 620, 410);
  await expectSize(matches, 900, 1500);
  await expect(table).not.toHaveAttribute('data-mtrn-fallback-pending', /.*/);
  await expect(matches).not.toHaveAttribute('data-mtrn-fallback-pending', /.*/);
});

test('arms embeds that are inserted later (admin preview replaces the iframe)', async ({ page }) => {
  await setupPage(page, iframeHtml('mtrn-table-t5-aaaaaaaa', embedSrc('table', { w: 620, h: 410 })));
  await expectSize(page.locator('#mtrn-table-t5-aaaaaaaa'), 620, 410);

  await page.evaluate((src) => {
    const old = document.getElementById('mtrn-table-t5-aaaaaaaa');
    const next = document.createElement('iframe');
    next.id = 'mtrn-table-t5-bbbbbbbb';
    next.className = 'mtrn-embed-frame';
    next.width = '300';
    next.height = '200';
    next.src = src;
    old.parentNode.replaceChild(next, old);
  }, embedSrc('table', { w: 500, h: 300 }));
  await page.clock.runFor(150); // observer delay

  const next = page.locator('#mtrn-table-t5-bbbbbbbb');
  await expectSize(next, 500, 300);
  await expect(next).toHaveAttribute('data-mtrn-armed', 'true');
  await page.clock.runFor(6000);
  await expectSize(next, 500, 300);
});

test('re-arms an embed whose src changes and applies the new size', async ({ page }) => {
  await setupPage(page, iframeHtml('mtrn-matches-t6-aaaaaaaa', embedSrc('matches', { w: 900, h: 1500 })));
  const iframe = page.locator('#mtrn-matches-t6-aaaaaaaa');
  await expectSize(iframe, 900, 1500);

  await page.evaluate((src) => { document.getElementById('mtrn-matches-t6-aaaaaaaa').src = src; }, embedSrc('matches', { w: 700, h: 800 }));
  await expectSize(iframe, 700, 800);
  await page.clock.runFor(6000);
  await expectSize(iframe, 700, 800);
});

test('a src change to an embed that never reports falls back after 5 seconds', async ({ page }) => {
  await setupPage(page, iframeHtml('mtrn-table-t7-aaaaaaaa', embedSrc('table', { w: 620, h: 410 })));
  const iframe = page.locator('#mtrn-table-t7-aaaaaaaa');
  await expectSize(iframe, 620, 410);

  await page.evaluate((src) => { document.getElementById('mtrn-table-t7-aaaaaaaa').src = src; }, embedSrc('table', { silent: 1 }));
  await expect(iframe).toHaveAttribute('data-mtrn-fallback-pending', 'true');
  await page.clock.runFor(5100);
  await expect(iframe).toHaveAttribute('height', '200');
});

test('ignores size messages that do not come from an embed', async ({ page }) => {
  await setupPage(page, iframeHtml('mtrn-table-t8-aaaaaaaa', embedSrc('table', { w: 620, h: 410 })));
  const iframe = page.locator('#mtrn-table-t8-aaaaaaaa');
  await expectSize(iframe, 620, 410);

  await page.evaluate(() => {
    window.postMessage({ type: 'iframeSizeMtpTable', width: 999, height: 999 }, '*');
    window.postMessage({ type: 'somethingElse', width: 888, height: 888 }, '*');
  });
  await page.clock.runFor(500);
  await expectSize(iframe, 620, 410);
});
