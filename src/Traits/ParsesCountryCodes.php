<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Traits;

trait ParsesCountryCodes
{
    protected function parseCountryCodes($countries, int $max = 10): array
    {
        if (! is_string($countries)) {
            return [];
        }

        return collect(explode(',', $countries))
            ->filter(fn ($code) => preg_match('/^[A-Za-z]{2}$/', $code) === 1)
            ->take($max)
            ->all();
    }
}
