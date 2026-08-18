# Browser tests

Playwright tests that drive a real Statamic control panel to verify the Country,
Region, and Region In Country fieldtypes actually render and work — not just that
the PHP/JS builds without errors.

This suite doesn't run in isolation: it needs a real Statamic site, with this addon
installed (typically via a local `path` repository during development), already
running somewhere you control. It never creates users or touches credentials on
its own — you provide both.

## One-time site setup

1. Install this addon in a local Statamic site (composer path repository is easiest
   for development).
2. Add the fields in [fixtures/blueprint.yaml](fixtures/blueprint.yaml) to a
   collection blueprint on that site (any collection is fine).
3. Create or use an existing entry in that collection.

## Required environment variables

| Variable | Example | Notes |
|---|---|---|
| `PLAYWRIGHT_BASE_URL` | `https://my-test-site.test` | Root URL of the Statamic site. |
| `PLAYWRIGHT_CP_EMAIL` | `me@example.com` | An existing CP user's email. |
| `PLAYWRIGHT_CP_PASSWORD` | `...` | That user's password. |
| `PLAYWRIGHT_ENTRY_URL` | `/cp/collections/pages/entries/049ed0c0-...` | CP edit URL (relative path is fine) of the entry with the fields from step 2. |

Set these however you like — exported in your shell, or prefixed on the command
line. Nothing is written to disk except the cached login session
(`tests/browser/.auth/user.json`, gitignored) used to skip logging in on every test.

## Running

```bash
npm run test:browser
```

or directly:

```bash
PLAYWRIGHT_BASE_URL=https://my-test-site.test \
PLAYWRIGHT_CP_EMAIL=me@example.com \
PLAYWRIGHT_CP_PASSWORD=secret \
PLAYWRIGHT_ENTRY_URL=/cp/collections/pages/entries/049ed0c0-9796-43f7-b158-4f6129328c12 \
npx playwright test
```

## What's covered

- All three fieldtypes render without a "Component ... does not exist" error and
  without unexpected console errors.
- The Country fieldtype is searchable and selectable.
- A `region` field configured with `country: countries_field` updates its options
  live when the linked country field's selection changes.
- A `region` field configured with `country: countries_manual` (`region_field_manual`
  in the fixture) shows options combined from every listed country.
- The `region_in_country` fieldtype's internal country selection cascades into its
  own region options.
- `clearable` removes a selected value via the clear button.
- A custom `placeholder` renders when configured (`country_field_limited` in the
  fixture).
- `max_items` shows a live `n/max` count indicator as options are selected
  (`country_field_limited` in the fixture).

Every config option is CP/Vue-side only (there's no server-side enforcement of
`multiple`, `max_items`, or `clearable` — grep the fieldtype classes and you won't
find `rules()` methods), which is why they're tested here rather than in PHPUnit.
The one exception is `countries_manual`'s region-combining logic, which is backend
work and is additionally covered directly in
`tests/Controllers/RegionFieldtypeControllerTest.php`.
