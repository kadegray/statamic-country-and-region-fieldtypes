<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes;

use Error;
use Statamic\Fields\Fieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\FieldtypeFilters\RegionFieldtypeFilter;
use Sokil\IsoCodes\IsoCodesFactory;
use Statamic\Facades\Site;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;

class RegionInCountryFieldtype extends Fieldtype
{

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
        return new RegionFieldtypeFilter($this);
    }

    protected function configFieldItems(): array
    {
        return [
            'region_placeholder' => [
                'display' => __('Region Placeholder'),
                'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                'type' => 'text',
                'default' => '',
                'width' => 50,
            ],
            'country_placeholder' => [
                'display' => __('Country Placeholder'),
                'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                'type' => 'text',
                'default' => '',
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
                'display' => __('Default Country'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'country',
                'width' => 50,
            ],
            'render_invalid_value' => [
                'display' => __('Render Invalid Value'),
                'instructions' => 'In antlers, this will render the raw value if the value is not a principal subdivision code (ISO 3166-2). A use case for this would be if you have imported region data that is not in principal subdivision code format and you want it to render anyway.',
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
        ];
    }

    public function augment($value)
    {
        if (!$value) {
            return null;
        }

        $renderInvalidValue = $this->config('render_invalid_value');
        $iso31662Regex = '/^[A-Za-z0-9]{2}-[A-Za-z0-9]{2,3}$/';
        $valid = preg_match($iso31662Regex, $value);
        if ($valid !== 1) {
            return $renderInvalidValue ? $value : null;
        }

        $currentLocale = data_get(Site::current(), 'locale');

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($currentLocale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        try {
            $regionCode = $isoCodes->getSubdivisions()->getByCode($value);
            $regionLocalName = $regionCode->getLocalName();
        } catch (Error $error) {
            if ($renderInvalidValue) {
                return $value;
            }
            return null;
        }
        $regionName = $regionLocalName;

        list($country) = explode('-', $value);

        try {
            $countryCode = $isoCodes->getCountries()->getByAlpha2($country);
            $countryLocalName = $countryCode->getLocalName();
        } catch (Error $error) {
            if ($renderInvalidValue) {
                return $value;
            }
            return $regionName;
        }
        $countryName = $countryLocalName;

        return "$regionName, $countryName";
    }
}
