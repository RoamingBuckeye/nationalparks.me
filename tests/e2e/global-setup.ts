import { execSync } from 'node:child_process';
import { existsSync, writeFileSync } from 'node:fs';
import path from 'node:path';

/**
 * Prepare a dedicated SQLite database for the E2E run — a fresh schema seeded
 * with the deterministic E2eSeeder fixtures — and build the front-end assets
 * `php artisan serve` will serve. Runs once before the suite.
 */
export default async function globalSetup() {
    const database = path.resolve('database/e2e.sqlite');

    if (!existsSync(database)) {
        writeFileSync(database, '');
    }

    const env = {
        ...process.env,
        DB_CONNECTION: 'sqlite',
        DB_DATABASE: database,
    };

    execSync('php artisan migrate:fresh --seed --seeder=E2eSeeder --force', {
        env,
        stdio: 'inherit',
    });

    execSync('npm run build', { stdio: 'inherit' });
}
