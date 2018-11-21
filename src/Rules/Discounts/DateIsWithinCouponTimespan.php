<?php

namespace Happypixels\Shopr\Rules\Discounts;

use Happypixels\Shopr\Models\DiscountCoupon;
use Illuminate\Contracts\Validation\Rule;

class DateIsWithinCouponTimespan implements Rule
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
        return DiscountCoupon::where('code', $value)->validTimespan()->count() > 0;
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
