// Playwright configuration for the frontend embed resize tests.
//
// The tests load the real assets/js/frontend.js into a blank page and serve
// fake embeds from intercepted routes, so they need no WordPress install and
// never contact the MeinTurnierplan service.
//
// Locally you can reuse an installed Google Chrome instead of downloading a
// browser:  PLAYWRIGHT_CHANNEL=chrome npm run test:e2e
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests/e2e',
  timeout: 30000,
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['github'], ['list']] : 'list',
  use: {
    browserName: 'chromium',
    channel: process.env.PLAYWRIGHT_CHANNEL || undefined,
    headless: true,
    trace: 'retain-on-failure'
  }
});
