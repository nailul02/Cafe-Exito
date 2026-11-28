// @ts-check
import { defineConfig, devices } from '@playwright/test';

const frontendBaseUrl = 'http://127.0.0.1:5178';
const apiBaseUrl = 'http://127.0.0.1:8127/api/v1';
process.env.E2E_API_BASE_URL = apiBaseUrl;

/**
 * Playwright E2E Configuration for Cafe Éxito
 * Target: Full-Stack E2E -> dedicated local servers -> PostgreSQL cafe_exito / public.
 * Rule: Workers: 1 to guarantee deterministic, collision-free transaction flow.
 * Note: Never auto-run migrations or destructive seeding in webServer or globalSetup.
 */
export default defineConfig({
  testDir: './e2e',
  webServer: [
    {
      command: 'php artisan --env=testing serve --host=127.0.0.1 --port=8127',
      cwd: '../backend',
      url: 'http://127.0.0.1:8127/api/v1/health',
      reuseExistingServer: false,
      env: {
        APP_ENV: 'testing',
        DB_CONNECTION: 'pgsql',
        DB_HOST: '127.0.0.1',
        DB_PORT: '5432',
        DB_DATABASE: 'cafe_exito',
        DB_USERNAME: 'cafe_exito_user',
        DB_SEARCH_PATH: 'public',
        DB_URL: '',
        CORS_ALLOWED_ORIGINS: 'http://127.0.0.1:5178',
      },
    },
    {
      command: 'npm.cmd run dev -- --host 127.0.0.1 --port 5178 --strictPort',
      cwd: '.',
      url: frontendBaseUrl,
      reuseExistingServer: false,
      env: {
        VITE_API_BASE_URL: apiBaseUrl,
        VITE_USE_MOCK: 'false',
      },
    },
  ],
  /* Maximum time one test can run for */
  timeout: 30 * 1000,
  expect: {
    /* Timeout for each expect assertion */
    timeout: 5000,
  },
  /* Run tests in files in parallel? Set false to avoid cross-scenario race conditions */
  fullyParallel: false,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Retry on CI or for debugging flaky network calls */
  retries: process.env.CI ? 2 : 1,
    /* E2E writes to the approved local DB; one worker limits cross-scenario interference. */
  workers: 1,
  /* Reporter to use */
  reporter: [
    ['list'],
    ['html', { outputFolder: process.env.PLAYWRIGHT_REPORT_DIR || 'playwright-report-current', open: 'never' }],
  ],
  /* Shared settings for all the projects below */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: frontendBaseUrl,

    /* Traces can capture authentication request/response bodies; keep them disabled. */
    trace: 'off',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',

    /* Locale and timezone for Asia/Jakarta */
    locale: 'id-ID',
    timezoneId: 'Asia/Jakarta',
  },

  /* Configure projects for major browsers */
  projects: [
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
      },
    },
    {
      name: 'chrome-system',
      use: {
        ...devices['Desktop Chrome'],
        channel: 'chrome',
      },
    },
  ],
});
