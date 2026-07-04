import { type Locator, type Page } from '@playwright/test';

export class ParkPage {
    readonly page: Page;
    readonly checkInButton: Locator;

    constructor(page: Page) {
        this.page = page;
        this.checkInButton = page.getByRole('button', { name: 'Check in now' });
    }

    async checkInNow() {
        // Checking in redirects to the new visit's page.
        await this.checkInButton.click();
        await this.page.waitForURL('**/visits/**');
    }
}
