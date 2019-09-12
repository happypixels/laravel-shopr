<?php

$middleware = [
    Happypixels\Shopr\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
];

Route::group(['middleware' => $middleware, 'namespace' => 'Happypixels\Shopr\Controllers\Web'], function () {
    // Cart.
    if (config('shopr.templates.cart')) {
        Route::view('cart', config('shopr.templates.cart'))->name('shopr.cart');
    }

    // Checkout.
    if (config('shopr.templates.checkout')) {
        Route::view('checkout', config('shopr.templates.checkout'))
            ->name('shopr.checkout')
            ->middleware(Happypixels\Shopr\Middleware\CartMustHaveItems::class);
    }

    // Order confirmation.
    if (config('shopr.templates.order-confirmation')) {
        Route::get('order-confirmation', 'OrderController@confirmation')
            ->name('shopr.order-confirmation')
            ->middleware(Happypixels\Shopr\Middleware\RequireOrderToken::class);
    }

    Route::get('payments/confirm', 'PaymentConfirmationController')->name('shopr.payments.confirm');
});
