import { defineConfig, devices } from '@playwright/test';
import 'dotenv/config';
import path from 'path';

const BAGISTO_PATH = process.env.BAGISTO_PATH || path.resolve(__dirname, '../../../../..');

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
    reuseExistingServer: !process.env.CI,
    timeout: 60_000,
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
