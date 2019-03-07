<?php

namespace Happypixels\Shopr\PaymentProviders;

class Stripe extends PaymentProvider
{
    /**
     * Makes the payment to Stripe.
     *
     * @return ResponseInterface
     */
    public function purchase()
    {
        return $this->gateway->purchase([
            'amount' => $this->cart->total(),
            'currency' => config('shopr.currency'),
            'token' => $this->input['token'],
        ])->send();
    }
}
