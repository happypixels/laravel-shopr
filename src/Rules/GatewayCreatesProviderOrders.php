<?php

namespace Happypixels\Shopr\Rules;

use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;
use Illuminate\Contracts\Validation\Rule;

class GatewayCreatesProviderOrders implements Rule
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
        try {
            $provider = PaymentProviderManager::make(request());

            return $provider->createsProviderOrders();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This payment provider does not allow creating orders.';
    }
}
