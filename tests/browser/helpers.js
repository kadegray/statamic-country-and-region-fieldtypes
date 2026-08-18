export function requireEnv(name) {
    const value = process.env[name];

    if (!value) {
        throw new Error(`Missing required env var: ${name}. See tests/browser/README.md.`);
    }

    return value;
}

/**
 * Statamic shows a "Licensing Alert" modal on trial/commercial addon setups that
 * blocks clicks on everything behind it. Harmless, but needs dismissing first.
 */
export async function dismissLicensingModal(page) {
    const snooze = page.locator('button:has-text("Snooze")');

    if (await snooze.count()) {
        await snooze.click();
    }
}

/**
 * Finds the Nth `[data-ui-combobox-trigger]` button inside the smallest ancestor
 * of a field labeled `labelText`, and tags it with a stable attribute so it can be
 * located afterwards regardless of selection state.
 *
 * Locating by the trigger button (rather than the inner `<input>`) matters because
 * the input's placeholder/attributes change once a value is selected, but the
 * trigger button itself does not.
 *
 * `index` disambiguates fields that render more than one combobox under a single
 * label, e.g. the region_in_country fieldtype's composite "Select Country" /
 * "Select Region" pair, both nested under one "Region In Country Field" label.
 */
export async function markComboboxTrigger(page, labelText, tag, index = 0) {
    const found = await page.evaluate(
        ({ labelText, tag, index }) => {
            const candidates = [...document.querySelectorAll('body *')].filter(
                (el) => el.children.length === 0 && el.textContent.trim() === labelText
            );

            for (const label of candidates) {
                let el = label;

                while (el && el !== document.body) {
                    const triggers = el.querySelectorAll('[data-ui-combobox-trigger]');

                    if (triggers.length > index) {
                        triggers[index].setAttribute('data-pw-target', tag);

                        return true;
                    }

                    el = el.parentElement;
                }
            }

            return false;
        },
        { labelText, tag, index }
    );

    if (!found) {
        throw new Error(`Could not find combobox trigger #${index} for field labeled "${labelText}"`);
    }

    return page.locator(`[data-pw-target="${tag}"]`);
}

/**
 * Opens a combobox trigger, types a search query, and clicks the matching option.
 */
export async function selectComboboxOption(page, trigger, optionText) {
    await trigger.click();
    await page.waitForTimeout(200);
    await page.keyboard.type(optionText);
    await page.getByText(optionText, { exact: true }).first().click({ timeout: 5000 });
}
