# Changelog

## 2.0.4 (2026-08-18)

### Fixed

- `RegionFieldtypeController` and the `countries_and_regions:regions` tag split a comma-separated country list with no cap on how many entries it could contain and no format check before doing work. A single request with a very long comma list could force the server to loop once per entry, each iteration re-scanning the subdivision dataset — the `2.0.1` rate limiting only caps requests per minute, not the cost of any one request, so it didn't mitigate this. Added a shared `ParsesCountryCodes` trait (`src/Traits/ParsesCountryCodes.php`) that filters to well-formed ISO 3166-1 alpha-2 codes and caps the list to 10 entries before any lookup happens; both call sites now use it.
- The public `countries_and_regions/{country}/regions` web route and the CP `countries`/`{country}/regions` action routes shared a single rate-limit bucket keyed only by IP, so heavy front-end traffic could exhaust the budget and 429 CP editors behind the same IP/NAT. Split into two named limiters, `kadegray_scarf.options.cp` and `kadegray_scarf.options.public`, same 60/min-per-IP policy, registered independently.

## 2.0.3 (2026-08-18)

### Fixed

- `statamic/cms` was only ever declared under `require-dev`, never `require`, going all the way back to when it was constrained to `^3.3` — meaning Composer never actually enforced or advertised which Statamic version this addon supports for anyone installing it into their own site. Moved `"statamic/cms": "^6.0"` into `require`, so Composer now correctly refuses to install this addon into a pre-6 Statamic site instead of allowing a broken install.

## 2.0.2 (2026-08-18)

### Added

- Test coverage for the `countries_and_regions` Antlers tag (`tests/Tags/CountriesAndRegionsTest.php`), covering `countries()`/`regions()` directly plus one `Antlers::parse()` test proving the tag is actually registered and renders correctly through real Antlers parsing — previously untested.
- Test coverage in `RegionFieldtypeControllerTest` for the public `countries_and_regions/{country}/regions` web route (`routes/web.php`), verifying both its response shape and that the `2.0.1` rate limiting is actually wired up on this route specifically, not just the CP action-route equivalent.

## 2.0.1 (2026-08-18)

### Fixed

- `CountryFieldtypeController`/`RegionFieldtypeController` no longer mutate the process's global locale via `putenv()`/`setlocale()`. Both endpoints are public and unauthenticated, and since PHP-FPM/Octane workers are reused across requests, a single request previously left `LC_ALL`/`LANGUAGE` permanently changed for every unrelated request handled by that worker afterward. Both controllers now resolve locale-aware names through `SymfonyTranslationDriver` instead, matching the pattern already used by the `countries_and_regions` tag and the two fieldtype filters.

### Added

- Rate limiting (60 requests/minute per IP) on the `countries`, `{country}/regions`, and `countries_and_regions/{country}/regions` endpoints, via a new `kadegray_scarf.options` named rate limiter registered in `ServiceProvider`.

## 2.0.0 (2026-08-17)

### Changed

- Ported the control panel frontend from Vue 2 / Laravel Mix (Statamic 3.x) to Vue 3 / Vite, using the `@statamic/cms` package and `Combobox` UI component, for compatibility with Statamic 6's rewritten control panel.
- `ServiceProvider` now registers CP assets via `$vite` instead of `$scripts`.

### Added

- PHP unit test suite (`tests/`) covering each fieldtype's `augment()`/`process()`/`defaultValue()` behavior, config-driven branches (`render_invalid_value`, `region_is_required`), and the `countries`/`regions` HTTP endpoints, using Statamic's `AddonTestCase`.
- Playwright browser test suite (`tests/browser/`) driving a real control panel to verify each fieldtype renders, is interactive, and correctly implements `countries_field`/`countries_manual` linking, `clearable`, `max_items`, and `placeholder`.
- `TESTING.md` documenting how to run both suites.

## 1.2.6 (2024-11-10)

### Fixed

- `region_in_country`'s `augment()` now actually honors `region_is_required`: when disabled, a country-only value augments to just the country name instead of being treated as invalid.

## 1.2.5 (2024-11-10)

### Added

- `region_is_required` config option on the `region_in_country` fieldtype. When disabled, selecting only a country (with no region yet chosen) is accepted as a valid value instead of requiring a full region code.

### Fixed

