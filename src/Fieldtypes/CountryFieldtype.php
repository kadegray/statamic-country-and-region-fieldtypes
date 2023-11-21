<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes;

use Illuminate\Support\Facades\App;
use Statamic\Fields\Fieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\FieldtypeFilters\CountryFieldtypeFilter;
use Kadegray\StatamicCountryAndRegionFieldtypes\Traits\FetchLocale;

class CountryFieldtype extends Fieldtype
{
    use FetchLocale;

    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }

    public function filter()
    {
        return new CountryFieldtypeFilter($this);
    }

    protected function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                'type' => 'text',
                'default' => 'Select country...',
                'width' => 50,
            ],
            'multiple' => [
                'display' => __('Multiple'),
                'instructions' => __('statamic::fieldtypes.select.config.multiple'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'min' => 1,
                'type' => 'integer',
                'width' => 50,
            ],
            'clearable' => [
                'display' => __('Clearable'),
                'instructions' => __('statamic::fieldtypes.select.config.clearable'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'country',
                'width' => 50,
            ],
            'render_invalid_value' => [
                'display' => __('Render Invalid Value'),
                'instructions' => 'In antlers, this will render the raw value if the value is not a country code (ISO 3166-1). A use case for this would be if you have imported country data that is not in country code format and you want it to render anyway.',
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
        ];
    }

    public function augment($value)
    {
        $countries = [];

        if (is_string($value)) {
            $countries[] = $value;
        } else {
            $countries = $value;
        }

        if (!$countries) {
            return null;
        }

        $renderInvalidValue = $this->config('render_invalid_value');

        $originalLocale = App::currentLocale();
        $scarfLocale = $this->getScarfLocale();
        if ($scarfLocale) {
            App::setLocale($scarfLocale);
        }

        $iso31661Regex = '/^[A-Za-z0-9]{2}$/';
        foreach ($countries as &$country) {

            $valid = preg_match($iso31661Regex, $country);
            if ($valid !== 1) {
                if (!$renderInvalidValue) {
                    $country = null;
                }
                continue;
            }

            $langKey = "kadegray_scarf::countries.{$country}";
            $countryLocalName = __($langKey);
            if ($countryLocalName === $langKey) {
                if (!$renderInvalidValue) {
                    $country = null;
                }
                continue;
            }

            $country = $countryLocalName;
        }

        if ($scarfLocale) {
            App::setLocale($originalLocale);
        }

        if (count($countries) > 1) {
            return $countries;
        }

        return $countries[0];
    }
}
