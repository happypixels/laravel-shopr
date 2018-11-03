<?php

namespace Happypixels\Shopr\Rules;

use Happypixels\Shopr\Models\DiscountCoupon;
use Illuminate\Contracts\Validation\Rule;

class ValidDiscountCoupon implements Rule
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
        return DiscountCoupon::valid()->where('code', $value)->count() > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid discount coupon.';
    }
}
