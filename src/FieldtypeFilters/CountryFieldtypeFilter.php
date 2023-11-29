<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\FieldtypeFilters;

use Statamic\Extend\HasFields;
use Sokil\IsoCodes\IsoCodesFactory;
use Statamic\Facades\Site;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;

class CountryFieldtypeFilter
{
    use HasFields;

    protected $fieldtype;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function fieldItems()
    {
        return [
            'value' => [
                'type' => 'country',
            ]
        ];
    }

    public function apply($query, $handle, $values)
    {
        $country = data_get($values, 'value');
        if ($country) {
            $query->where($handle, $country);
        }
    }

    public function badge($values)
    {
        $country = data_get($values, 'value');
        if (!$country) {

            return;
        }

        $currentLocale = data_get(Site::current(), 'locale');

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($currentLocale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        $countryCode = $isoCodes->getCountries()->getByAlpha2($country);
        $countryName = $countryCode ? $countryCode->getLocalName() : null;

        $field = $this->fieldtype->field()->display();

        return $field . " is $country" . ($countryName ? " ($countryName)" : "");
    }
}
