<?php

$middleware = [
    Happypixels\Shopr\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
];

Route::group(['prefix' => 'api/shopr', 'namespace' => 'Happypixels\Shopr\Controllers', 'middleware' => $middleware], function () {
    Route::get('cart', 'CartController@index');
    Route::get('cart/count', 'CartController@count');
    Route::delete('cart', 'CartController@destroy');

    Route::apiResource('cart/items', 'CartItemController', ['only' => ['store', 'update', 'destroy']]);

    Route::post('cart/discounts', 'CartDiscountController@store');

    Route::post('checkout/charge', 'CheckoutController@charge');
});
