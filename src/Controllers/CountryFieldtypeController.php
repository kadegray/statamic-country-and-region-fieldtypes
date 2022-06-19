<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Sokil\IsoCodes\IsoCodesFactory;

class CountryFieldtypeController extends BaseController
{
    public function getOptions()
    {
        $countries = [];
        $isoCodes = new IsoCodesFactory();
        foreach ($isoCodes->getCountries() as $country) {
            $countries[] = [
                'value' => $country->getAlpha2(),
                'label' => $country->getLocalName(),
            ];
        }

        return $countries;
    }
}
