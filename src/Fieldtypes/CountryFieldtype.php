<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\FieldtypeFilter;
use Kadegray\StatamicCountryAndRegionFieldtypes\FieldtypeFilters\CountryFieldtypeFilter;
use Sokil\IsoCodes\IsoCodesFactory;
use Illuminate\Support\Facades\App;
use Statamic\Facades\Site;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;

class CountryFieldtype extends Fieldtype
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
        return new CountryFieldtypeFilter($this);
    }

    public function augment($value)
    {
        $currentLocale = data_get(Site::current(), 'locale');

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($currentLocale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        $countryName = $isoCodes->getCountries()
            ->getByAlpha2($value)
            ->getLocalName();

        return $countryName;
    }

    protected function configFieldItems(): array
    {
        return [
            'language' => [
                'type' => 'hidden',
                'default' => App::getLocale(),
            ],
            'localizable' => [
                'type' => 'hidden',
                'value' => false,
                'default' => false,
            ]
        ];
    }
}
