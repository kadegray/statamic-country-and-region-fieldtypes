import { test as setup } from '@playwright/test';
import { requireEnv } from './helpers.js';

const authFile = 'tests/browser/.auth/user.json';

setup('authenticate', async ({ page }) => {
    const email = requireEnv('PLAYWRIGHT_CP_EMAIL');
    const password = requireEnv('PLAYWRIGHT_CP_PASSWORD');

    await page.goto('/cp/auth/login');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', password);
    await page.click('button:has-text("Continue")');

    // Statamic's login can 409-then-retry once to refresh a stale CSRF token
    // before it actually redirects, so wait for the real destination rather
    // than the first response.
    await page.waitForURL(/\/cp(\/dashboard)?\/?$/, { timeout: 15000 });

    await page.context().storageState({ path: authFile });
});
