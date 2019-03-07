<?php

namespace Happypixels\Shopr\Cart\Drivers;

use Happypixels\Shopr\Cart\Cart;
use Illuminate\Support\Facades\Session;

class SessionCart extends Cart
{
    /**
     * The key under which the cart is persisted.
     *
     * @var string
     */
    private $cartKey = 'shopr.cart';

    /**
     * Retrieve all cart items from the store.
     *
     * @return array|null
     */
    public function get()
    {
        return unserialize(Session::get($this->cartKey)) ?: null;
    }

    /**
     * Persists the cart data as a session.
     *
     * @return void
     */
    public function persist($data)
    {
        Session::put($this->cartKey, serialize($data));
    }
}
