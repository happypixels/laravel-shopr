<?php
namespace Happypixels\Shopr\Tests\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

class DiscountTestRule implements Rule
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
        return false;
    }
    
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The test rule failed.';
    }
}
