<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Tests\Controllers;

use Kadegray\StatamicCountryAndRegionFieldtypes\Tests\TestCase;

class RegionFieldtypeControllerTest extends TestCase
{
    public function test_it_returns_regions_for_a_single_country()
    {
        $response = $this->post('/!/statamic-country-and-region-fieldtypes/US/regions');

        $response->assertOk();

        $regions = $response->json();

        $this->assertIsArray($regions);
        $this->assertNotEmpty($regions);
        $this->assertArrayHasKey('value', $regions[0]);
        $this->assertArrayHasKey('label', $regions[0]);

        $this->assertTrue(collect($regions)->contains(fn ($region) => $region['value'] === 'US-CA' && $region['label'] === 'California'));
        $this->assertFalse(collect($regions)->contains(fn ($region) => str_starts_with($region['value'], 'CA-')));
    }

    public function test_it_combines_regions_for_multiple_comma_separated_countries()
    {
        $response = $this->post('/!/statamic-country-and-region-fieldtypes/US,CA/regions');

        $response->assertOk();

        $regions = $response->json();

        $this->assertTrue(collect($regions)->contains(fn ($region) => $region['value'] === 'US-CA' && $region['label'] === 'California'));
        $this->assertTrue(collect($regions)->contains(fn ($region) => $region['value'] === 'CA-ON' && $region['label'] === 'Ontario'));
    }

    public function test_the_public_web_route_returns_regions_for_a_single_country()
    {
        $response = $this->post('/countries_and_regions/US/regions');

        $response->assertOk();

        $regions = $response->json();

        $this->assertIsArray($regions);
        $this->assertNotEmpty($regions);
        $this->assertTrue(collect($regions)->contains(fn ($region) => $region['value'] === 'US-CA' && $region['label'] === 'California'));
        $this->assertFalse(collect($regions)->contains(fn ($region) => str_starts_with($region['value'], 'CA-')));
    }

    public function test_the_public_web_route_is_rate_limited()
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->post('/countries_and_regions/US/regions')->assertOk();
        }

        $this->post('/countries_and_regions/US/regions')->assertStatus(429);
    }

    public function test_the_public_route_and_cp_route_have_independent_rate_limit_buckets()
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->post('/countries_and_regions/US/regions')->assertOk();
        }

        $this->post('/countries_and_regions/US/regions')->assertStatus(429);

        // The CP action route uses a separate rate-limit bucket, so exhausting
        // the public route's budget above doesn't affect it.
        $this->post('/!/statamic-country-and-region-fieldtypes/US/regions')->assertOk();
    }

    public function test_it_only_processes_the_first_ten_countries()
    {
        $countries = implode(',', array_fill(0, 10, 'US')).',BR';

        $response = $this->post("/countries_and_regions/{$countries}/regions");

        $response->assertOk();

        $regions = $response->json();

        $this->assertTrue(collect($regions)->contains(fn ($region) => str_starts_with($region['value'], 'US-')));
        $this->assertFalse(collect($regions)->contains(fn ($region) => str_starts_with($region['value'], 'BR-')));
    }

    public function test_it_ignores_malformed_country_segments()
    {
        $response = $this->post('/countries_and_regions/US,not-a-code,123/regions');

        $response->assertOk();

        $regions = $response->json();

        $this->assertTrue(collect($regions)->contains(fn ($region) => $region['value'] === 'US-CA'));
    }
}
