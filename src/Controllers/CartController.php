<?php

namespace Happypixels\Shopr\Controllers;

use Illuminate\Routing\Controller;
use Happypixels\Shopr\Contracts\Cart;

class CartController extends Controller
{
    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        return $this->cart->summary();
    }

    public function count()
    {
        return ['count' => $this->cart->count()];
    }

    public function destroy()
    {
        $this->cart->clear();

        return $this->cart->summary();
    }
}
