<?php

namespace Happypixels\Shopr\Controllers;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Rules\CouponHasntBeenApplied;
use Happypixels\Shopr\Rules\CouponIsValid;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;

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
        $this->validate($request, [
            'code' => [
                'required',
                new CouponHasntBeenApplied,
                new CouponIsValid
            ]
        ]);

        $coupon = DiscountCoupon::valid()->where('code', $request->code)->first();

        $this->cart->applyDiscountCoupon($coupon);

        #Event::fire('shopr.cart.discount.applied', $coupon);

        return $this->cart->summary();
    }
}
