<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes;

use Statamic\Providers\AddonServiceProvider;
use Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes\CountryFieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes\RegionFieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes\RegionInCountryFieldtype;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'actions' => __DIR__ . '/../routes/actions.php',
    ];

    protected $scripts = [
        __DIR__ . '/../public/js/addon.js'
    ];

    public function bootAddon()
    {
        CountryFieldtype::register();
        RegionFieldtype::register();
        RegionInCountryFieldtype::register();
    }
}
