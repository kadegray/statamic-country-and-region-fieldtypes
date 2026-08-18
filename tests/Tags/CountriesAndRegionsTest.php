<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Tests\Tags;

use Kadegray\StatamicCountryAndRegionFieldtypes\Tags\CountriesAndRegions;
use Kadegray\StatamicCountryAndRegionFieldtypes\Tests\TestCase;
use Statamic\Facades\Antlers;

class CountriesAndRegionsTest extends TestCase
{
    private function tag(array $params = []): CountriesAndRegions
    {
        $tag = new CountriesAndRegions();
        $tag->setProperties([
            'parser' => null,
            'content' => '',
            'context' => [],
            'params' => $params,
            'tag' => 'countries_and_regions',
            'tag_method' => 'index',
        ]);

        return $tag;
    }

    public function test_countries_returns_iso_code_and_name_for_each_country()
    {
        $countries = collect($this->tag()->countries())->keyBy('isoCode');

        $this->assertSame('United States', $countries['US']['countryName']);
        $this->assertSame('Canada', $countries['CA']['countryName']);
    }

    public function test_countries_does_not_include_selected_key_without_default_param()
    {
        $countries = $this->tag()->countries();

        $this->assertArrayNotHasKey('selected', $countries[0]);
    }

    public function test_countries_marks_the_default_as_selected_case_insensitively()
    {
        $countries = collect($this->tag(['default' => 'us'])->countries())->keyBy('isoCode');

        $this->assertSame('selected', $countries['US']['selected']);
        $this->assertSame('', $countries['CA']['selected']);
    }

    public function test_regions_returns_iso_code_and_name_for_a_single_country()
    {
        $regions = collect($this->tag(['country' => 'US'])->regions())->keyBy('isoCode');

        $this->assertSame('California', $regions['US-CA']['regionName']);
        $this->assertArrayNotHasKey('CA-ON', $regions);
    }

    public function test_regions_combines_multiple_comma_separated_countries()
    {
        $regions = collect($this->tag(['country' => 'US,CA'])->regions())->keyBy('isoCode');

        $this->assertArrayHasKey('US-CA', $regions);
        $this->assertArrayHasKey('CA-ON', $regions);
    }

    public function test_regions_marks_the_default_as_selected_case_insensitively()
    {
        $regions = collect($this->tag(['country' => 'US', 'default' => 'us-ca'])->regions())->keyBy('isoCode');

        $this->assertSame('selected', $regions['US-CA']['selected']);
    }

    public function test_regions_only_processes_the_first_ten_countries()
    {
        $countries = implode(',', array_fill(0, 10, 'US')).',BR';

        $regions = collect($this->tag(['country' => $countries])->regions());

        $this->assertTrue($regions->contains(fn ($region) => str_starts_with($region['isoCode'], 'US-')));
        $this->assertFalse($regions->contains(fn ($region) => str_starts_with($region['isoCode'], 'BR-')));
    }

    public function test_regions_ignores_malformed_country_segments()
    {
        $regions = collect($this->tag(['country' => 'US,not-a-code,123'])->regions())->keyBy('isoCode');

        $this->assertArrayHasKey('US-CA', $regions);
    }

    public function test_it_is_registered_and_renders_through_antlers()
    {
        $result = (string) Antlers::parse(
            '{{ countries_and_regions:countries default="US" }}{{ isoCode }}:{{ countryName }}:{{ selected }};{{ /countries_and_regions:countries }}',
            [],
            trusted: true
        );

        $this->assertStringContainsString('US:United States:selected;', $result);
    }
}
