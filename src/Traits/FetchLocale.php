<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Site;

trait FetchLocale
{
    public $twentyFourHoursInSeconds = 24 * 60 * 60;

    public function getScarfLocale()
    {
        $statamicLocale = data_get(Site::current(), 'locale');
        $sokilLocales = $this->getSokilLocales();

        if (in_array($statamicLocale, $sokilLocales)) {
            return $statamicLocale;
        }

        $pattern = "/([a-zA-Z]{1,3})([^a-zA-Z0-9])([a-zA-Z]{1,3})/i";
        preg_match_all($pattern, $statamicLocale, $result);
        [,
            $locale,
            // $separator,
            // $localeLang
        ] = collect($result)->flatten();

        if (in_array($locale, $sokilLocales)) {
            return $locale;
        }

        return null;
    }

    public function getSokilLocales()
    {
        // sokil/php-isocodes-db-i18n locales.
        $locales = Cache::remember('kadegray_scarf_sokil_locales', $this->twentyFourHoursInSeconds, function () {
            $locales = scandir(__DIR__ . "/../../vendor/sokil/php-isocodes-db-i18n/messages/");
            $locales = Arr::where($locales, function (string $value) {
                return !in_array($value, ['.', '..', 'LICENSE']);
            });
            return $locales;
        });

        return $locales;
    }
}
