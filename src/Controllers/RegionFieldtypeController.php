<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Kadegray\StatamicCountryAndRegionFieldtypes\Traits\ParsesCountryCodes;
use Sokil\IsoCodes\IsoCodesFactory;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;

class RegionFieldtypeController extends BaseController
{
    use ParsesCountryCodes;

    public function getOptions($countries)
    {
        $locale = App::getLocale();

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($locale);

        $countries = $this->parseCountryCodes($countries);

        $regions = [];

        foreach ($countries as $country) {
            $isoCodes = new IsoCodesFactory(null, $driver);
            $subdivisions = $isoCodes->getSubdivisions();
            foreach ($subdivisions->getAllByCountryCode($country) as $region) {
                $regions[] = [
                    'value' => $region->getCode(),
                    'label' => $region->getLocalName(),
                ];
            }
        }

        return $regions;
    }
}
