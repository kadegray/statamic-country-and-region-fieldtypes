<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes;

use Kadegray\StatamicCountryAndRegionFieldtypes\Console\Commands\GenerateLanguageFiles;
use Statamic\Providers\AddonServiceProvider;
use Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes\CountryFieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes\RegionFieldtype;
use Kadegray\StatamicCountryAndRegionFieldtypes\Fieldtypes\RegionInCountryFieldtype;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'actions' => __DIR__ . '/../routes/actions.php',
        'web' => __DIR__ . '/../routes/web.php',
    ];

    protected $scripts = [
        __DIR__ . '/../public/js/addon.js'
    ];

    protected $tags = [
        \Kadegray\StatamicCountryAndRegionFieldtypes\Tags\CountriesAndRegions::class,
    ];

    public function bootAddon()
    {
        CountryFieldtype::register();
        RegionFieldtype::register();
        RegionInCountryFieldtype::register();

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateLanguageFiles::class,
            ]);
        }

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'kadegray_scarf');
    }
}
