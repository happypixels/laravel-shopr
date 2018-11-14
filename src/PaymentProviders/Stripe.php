<?php

namespace Happypixels\Shopr\PaymentProviders;

use Happypixels\Shopr\Models\Order;

class Stripe extends PaymentProvider
{
    public function create($order)
    {
        // Make the purchase.
        $response = $this->gateway->purchase([
            'amount' => $order->total,
            'currency' => config('shopr.currency'),
            'token' => $this->input['token']
        ])->send();

        if ($response->isSuccessful()) {
            $order->transaction_reference = $response->getTransactionReference();
            $order->transaction_id = $response->getTransactionId();
            $order->payment_status = 'paid';
            $order->save();

            return $response;
        } else {
            // Throw exception.
            #return response()->json(['response' => $response->getMessage()], 400);
        }
    }

    /**
     * Makes sure there's a paid order matching the provided token.
     *
     * @param  string $token
     * @return boolean
     */
    public function allowConfirmationPage($token)
    {
        return ($this->getConfirmedOrder($token) !== null);
    }

    /**
     * Returns paid order matching the token if there is one.
     *
     * @param  string $token
     * @return Order|null
     */
    public function getConfirmedOrder($token)
    {
        return Order::with('items')
            ->where('token', request('token'))
            ->where('payment_status', 'paid')
            ->first();
    }
}
