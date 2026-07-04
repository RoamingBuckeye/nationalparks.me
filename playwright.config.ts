import { defineConfig, devices } from '@playwright/test';
import path from 'node:path';

const PORT = 8123;
const BASE_URL = `http://127.0.0.1:${PORT}`;
const DATABASE = path.resolve('database/e2e.sqlite');

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    reporter: 'list',

    use: {
        baseURL: BASE_URL,
        trace: 'on-first-retry',
    },

    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],

    // The command builds assets and seeds a fresh SQLite database *before*
    // `artisan serve` binds the port, so the server is only reachable once the
    // app can actually render — CI has no pre-built assets to rely on.
    webServer: {
        command: [
            `touch "${DATABASE}"`,
            'npm run build',
            'php artisan migrate:fresh --seed --seeder=E2eSeeder --force',
            `php artisan serve --port=${PORT}`,
        ].join(' && '),
        url: BASE_URL,
        reuseExistingServer: !process.env.CI,
        timeout: 180_000,
        env: {
            APP_ENV: 'local',
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: DATABASE,
            SESSION_DRIVER: 'file',
            CACHE_STORE: 'file',
            QUEUE_CONNECTION: 'sync',
        },
    },
});
