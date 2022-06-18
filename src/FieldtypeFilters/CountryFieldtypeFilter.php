<?php

namespace Kadegray\StatamicCountryAndRegionFieldtype\FieldtypeFilters;

use Statamic\Extend\HasFields;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Sokil\IsoCodes\IsoCodesFactory;

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

        $isoCodes = new IsoCodesFactory();
        $countryName = $isoCodes->getCountries()
            ->getByAlpha2($country)
            ->getLocalName();

        $field = $this->fieldtype->field()->display();

        return $field . " is $countryName";
    }
}
