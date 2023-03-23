<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;
use Sokil\IsoCodes\IsoCodesFactory;
use Illuminate\Support\Str;

class RegionFieldtypeController extends BaseController
{
    public function getOptions($countries)
    {
        $locale = App::getLocale();
        putenv("LANGUAGE=$locale.UTF-8");
        putenv("LC_ALL=$locale.UTF-8");
        setlocale(LC_ALL, "$locale.UTF-8");

        if (Str::contains($countries, ',')) {
            $countries = Str::of($countries)->explode(',');
        } else if (is_string($countries)) {
            $countries = [$countries];
        }

        $regions = [];

        foreach ($countries as $country) {
            $isoCodes = new IsoCodesFactory();
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
