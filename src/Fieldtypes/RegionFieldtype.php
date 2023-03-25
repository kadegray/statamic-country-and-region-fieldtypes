<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\FieldtypeFilters\RegionFieldtypeFilter;
use Sokil\IsoCodes\IsoCodesFactory;
use Statamic\Facades\Site;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;

class RegionFieldtype extends Fieldtype
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
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                'type' => 'text',
                'default' => 'Select region...',
                'width' => 50,
            ],
            'country' => [
                'display' => 'Country',
                'instructions' => 'Select how you would like to filter Regions by Country.',
                'type' => 'select',
                'default' => 'countries_field',
                'options' => [
                    'countries_field' =>  'Field',
                    'countries_manual' => 'Manual',
                ],
                'width' => 50,
            ],
            'countries_manual' => [
                'display' => 'Manually defined Countries',
                'instructions' => 'Select specific Countries.',
                'type' => 'country',
                'multiple' => true,
                'width' => 50,
                'if' => [
                    'country' => 'countries_manual',
                ]
            ],
            'countries_field' => [
                'display' => 'Country field handle',
                'instructions' => 'This should be the handle of the Country Fieldtype that you would like to use.',
                'type' => 'text',
                'default' => 'country',
                'width' => 50,
                'if' => [
                    'country' => 'countries_field',
                ]
            ],
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'min' => 1,
                'type' => 'integer',
                'width' => 50,
            ],
            'multiple' => [
                'display' => __('Multiple'),
                'instructions' => __('statamic::fieldtypes.select.config.multiple'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
            'clearable' => [
                'display' => __('Clearable'),
                'instructions' => __('statamic::fieldtypes.select.config.clearable'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
        ];
    }

    public function augment($value)
    {
        $currentLocale = data_get(Site::current(), 'locale');

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($currentLocale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        $regions = [];

        if (is_string($value)) {
            $regions[] = $value;
        } else {
            $regions = $value;
        }

        foreach ($regions as &$region) {
            $region = $isoCodes->getSubdivisions()
                ->getByCode($region)
                ->getLocalName();
        }

        if (count($regions) > 1) {
            return $regions;
        }

        return $regions[0];
    }
}
