<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\FieldtypeFilters;

use Statamic\Extend\HasFields;
use Sokil\IsoCodes\IsoCodesFactory;
use Statamic\Facades\Site;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;

class RegionFieldtypeFilter
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
                'type' => 'region_in_country',
            ]
        ];
    }

    public function apply($query, $handle, $values)
    {
        $region = data_get($values, 'value');
        if ($region) {
            $query->where($handle, $region);
        }
    }

    public function badge($values)
    {
        $region = data_get($values, 'value');
        if (!$region) {

            return;
        }

        $currentLocale = data_get(Site::current(), 'locale');

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($currentLocale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        $regionCode = $isoCodes->getSubdivisions()->getByCode($region);
        $regionName = $regionCode ? $regionCode->getLocalName() : null;

        $field = $this->fieldtype->field()->display();

        return $field . " is $region" . ($regionName ? " $regionName" : "");
    }
}
