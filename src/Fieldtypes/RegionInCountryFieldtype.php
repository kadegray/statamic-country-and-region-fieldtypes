<?php

namespace Kadegray\StatamicCountryAndRegionFieldtype\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Kadegray\StatamicCountryAndRegionFieldtype\FieldtypeFilters\RegionFieldtypeFilter;
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
        return [];
    }

    public function augment($region)
    {
        $currentLocale = data_get(Site::current(), 'locale');

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($currentLocale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        list($country) = explode('-', $region);

        $countryName = $isoCodes->getCountries()
            ->getByAlpha2($country)
            ->getLocalName();

        $regionName = $isoCodes->getSubdivisions()
            ->getByCode($region)
            ->getLocalName();

        return "$regionName, $countryName";
    }
}
