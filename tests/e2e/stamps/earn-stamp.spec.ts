import { expect } from '@playwright/test';
import { test } from '../fixtures/auth';
import { ParkPage } from '../pages/ParkPage';
import { ParksPage } from '../pages/ParksPage';
import { StampsPage } from '../pages/StampsPage';

test.describe('Earning a stamp by checking in', () => {
    test('checking into a park earns the matching stamps', async ({
        authenticatedPage,
    }) => {
        const stamps = new StampsPage(authenticatedPage);

        // Precondition: nothing earned yet.
        await stamps.goto();
        await expect(stamps.earnedStamp('First Stamp')).toHaveCount(0);

        // Check into New River Gorge (West Virginia's only national park).
        const parks = new ParksPage(authenticatedPage);
        await parks.openPark('New River Gorge');
        await new ParkPage(authenticatedPage).checkInNow();

        // The reveal modal celebrates the unlock (First Stamp + Mountaineer).
        const reveal = authenticatedPage.getByRole('dialog');
        await expect(reveal).toBeVisible();
        await expect(reveal).toContainText('New stamps earned!');
        await expect(
            reveal.getByRole('img', { name: /First Stamp stamp, earned/ }),
        ).toBeVisible();
        await authenticatedPage.getByRole('button', { name: 'Nice!' }).click();
        await expect(reveal).toBeHidden();

        // And they're now in the collection.
        await stamps.goto();
        await expect(stamps.earnedStamp('First Stamp')).toBeVisible();
        await expect(stamps.earnedStamp('Mountaineer')).toBeVisible();
    });
});
