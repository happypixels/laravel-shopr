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

    Route::post('checkout/charge', 'CheckoutController@charge');

    Route::post('orders', 'OrderController@store');
    Route::post('orders/confirm', 'OrderController@confirm');

    Route::group(['prefix' => 'webhooks', 'namespace' => 'Webhooks'], function () {
        Route::post('kco/validate', 'KlarnaCheckoutController@validate');
        Route::post('kco/push', 'KlarnaCheckoutController@push');
    });
});
