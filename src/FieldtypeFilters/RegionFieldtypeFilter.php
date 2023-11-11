<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\FieldtypeFilters;

use Statamic\Extend\HasFields;
use Sokil\IsoCodes\IsoCodesFactory;

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

        $isoCodes = new IsoCodesFactory();
        $regionName = $isoCodes->getSubdivisions()
            ->getByCode($region)
            ->getLocalName();

        $field = $this->fieldtype->field()->display();

        return $field . " is $regionName";
    }
}
