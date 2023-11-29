<?php

use Illuminate\Support\Facades\Route;
use Kadegray\StatamicCountryAndRegionFieldtypes\Controllers\CountryFieldtypeController;
use Kadegray\StatamicCountryAndRegionFieldtypes\Controllers\RegionFieldtypeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('countries_and_regions/{country}/regions', [RegionFieldtypeController::class, 'getOptions']);
