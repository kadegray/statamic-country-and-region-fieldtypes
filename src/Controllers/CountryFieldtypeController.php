<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Sokil\IsoCodes\IsoCodesFactory;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;

class CountryFieldtypeController extends BaseController
{
    public function getOptions()
    {
        $locale = App::getLocale();

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($locale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        $countries = [];
        foreach ($isoCodes->getCountries() as $country) {
            $countries[] = [
                'value' => $country->getAlpha2(),
                'label' => $country->getLocalName(),
            ];
        }

        return $countries;
    }
}
