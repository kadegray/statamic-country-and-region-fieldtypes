<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Sokil\IsoCodes\IsoCodesFactory;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;
use Illuminate\Support\Str;

class RegionFieldtypeController extends BaseController
{
    public function getOptions($countries)
    {
        $locale = App::getLocale();

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($locale);

        if (Str::contains($countries, ',')) {
            $countries = Str::of($countries)->explode(',');
        } else if (is_string($countries)) {
            $countries = [$countries];
        }

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
