<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Tests\Fieldtypes;

use Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes\RegionFieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\Tests\TestCase;
use Statamic\Fields\Field;

class RegionFieldtypeTest extends TestCase
{
    private function fieldtype(array $config = []): RegionFieldtype
    {
        return (new RegionFieldtype())->setField(
            new Field('region_field', array_merge(['type' => 'region'], $config))
        );
    }

    public function test_default_value_is_null()
    {
        $this->assertNull($this->fieldtype()->defaultValue());
    }

    public function test_pre_process_and_process_are_passthroughs()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame('US-CA', $fieldtype->preProcess('US-CA'));
        $this->assertSame('US-CA', $fieldtype->process('US-CA'));
    }

    public function test_augment_returns_null_for_empty_value()
    {
        $this->assertNull($this->fieldtype()->augment(null));
        $this->assertNull($this->fieldtype()->augment(''));
        $this->assertNull($this->fieldtype()->augment([]));
    }

    public function test_augment_returns_localized_name_for_valid_code()
    {
        $this->assertSame('California', $this->fieldtype()->augment('US-CA'));
        $this->assertSame('Ontario', $this->fieldtype()->augment('CA-ON'));
    }

    public function test_augment_returns_array_of_names_for_multiple_values()
    {
        $this->assertSame(
            ['California', 'Ontario'],
            $this->fieldtype()->augment(['US-CA', 'CA-ON'])
        );
    }

    public function test_augment_returns_null_for_invalid_format_by_default()
    {
        // Missing the required "country-subdivision" shape entirely.
        $this->assertNull($this->fieldtype()->augment('NOTREAL'));
        // Syntactically shaped like a region code, but not a real subdivision.
        $this->assertNull($this->fieldtype()->augment('US-ZZ'));
    }

    public function test_augment_returns_raw_value_for_invalid_code_when_render_invalid_value_enabled()
    {
        $fieldtype = $this->fieldtype(['render_invalid_value' => true]);

        $this->assertSame('NOTREAL', $fieldtype->augment('NOTREAL'));
        $this->assertSame('US-ZZ', $fieldtype->augment('US-ZZ'));
    }
}
