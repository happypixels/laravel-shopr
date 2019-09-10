<?php

namespace Happypixels\Shopr\PaymentProviders;

use Omnipay\Omnipay;

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
            'paymentMethod' => $this->input['payment_method_id'],
            'returnUrl' => route('shopr.payments.confirm', ['gateway' => 'Stripe']),
            'confirm' => true,
        ])->send();
    }

    /**
     * The data used for confirming a payment, used for example when confirming a payment using SCA.
     * The payment reference should be found in the $this->input-array.
     *
     * @return array
     */
    public function getPaymentConfirmationData() : array
    {
        return [
            'paymentIntentReference' => $this->input['payment_intent'],
            'returnUrl' => route('shopr.order-confirmation', ['gateway' => 'Stripe']),
        ];
    }

    /**
     * Initializes and authorizes the gateway with the credentials.
     *
     * @return Happypixels\Shopr\PaymentProviders\PaymentProvider
     */
    public function initialize()
    {
        $this->gateway = Omnipay::create('Stripe_PaymentIntents');
        $this->gateway->initialize($this->config);

        return $this;
    }
}
