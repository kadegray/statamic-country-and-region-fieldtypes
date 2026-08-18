<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Tests\Controllers;

use Kadegray\StatamicCountryAndRegionFieldtypes\Tests\TestCase;

class CountryFieldtypeControllerTest extends TestCase
{
    public function test_it_returns_a_list_of_countries()
    {
        $response = $this->post('/!/statamic-country-and-region-fieldtypes/countries');

        $response->assertOk();

        $countries = $response->json();

        $this->assertIsArray($countries);
        $this->assertNotEmpty($countries);
        $this->assertArrayHasKey('value', $countries[0]);
        $this->assertArrayHasKey('label', $countries[0]);

        $this->assertTrue(collect($countries)->contains(fn ($country) => $country['value'] === 'US' && $country['label'] === 'United States'));
        $this->assertTrue(collect($countries)->contains(fn ($country) => $country['value'] === 'CA' && $country['label'] === 'Canada'));
    }
}
