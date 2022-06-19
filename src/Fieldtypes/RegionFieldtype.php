<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\FieldtypeFilters\RegionFieldtypeFilter;
use Sokil\IsoCodes\IsoCodesFactory;
use Illuminate\Support\Facades\App;
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
            'language' => [
                'type' => 'hidden',
                'default' => App::getLocale(),
            ],
            'country' => [
                'display' => 'Country',
                'instructions' => 'Select how you would like to filter Regions by Country.',
                'type' => 'select',
                'default' => 'country_field',
                'options' => [
                    'country_field' =>  'Field',
                    'country_manual' => 'Manual',
                ],
                'width' => 50,
            ],
            'country_manual' => [
                'display' => 'Manually defined Country',
                'instructions' => 'Select a specific Country.',
                'type' => 'country',
                'width' => 50,
                'if' => [
                    'country' => 'country_manual',
                ]
            ],
            'country_field' => [
                'display' => 'Country field handle',
                'instructions' => 'This should be the handle of the Country FieldType that you would like to use.',
                'type' => 'text',
                'default' => 'country',
                'width' => 50,
                'if' => [
                    'country' => 'country_field',
                ]
            ],
        ];
    }

    public function augment($value)
    {
        $currentLocale = data_get(Site::current(), 'locale');

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($currentLocale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        $regionName = $isoCodes->getSubdivisions()
            ->getByCode($value)
            ->getLocalName();

        return $regionName;
    }
}
