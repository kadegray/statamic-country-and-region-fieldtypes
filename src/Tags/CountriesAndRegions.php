<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Tags;

use Sokil\IsoCodes\IsoCodesFactory;
use Sokil\IsoCodes\TranslationDriver\SymfonyTranslationDriver;
use Statamic\Facades\Site;
use Illuminate\Support\Str;

class CountriesAndRegions extends \Statamic\Tags\Tags
{
    protected static $handle = 'countries_and_regions';

    private function getIsoCodes()
    {
        $currentLocale = data_get(Site::current(), 'locale');

        $driver = new SymfonyTranslationDriver();
        $driver->setLocale($currentLocale);
        $isoCodes = new IsoCodesFactory(null, $driver);

        return $isoCodes;
    }

    public function countries()
    {
        $default = $this->params->get('default');
        $countries = [];

        $isoCodes = $this->getIsoCodes();
        foreach ($isoCodes->getCountries() as $country) {

            $countryIsoCode = $country->getAlpha2();
            $c = [
                'isoCode' => $countryIsoCode,
                'countryName' => $country->getLocalName(),
            ];

            if (!!$default) {
                $c['selected'] = Str::lower($countryIsoCode) === Str::lower($default) ? 'selected' : '';
            }

            $countries[] = $c;
        }

        return $countries;
    }

    public function regions()
    {
        $default = $this->params->get('default');
        $countries = $this->params->get('country');
        $countries = Str::contains($countries, ',') ? Str::of($countries)->explode(',') : [$countries];

        $isoCodes = $this->getIsoCodes();
        $isoCodes = $isoCodes->getSubdivisions();

        $regions = [];
        foreach ($countries as $country) {

            if (!!$country) {
                $countryIsoCodes = $isoCodes->getAllByCountryCode($country);
            }

            foreach ($countryIsoCodes as $region) {

                $regionIsoCode = $region->getCode();
                $r = [
                    'isoCode' => $regionIsoCode,
                    'regionName' => $region->getLocalName(),
                ];

                if (!!$default) {
                    $r['selected'] = Str::lower($regionIsoCode) === Str::lower($default) ? 'selected' : '';
                }

                $regions[] = $r;
            }
        }

        return $regions;
    }
}
