<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Sokil\IsoCodes\IsoCodesFactory;

class RegionFieldtypeController extends BaseController
{
    public function getOptions($country)
    {
        $regions = [];
        $isoCodes = new IsoCodesFactory();
        $subdivisions = $isoCodes->getSubdivisions();
        foreach ($subdivisions->getAllByCountryCode($country) as $region) {
            $regions[] = [
                'value' => $region->getCode(),
                'label' => $region->getLocalName(),
            ];
        }

        return $regions;
    }
}
