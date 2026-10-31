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

## Golden (characterization) tests

`tests/Unit/GoldenTest.php` and `tests/e2e/admin-js-golden.spec.js` freeze
the current output of every channel for the cases in `tests/golden/cases.php`
(see `docs/phase-2-option-inventory.md`). They fail on any output change, which
is the point during a refactor. After an intentional change, regenerate in
this order and review the diff of `tests/golden/expected/` before committing:

```bash
MTRN_UPDATE_GOLDENS=1 vendor/bin/phpunit --filter GoldenTest
MTRN_UPDATE_GOLDENS=1 PLAYWRIGHT_CHANNEL=chrome npx playwright test tests/e2e/admin-js-golden.spec.js
```

The JS goldens are built from the PHP-captured admin forms, so the PHP step
always comes first.

Releases: pushing a tag such as `1.3.0` runs `.github/workflows/deploy.yml`,
which verifies that the tag, the plugin header, `MTRN_PLUGIN_VERSION` and the
readme `Stable tag` agree and then commits to WordPress.org SVN. The files
listed in `.distignore` are left out of the release.
