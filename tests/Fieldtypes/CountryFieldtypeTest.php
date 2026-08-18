<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Tests\Fieldtypes;

use Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes\CountryFieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\Tests\TestCase;

class CountryFieldtypeTest extends TestCase
{
    private function fieldtype(array $config = []): CountryFieldtype
    {
        return (new CountryFieldtype())->setField(
            new \Statamic\Fields\Field('country_field', array_merge(['type' => 'country'], $config))
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
        $field = new \Statamic\Fields\Field('country_field', ['type' => 'country', 'default' => 'US']);

        $this->assertSame('US', $field->defaultValue());
    }

    public function test_field_default_value_falls_back_to_fieldtype_default_when_unset()
    {
        $field = new \Statamic\Fields\Field('country_field', ['type' => 'country']);

        $this->assertNull($field->defaultValue());
    }

    public function test_pre_process_and_process_are_passthroughs()
    {
        $fieldtype = $this->fieldtype();

        $this->assertSame('US', $fieldtype->preProcess('US'));
        $this->assertSame('US', $fieldtype->process('US'));
        $this->assertNull($fieldtype->preProcess(null));
        $this->assertNull($fieldtype->process(null));
    }

    public function test_augment_returns_null_for_empty_value()
    {
        $this->assertNull($this->fieldtype()->augment(null));
        $this->assertNull($this->fieldtype()->augment(''));
        $this->assertNull($this->fieldtype()->augment([]));
    }

    public function test_augment_returns_localized_name_for_valid_code()
    {
        $this->assertSame('United States', $this->fieldtype()->augment('US'));
        $this->assertSame('Canada', $this->fieldtype()->augment('CA'));
    }

    public function test_augment_returns_array_of_names_for_multiple_values()
    {
        $this->assertSame(
            ['United States', 'Canada'],
            $this->fieldtype()->augment(['US', 'CA'])
        );
    }

    public function test_augment_returns_null_for_invalid_code_by_default()
    {
        $this->assertNull($this->fieldtype()->augment('NOTREAL'));
        $this->assertNull($this->fieldtype()->augment('ZZ'));
    }

    public function test_augment_returns_raw_value_for_invalid_code_when_render_invalid_value_enabled()
    {
        $fieldtype = $this->fieldtype(['render_invalid_value' => true]);

        $this->assertSame('NOTREAL', $fieldtype->augment('NOTREAL'));
        $this->assertSame('ZZ', $fieldtype->augment('ZZ'));
    }

    public function test_augment_treats_valid_but_unknown_two_char_code_as_invalid()
    {
        // ZZ is a syntactically valid ISO 3166-1 alpha-2 shape but not a real country.
        $this->assertNull($this->fieldtype()->augment('ZZ'));
    }
}
