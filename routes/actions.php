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

Route::post('countries', [CountryFieldtypeController::class, 'getOptions'])->middleware('throttle:kadegray_scarf.options');
Route::post('{country}/regions', [RegionFieldtypeController::class, 'getOptions'])->middleware('throttle:kadegray_scarf.options');
