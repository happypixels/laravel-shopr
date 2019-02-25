<?php

namespace Happypixels\Shopr\PaymentProviders;

class Stripe extends PaymentProvider
{
    public function purchase()
    {
        return $this->gateway->purchase([
            'amount' => $this->cart->total(),
            'currency' => config('shopr.currency'),
            'token' => $this->input['token'],
        ])->send();
    }
}
