<?php

namespace Happypixels\Shopr\Rules;

use Happypixels\Shopr\Contracts\Cart;
use Illuminate\Contracts\Validation\Rule;

class CartNotEmpty implements Rule
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
        return (!app(Cart::class)->isEmpty());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Your cart is empty.';
    }
}
