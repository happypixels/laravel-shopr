<?php

namespace Happypixels\Shopr\Rules\Discounts;

use Illuminate\Contracts\Validation\Rule;

class NotADiscount implements Rule
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
        return (new $value)->isDiscount() === false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('shopr::shoppables.invalid_shoppable');
    }
}
