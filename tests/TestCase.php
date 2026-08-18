<?php

namespace Kadegray\StatamicCountryAndRegionFieldtypes\Tests;

use Kadegray\StatamicCountryAndRegionFieldtypes\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
