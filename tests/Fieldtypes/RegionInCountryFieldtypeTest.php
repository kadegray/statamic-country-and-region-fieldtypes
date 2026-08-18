<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Tests\Fieldtypes;

use Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes\RegionInCountryFieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\Tests\TestCase;
use Statamic\Fields\Field;

class RegionInCountryFieldtypeTest extends TestCase
{
    private function fieldtype(array $config = []): RegionInCountryFieldtype
    {
        return (new RegionInCountryFieldtype())->setField(
            new Field('region_in_country_field', array_merge(['type' => 'region_in_country'], $config))
        );
    }

    public function test_default_value_is_null()
    {
        $this->assertNull($this->fieldtype()->defaultValue());
    }

    public function test_field_default_value_config_is_used_when_present()
    {
        // The fieldtype's own defaultValue() always returns null; Statamic's core
        // Field class is what actually resolves the "Default Value" config option,
        // falling back to the fieldtype's defaultValue() only when unset.
        $field = new Field('region_in_country_field', ['type' => 'region_in_country', 'default' => 'US']);

        $this->assertSame('US', $field->defaultValue());
    }

    public function test_field_default_value_falls_back_to_fieldtype_default_when_unset()
    {
        $field = new Field('region_in_country_field', ['type' => 'region_in_country']);

        $this->assertNull($field->defaultValue());
    }

    public function test_pre_process_and_process_are_passthroughs()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame('CA-ON', $fieldtype->preProcess('CA-ON'));
        $this->assertSame('CA-ON', $fieldtype->process('CA-ON'));
    }

    public function test_augment_returns_null_for_empty_value()
    {
        $this->assertNull($this->fieldtype()->augment(null));
        $this->assertNull($this->fieldtype()->augment(''));
    }

    public function test_augment_combines_region_and_country_for_a_full_region_code()
    {
        $this->assertSame('Ontario, Canada', $this->fieldtype()->augment('CA-ON'));
        $this->assertSame('California, United States', $this->fieldtype()->augment('US-CA'));
    }

    public function test_augment_treats_bare_country_code_as_invalid_when_region_is_required()
    {
        // region_is_required defaults to true, so a country-only value isn't a complete answer.
        $this->assertNull($this->fieldtype()->augment('US'));
    }

    public function test_augment_accepts_bare_country_code_when_region_is_not_required()
    {
        $fieldtype = $this->fieldtype(['region_is_required' => false]);

        $this->assertSame('United States', $fieldtype->augment('US'));
        $this->assertSame('Canada', $fieldtype->augment('CA'));
    }

    public function test_augment_returns_null_for_invalid_value_by_default()
    {
        $this->assertNull($this->fieldtype()->augment('NOTREAL'));
    }

    public function test_augment_returns_raw_value_for_invalid_value_when_render_invalid_value_enabled()
    {
        $fieldtype = $this->fieldtype(['render_invalid_value' => true]);

        $this->assertSame('NOTREAL', $fieldtype->augment('NOTREAL'));
    }
}
