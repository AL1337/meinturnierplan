# Tests and checks

All checks run in GitHub Actions (`.github/workflows/ci.yml`). To run them locally:

```bash
composer install            # once
composer lint               # PHP syntax on every file
composer phpcs              # WordPress security/i18n/compatibility sniffs (phpcs.xml.dist)
composer test               # PHPUnit unit tests (tests/Unit)

npm install                 # once
npm run lint:js             # node --check on every script
npx playwright install chromium   # once, or reuse Google Chrome (next line)
PLAYWRIGHT_CHANNEL=chrome npm run test:e2e   # frontend embed resize tests (tests/e2e)
```

The PHP tests use Brain Monkey to stub WordPress functions, so they need no
WordPress install or database. They cover the embed URL and markup produced
from shortcode attributes and post meta, and the JSON API helpers.

The Playwright tests load the real `assets/js/frontend.js` into a blank page
and serve fake embeds from intercepted routes, so they never contact the
MeinTurnierplan service.

Releases: pushing a tag such as `1.3.0` runs `.github/workflows/deploy.yml`,
which verifies that the tag, the plugin header, `MTRN_PLUGIN_VERSION` and the
readme `Stable tag` agree and then commits to WordPress.org SVN. The files
listed in `.distignore` are left out of the release.
