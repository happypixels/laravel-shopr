<?php

namespace Happypixels\Shopr\PaymentProviders;

use Omnipay\Omnipay;
use Happypixels\Shopr\Exceptions\PaymentFailedException;

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
     * Confirms a payment if needed.
     *
     * @param string $reference
     * @return array
     */
    public function confirm($reference)
    {
        $response = $this->gateway->confirm([
            'paymentIntentReference' => $reference,
            'returnUrl' => route('shopr.order-confirmation').'?gateway=Stripe',
        ])->send();

        if (! $response->isSuccessful()) {
            throw new PaymentFailedException($response->getMessage());
        }

        return [
            'success' => true,
            'transaction_reference' => $response->getTransactionReference(),
            'transaction_id' => $response->getTransactionId(),
            'payment_status' => 'paid',
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
