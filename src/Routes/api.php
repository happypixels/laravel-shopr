<?php

use Happypixels\Shopr\Shopr;

Route::group([
    'prefix' => config('shopr.rest_api.prefix'),
    'namespace' => 'Happypixels\Shopr\Controllers',
    'middleware' => Shopr::getApiMiddleware(),
], function () {
    Route::get('cart', 'CartController@index');
    Route::get('cart/count', 'CartController@count');
    Route::delete('cart', 'CartController@destroy');
    Route::post('cart/checkout', 'CartCheckoutController');

    Route::apiResource('cart/items', 'CartItemController', ['only' => ['store', 'update', 'destroy']]);

    Route::post('cart/discounts', 'CartDiscountController@store');
});
