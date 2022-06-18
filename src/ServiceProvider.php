<?php

namespace Kadegray\StatamicCountryAndRegionFieldtype;

use Statamic\Providers\AddonServiceProvider;
use Kadegray\StatamicCountryAndRegionFieldtype\Fieldtypes\CountryFieldtype;
use Kadegray\StatamicCountryAndRegionFieldtype\Fieldtypes\RegionFieldtype;
use Kadegray\StatamicCountryAndRegionFieldtype\Fieldtypes\RegionInCountryFieldtype;

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
