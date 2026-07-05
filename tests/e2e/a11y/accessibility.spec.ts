import AxeBuilder from '@axe-core/playwright';
import { expect, type Page } from '@playwright/test';
import { test } from '../fixtures/auth';

// Concise, stable summary of serious/critical WCAG violations for assertions.
const scan = async (page: Page): Promise<string[]> => {
    const { violations } = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();

    return violations
        .filter((v) => v.impact === 'serious' || v.impact === 'critical')
        .map((v) => `${v.impact} · ${v.id} (${v.nodes.length}) · ${v.help}`)
        .sort();
};

// Collect { path: violations } across pages so one failure lists every page.
const scanAll = async (page: Page, paths: string[]) => {
    const problems: Record<string, string[]> = {};

    for (const path of paths) {
        await page.goto(path);
        await page.waitForLoadState('networkidle');
        const issues = await scan(page);

        if (issues.length) {
            problems[path] = issues;
        }
    }

    return problems;
};

test.describe('Accessibility (axe, WCAG 2.1 AA)', () => {
    test('public pages have no serious violations', async ({ page }) => {
        expect(
            await scanAll(page, [
                '/',
                '/login',
                '/register',
                '/forgot-password',
            ]),
        ).toEqual({});
    });

    test('authenticated pages have no serious violations', async ({
        authenticatedPage: page,
    }) => {
        expect(
            await scanAll(page, [
                '/dashboard',
                '/parks',
                '/map',
                '/stamps',
                '/settings/profile',
                '/settings/security',
                '/settings/appearance',
                '/settings/sharing',
            ]),
        ).toEqual({});
    });
});
