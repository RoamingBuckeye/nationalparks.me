import { type Locator, type Page } from '@playwright/test';

export class StampsPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async goto() {
        await this.page.goto('/stamps');
    }

    /** The badge for a stamp, matched by its accessible label. */
    stamp(name: string): Locator {
        return this.page.getByRole('img', {
            name: new RegExp(`^${name} stamp`),
        });
    }

    /** The badge for a stamp that has been earned. */
    earnedStamp(name: string): Locator {
        return this.page.getByRole('img', {
            name: new RegExp(`^${name} stamp, earned`),
        });
    }
}
