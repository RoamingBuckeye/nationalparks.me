import { type Page } from '@playwright/test';

export class ParksPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async goto() {
        await this.page.goto('/parks');
    }

    async openPark(name: string) {
        await this.goto();
        await this.page.getByRole('link', { name: new RegExp(name) }).click();
    }
}
