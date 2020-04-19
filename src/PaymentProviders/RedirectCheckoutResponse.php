<?php

namespace Happypixels\Shopr\PaymentProviders;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class RedirectCheckoutResponse extends CheckoutResponse implements Arrayable, Jsonable
{
    /**
     * Create an instance of the response.
     *
     * @param string $transactionReference
     * @param string $redirectUrl
     */
    public function __construct($transactionReference, $redirectUrl)
    {
        $this->transactionReference = $transactionReference;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * Returns the payment status.
     *
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return 'pending';
    }

    /**
     * Whether the payment requires confirmation.
     *
     * @return bool
     */
    public function requiresConfirmation(): bool
    {
        return true;
    }

    /**
     * Whether the payment was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return false;
    }

    /**
     * Returns the URL to the payment confirmation page.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Returns the array representation of the response.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'redirect' => $this->getRedirectUrl(),
        ];
    }

    /**
     * Returns the json representation of the response.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray());
    }
}
