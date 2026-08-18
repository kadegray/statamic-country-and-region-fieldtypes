import { test, expect } from '@playwright/test';
import { dismissLicensingModal, markComboboxTrigger, requireEnv, selectComboboxOption } from './helpers.js';

// See tests/browser/README.md for the blueprint/entry this suite expects to find
// on the target site, and which env vars to set to point at it.
const entryUrl = requireEnv('PLAYWRIGHT_ENTRY_URL');

test.beforeEach(async ({ page }) => {
    await page.goto(entryUrl, { waitUntil: 'networkidle' });
    await dismissLicensingModal(page);
});

test('the country, region, and region-in-country fieldtypes render without errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', (msg) => {
        if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    await expect(page.getByText('Country Field', { exact: true })).toBeVisible();
    await expect(page.getByText('Region In Country Field', { exact: true })).toBeVisible();
    await expect(page.getByText('Region Field', { exact: true })).toBeVisible();

    const bodyText = await page.locator('body').innerText();
    expect(bodyText).not.toContain('does not exist');

    // WebAuthn/passkey feature-detection noise is unrelated to this addon.
    const meaningfulErrors = consoleErrors.filter(
        (error) => !error.includes('allowCredentials') && !error.includes('Resident credentials')
    );
    expect(meaningfulErrors).toEqual([]);
});

test('the country fieldtype is searchable and selectable', async ({ page }) => {
    const countryTrigger = await markComboboxTrigger(page, 'Country Field', 'country-field');

    await selectComboboxOption(page, countryTrigger, 'United States');

    await expect(countryTrigger).toContainText('United States');
});

test('the linked region field updates its options live when the country field changes', async ({ page }) => {
    const countryTrigger = await markComboboxTrigger(page, 'Country Field', 'country-field');
    const regionTrigger = await markComboboxTrigger(page, 'Region Field', 'region-field');

    await selectComboboxOption(page, countryTrigger, 'United States');
    await regionTrigger.click();
    await expect(page.getByText('California', { exact: true })).toBeVisible();
    await page.keyboard.press('Escape');

    await selectComboboxOption(page, countryTrigger, 'Canada');
    await regionTrigger.click();
    await expect(page.getByText('Ontario', { exact: true })).toBeVisible();
    await expect(page.getByText('California', { exact: true })).toHaveCount(0);
});

test('the region-in-country fieldtype cascades its country selection into region options', async ({ page }) => {
    const ricCountryTrigger = await markComboboxTrigger(page, 'Region In Country Field', 'ric-country', 0);
    const ricRegionTrigger = await markComboboxTrigger(page, 'Region In Country Field', 'ric-region', 1);

    await selectComboboxOption(page, ricCountryTrigger, 'Canada');

    await ricRegionTrigger.click();
    await expect(page.getByText('Ontario', { exact: true })).toBeVisible();

    await page.getByText('Ontario', { exact: true }).click();
    await expect(ricRegionTrigger).toContainText('Ontario');
});

test('a region field configured with country: countries_manual combines regions from every listed country', async ({ page }) => {
    // Configured in the fixture blueprint with countries_manual: [US, CA].
    const manualTrigger = await markComboboxTrigger(page, 'Region Field Manual', 'region-field-manual');

    await manualTrigger.click();
    await page.keyboard.type('California');
    await expect(page.getByText('California', { exact: true })).toBeVisible();
    await page.keyboard.press('Escape');

    await manualTrigger.click();
    await page.keyboard.type('Ontario');
    await expect(page.getByText('Ontario', { exact: true })).toBeVisible();
});

test('clearable removes a selected value', async ({ page }) => {
    const countryTrigger = await markComboboxTrigger(page, 'Country Field', 'country-field');

    await selectComboboxOption(page, countryTrigger, 'United States');
    await expect(countryTrigger).toContainText('United States');

    await countryTrigger.locator('[data-ui-combobox-clear-button]').click();

    await expect(countryTrigger).not.toContainText('United States');
});

test('a custom placeholder renders when configured', async ({ page }) => {
    // Configured in the fixture blueprint with placeholder: 'Choose up to two countries'.
    // A placeholder is an attribute, not text content, so it needs an attribute
    // selector rather than getByText.
    await expect(page.locator('input[placeholder="Choose up to two countries"]')).toBeVisible();
});

test('max_items limits selection and updates the count indicator', async ({ page }) => {
    const limitedTrigger = await markComboboxTrigger(page, 'Country Field Limited', 'country-field-limited');

    // The 0/2 indicator sits just below the combobox.
    await expect(page.getByText('0/2', { exact: true })).toBeVisible();

    await selectComboboxOption(page, limitedTrigger, 'United States');
    await expect(page.getByText('1/2', { exact: true })).toBeVisible();

    await limitedTrigger.click();
    await page.keyboard.type('Canada');
    await page.getByText('Canada', { exact: true }).first().click();
    await expect(page.getByText('2/2', { exact: true })).toBeVisible();
});
