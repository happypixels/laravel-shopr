<?php

namespace Happypixels\Shopr\Rules\Discounts;

use Happypixels\Shopr\Contracts\Cart;
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
        return 'A discount coupon has already been applied.';
    }
}
