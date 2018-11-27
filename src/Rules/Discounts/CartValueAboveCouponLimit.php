<?php

namespace Happypixels\Shopr\Rules\Discounts;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Illuminate\Contracts\Validation\Rule;

class CartValueAboveCouponLimit implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $coupon = DiscountCoupon::where('code', $value)->first();

        if (!$coupon) {
            return false;
        }

        $cart = app(Cart::class);

        if ($coupon->lower_cart_limit === null) {
            $limit = $coupon->getCalculatedPositiveValue();
        } else {
            $limit = $coupon->lower_cart_limit;
        }

        return app(Cart::class)->total() >= $limit;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('shopr::discounts.invalid_coupon');
    }
}