- Renamed an internal `regions` ref to `region` in the `region_in_country` Vue component (it always held a single region, not a collection) and reworked its value-sync watchers, fixing cases where the country and region sub-fields could fall out of sync with the field's actual value.

## 1.2.4 (2024-04-12)

### Fixed

- `region` fieldtype: same ISO 3166-2 one-character subdivision code fix as 1.2.3, applied here too.

## 1.2.3 (2024-04-12)

### Fixed

- `region_in_country` fieldtype rejected valid region codes whose subdivision segment is only one character (some ISO 3166-2 codes, e.g. `SI-001`-style short forms) — the regex required 2–3 characters. Now accepts 1–3.

## 1.2.2 (2023-12-08)

### Changed

- Significant performance improvement: country/region lookups previously instantiated `sokil/php-isocodes` with a Symfony translation driver on every `augment()` call. Names are now precompiled ahead of time into Laravel language files (`lang/{locale}/countries.php`, `lang/{locale}/regions.php`) via a new hidden `kg:scarf:generate:lang` command, and resolved through Laravel's standard `__()` translator instead.
- Added a `FetchLocale` trait that resolves the current Statamic site locale to one of the generated language files, falling back gracefully (with a cached lookup of available locales) when the site's locale isn't one sokil ships data for.
- Filters (`CountryFieldtypeFilter`, `RegionFieldtypeFilter`) are now locale-aware, matching the fieldtypes' own behavior.

## 1.2.1 (2023-12-02)

### Fixed

- Documentation corrections.

## 1.2.0 (2023-11-30)

### Added

- A new `countries_and_regions` Antlers tag (`countries` and `regions` methods) for building custom country/region `<select>` markup directly in templates, with an optional `default` parameter to mark the current value as `selected`.
- A `web` route (`countries_and_regions/{country}/regions`) alongside the existing CP action routes.

### Changed

- The `countries`/`regions` CP endpoints switched from `GET` to `POST` requests, sent with a CSRF token.

## 1.1.0 (2023-11-12)

### Added

- `render_invalid_value` config option on all three fieldtypes. When a stored value isn't a well-formed ISO 3166-1/3166-2 code (regex-validated), it's rendered as-is instead of becoming `null` — useful for content imported in a non-standard format.

### Changed

- Country/region code validation moved from a bare `try`/`catch` around the ISO code lookup to an explicit regex check beforehand, so malformed values are caught consistently and predictably.

### Removed

- A stray leftover debug method (`toShallowAugmentedCollection`) on `CountryFieldtype`.

## 1.0.4 (2023-11-02)

### Fixed

- Empty/falsy values passed to `augment()` on `CountryFieldtype`/`RegionFieldtype` no longer error — they return `null`.
- `RegionInCountryFieldtype::augment()` now catches lookup failures for unrecognized country or region codes and degrades gracefully (returns `null`, or just the country name if only the region lookup failed) instead of throwing.

## 1.0.3 (2023-05-20)

### Fixed

- Documentation corrections.

## 1.0.2 (2023-03-25)

### Added

- `augment()` on `CountryFieldtype`/`RegionFieldtype` now handles `multiple`-mode values, returning an array of localized names when the stored value is an array instead of just a single code.

## 1.0.1 (2023-03-25)

### Fixed

- Documentation corrections.

## 1.0.0 (2023-03-25)

Initial public release.

### Added

- `country` fieldtype (ISO 3166-1) with `placeholder`, `multiple`, `max_items`, `clearable`, and `default` config options.
- `region` fieldtype (ISO 3166-2), filterable by a linked sibling country field (`countries_field`) or a manually configured list of countries (`countries_manual`), with the same `placeholder`/`multiple`/`max_items`/`clearable`/`default` options.
- `region_in_country` fieldtype, combining a country and region selector into a single value, with independent placeholders for each sub-field.
- Locale-aware country/region name resolution (falls back through Statamic's current site locale).
- `CountryFieldtypeController`/`RegionFieldtypeController` endpoints backing the fieldtypes' option lists, with support for querying regions across multiple comma-separated countries.
- Listing filters (`CountryFieldtypeFilter`, `RegionFieldtypeFilter`) for the two simple fieldtypes.

### Changed

- Renamed the package/namespace from `statamic-country-and-region-fieldtype` (singular) to `statamic-country-and-region-fieldtypes` (plural).
