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
}
