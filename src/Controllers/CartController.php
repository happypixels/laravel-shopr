<?php

namespace Happypixels\Shopr\Controllers;

use Illuminate\Routing\Controller;
use Happypixels\Shopr\Facades\Cart;

class CartController extends Controller
{
    /**
     * Returns the full cart summary.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return Cart::get();
    }

    /**
     * Returns the count of the cart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function count()
    {
        return ['count' => Cart::count()];
    }

    /**
     * Clears the cart and returns the full cart summary.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        Cart::clear();

        return Cart::get();
    }
}
