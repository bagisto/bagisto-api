import { defineConfig, devices } from '@playwright/test';
import 'dotenv/config';

// Auto-boot the Bagisto server with APP_ENV=testing so the rate-limit bypass
// + test-key shortcircuit in VerifyStorefrontKey / ApiKeyService fire cleanly
// during e2e runs (otherwise tests must honor production-style rate limits).
//
// Safe — phpunit.xml already sets APP_ENV=testing for unit tests, no separate
// .env.testing exists, and config/database.php has no `testing` connection
// override, so the server uses the same DB/credentials as APP_ENV=local.
const BAGISTO_PATH = '/home/users/yashvir.singh/www/html/Bagisto/BagistoApi-2.3.8/bagisto';

export default defineConfig({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [
    ['list'],
    ['html'],
  ],
  webServer: {
    command: `cd ${BAGISTO_PATH} && APP_ENV=testing php artisan serve --host=127.0.0.1 --port=8000`,
    url: 'http://127.0.0.1:8000',
    // In CI: always boot a fresh server so APP_ENV is guaranteed.
    // Locally: reuse an existing server if one is on :8000 — note that if a
    // dev has `php artisan serve` already running with APP_ENV=local, the
    // rate-limit bypass will NOT fire. Stop your local serve before running
    // Playwright if you need the bypass behavior.
    reuseExistingServer: !process.env.CI,
    timeout: 30_000,
    stdout: 'ignore',
    stderr: 'pipe',
  },
  use: {
    baseURL: process.env.BAGISTO_URL || 'http://127.0.0.1:8000',
    trace: 'on-first-retry',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
