<?php

namespace Happypixels\Shopr\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Happypixels\Shopr\Facades\Cart;
use Illuminate\Foundation\Validation\ValidatesRequests;

class CartDiscountController extends Controller
{
    use ValidatesRequests;

    /**
     * Applies a discount coupon to the cart.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, ['code' => ['required', 'string']]);

        Cart::addDiscount($request->code);

        return Cart::get();
    }
}
