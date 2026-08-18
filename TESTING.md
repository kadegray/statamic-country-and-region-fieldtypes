# Testing

This addon has two separate test suites:

- **PHP unit tests** — the Fieldtype classes' `augment()`/`process()` logic and the
  `countries`/`regions` HTTP endpoints. Fully self-contained; no external site needed.
- **Playwright browser tests** — drive a real Statamic control panel to verify the
  fieldtypes actually render and work in the CP. Requires a local Statamic site with
  this addon installed. See [tests/browser/README.md](tests/browser/README.md) for
  full details.

## PHP unit tests

### Setup

```bash
composer install
```

### Run

```bash
vendor/bin/phpunit
```

Run a single file or method:

```bash
vendor/bin/phpunit tests/Fieldtypes/CountryFieldtypeTest.php
vendor/bin/phpunit --filter test_augment_returns_localized_name_for_valid_code
```

### What's covered

- `tests/Fieldtypes/` — `CountryFieldtype`, `RegionFieldtype`, and
  `RegionInCountryFieldtype`'s `defaultValue()`/`process()` passthroughs, `augment()`
  happy paths, invalid-value handling (with and without `render_invalid_value`),
  multi-value arrays, and the `region_is_required` toggle.
- `tests/Controllers/` — the real `/!/statamic-country-and-region-fieldtypes/countries`
  and `/!/statamic-country-and-region-fieldtypes/{country}/regions` endpoints,
  including the comma-separated multi-country case (the logic behind the region
  field's `countries_manual` config mode).

These use Statamic's official `Statamic\Testing\AddonTestCase` (Orchestra Testbench
under the hood), which boots a real, isolated Statamic app in-process — no database,
content, or external site required.

## Playwright browser tests

### Setup

```bash
npm install
npx playwright install chromium
```

### Run

Point the suite at a Statamic site you already have running locally, with this
addon installed and the fixture fields from
[tests/browser/fixtures/blueprint.yaml](tests/browser/fixtures/blueprint.yaml) added
to a blueprint:

```bash
PLAYWRIGHT_BASE_URL=https://my-test-site.test \
PLAYWRIGHT_CP_EMAIL=me@example.com \
PLAYWRIGHT_CP_PASSWORD=secret \
PLAYWRIGHT_ENTRY_URL=/cp/collections/pages/entries/<entry-id> \
npm run test:browser
```

Full details on the required env vars, expected fixture, and what's covered are in
[tests/browser/README.md](tests/browser/README.md).
