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
        ];
    }

    public function augment($region)
    {
        $currentLocale = data_get(Site::current(), 'locale');

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($currentLocale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        list($country) = explode('-', $region);

        try {
            $countryName = $isoCodes->getCountries()
                ->getByAlpha2($country)
                ->getLocalName();
        } catch (Error $error) {
            return null;
        }

        try {
            $regionName = $isoCodes->getSubdivisions()
                ->getByCode($region)
                ->getLocalName();
        } catch (Error $error) {
            return "$countryName";
        }

        return "$regionName, $countryName";
    }
}
