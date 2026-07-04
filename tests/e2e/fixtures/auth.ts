import { test as base, type Page } from '@playwright/test';

/**
 * Provides `authenticatedPage` — a page already logged in as the seeded
 * verified user (test@example.com). Logs in through the real form so the
 * session cookie is set exactly as it would be in production.
 */
export const test = base.extend<{ authenticatedPage: Page }>({
    authenticatedPage: async ({ page }, use) => {
        await page.goto('/login');
        await page.getByLabel('Email address').fill('test@example.com');
        await page.getByLabel('Password', { exact: true }).fill('password');
        await page.getByRole('button', { name: 'Log in' }).click();
        await page.waitForURL('**/dashboard');

        await use(page);
    },
});
