import { defineConfig, devices } from '@playwright/test';
import path from 'node:path';

const PORT = 8123;
const BASE_URL = `http://127.0.0.1:${PORT}`;

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    reporter: 'list',
    globalSetup: './tests/e2e/global-setup.ts',

    use: {
        baseURL: BASE_URL,
        trace: 'on-first-retry',
    },

    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],

    // `artisan serve` reads the app's .env for APP_KEY etc.; the DB overrides
    // point it at the E2E SQLite database prepared in global setup.
    webServer: {
        command: `php artisan serve --port=${PORT}`,
        url: BASE_URL,
        reuseExistingServer: !process.env.CI,
        timeout: 120_000,
        env: {
            APP_ENV: 'local',
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: path.resolve('database/e2e.sqlite'),
            SESSION_DRIVER: 'file',
            CACHE_STORE: 'file',
            QUEUE_CONNECTION: 'sync',
        },
    },
});
