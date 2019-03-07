<?php

namespace Happypixels\Shopr\Rules\Discounts;

use Happypixels\Shopr\Cart\Cart;
use Illuminate\Contracts\Validation\Rule;

class OnlyOneCouponPerOrder implements Rule
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
        return app(Cart::class)->hasDiscount() === false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('shopr::discounts.other_coupon_applied');
    }
}
