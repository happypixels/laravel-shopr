<?php

namespace Happypixels\Shopr\Tests\Support\Traits;

use Happypixels\Shopr\Cart\Cart;
use Happypixels\Shopr\Cart\Drivers\SessionCart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

trait InteractsWithCart
{
    public function addCartItem()
    {
        app(Cart::class)->addItem(get_class(TestShoppable::first()), 1, 1);
    }

    public function getCartCount()
    {
        return app(Cart::class)->count();
    }

    public function mockCart()
    {
        return $this->mock(Cart::class, SessionCart::class);

        return $cart;
    }
}
