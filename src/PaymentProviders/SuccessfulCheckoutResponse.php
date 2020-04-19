<?php

namespace Happypixels\Shopr\PaymentProviders;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class SuccessfulCheckoutResponse extends CheckoutResponse implements Arrayable, Jsonable
{
    /**
     * Create an instance of the response.
     *
     * @param string $transactionReference
     */
    public function __construct($transactionReference)
    {
        $this->transactionReference = $transactionReference;
    }

    /**
     * Returns the payment status.
     *
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return 'paid';
    }

    /**
     * Whether the payment requires confirmation.
     *
     * @return bool
     */
    public function requiresConfirmation(): bool
    {
        return false;
    }

    /**
     * Whether the payment was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return true;
    }

    /**
     * Returns the URL to the order confirmation page, if enabled.
     *
     * @return string|null
     */
    public function getRedirectUrl()
    {
        if (config('shopr.templates.order-confirmation')) {
            return route('shopr.order-confirmation', ['token' => optional($this->order)->token]);
        }
    }

    /**
     * Returns the array representation of the checkout result, which is the created order.
     *
     * @return array
     */
    public function toArray()
    {
        return optional($this->order)->toArray() ?: [];
    }

    /**
     * Returns the json representation of the checkout result.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $response = ['token' => optional($this->order)->token];

        if ($redirectUrl = $this->getRedirectUrl()) {
            $response['redirect'] = $redirectUrl;
        }

        return json_encode($response);
    }
}
