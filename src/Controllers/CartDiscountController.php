<?php

namespace Happypixels\Shopr\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Illuminate\Foundation\Validation\ValidatesRequests;

class CartDiscountController extends Controller
{
    use ValidatesRequests;

    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Applies a discount coupon to the cart.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        // Default validation rules that are always checked.
        $rules = ['required', 'string'];

        // Configurated rules.
        if (! empty(config('shopr.discount_coupons.validation_rules'))) {
            foreach (config('shopr.discount_coupons.validation_rules') as $rule) {
                $rules[] = new $rule;
            }
        }

        $this->validate($request, ['code' => $rules]);

        $coupon = DiscountCoupon::where('code', $request->code)->first();

        $this->cart->addDiscount($coupon);

        return $this->cart->summary();
    }
}
