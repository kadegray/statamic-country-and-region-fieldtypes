<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Sokil\IsoCodes\IsoCodesFactory;

class CountryFieldtypeController extends BaseController
{
    public function getOptions()
    {
        $locale = App::getLocale();
        putenv("LANGUAGE=$locale.UTF-8");
        putenv("LC_ALL=$locale.UTF-8");
        setlocale(LC_ALL, "$locale.UTF-8");

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
