<?php

namespace Happypixels\Shopr\Tests\Support\Traits;

use Mockery;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Repositories\SessionCartRepository;
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
        $cart = Mockery::mock(SessionCartRepository::class);
        $this->app->instance(Cart::class, $cart);

        return $cart;
    }
}
