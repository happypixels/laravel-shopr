<?php

namespace Happypixels\Shopr\PaymentProviders;

use Illuminate\Support\Facades\Event;

class KlarnaCheckout extends PaymentProvider
{
    public function create($order)
    {
        $taxRate = config('shopr.tax');

        $data['amount']           = $this->cart->total();
        $data['currency']         = config('shopr.currency');
        $data['locale']           = app()->getLocale();
        $data['purchase_country'] = app()->getLocale();
        $data['tax_amount']       = $this->cart->taxTotal();
        $data['notify_url']       = route('shop.klarna.push');
        $data['confirmation_url'] = config('shopr.gateways.klarna_checkout.confirmation_url');
        $data['return_url']       = config('shopr.gateways.klarna_checkout.checkout_url');
        $data['terms_url']        = config('shopr.gateways.klarna_checkout.terms_url');
        $data['validation_url']   = config('app.url');

        $data['items'] = [];

        foreach ($this->cart->items() as $item) {
            $data['items'][] = [
                'type'             => 'physical',
                'name'             => $item->shoppable->getTitle(),
                'quantity'         => $item->quantity,
                'tax_rate'         => $taxRate,
                'price'            => $item->price,
                'total_tax_amount' => $item->total() * $taxRate / (100 + $taxRate)
            ];
        }

        $response = $gateway->authorize($data)->send()->getData();

        if (empty($response['order_id']) || empty($response['html_snippet'])) {
            // Throw exception.
        }

        $order->transaction_id = $response['order_id'];
        $order->save();

        return $response;
    }

    /**
     * The namespace of the payment gateway used to initialize it.
     *
     * @return string
     */
    public function getGatewayPath()
    {
        return '\MyOnlineStore\Omnipay\KlarnaCheckout\Gateway';
    }

    /**
     * Require the order ID and that the cart isn't empty to proceed to the order confirmation.
     *
     * @param  string $token
     * @return boolean
     */
    public function allowConfirmationPage($token)
    {
        return (!empty($token) || $this->cart->isEmpty());
    }

    /**
     * Retrieves the order from Klarna, creates a matching order in our database and fires the created-event.
     *
     * @param  string $token The order ID stored in Klarna's system.
     * @return Happypixels\Shopr\Models\Order|false
     */
    public function getConfirmedOrder($token)
    {
        $response = $this->gateway->fetchTransaction(['transactionReference' => $token])->send()->getData();
        
        if (empty($response['management'])) {
            return false;
        }

        $response = $response['management'];
        $order = $this->cart->convertToOrder($this->getProviderName(), [
            'email' => $response['shipping_address']['email'],
            'phone' => $response['shipping_address']['phone'],
            'first_name' => $response['shipping_address']['given_name'],
            'last_name' => $response['shipping_address']['family_name'],
            'address' => $response['shipping_address']['street_address'],
            'zipcode' => $response['shipping_address']['postal_code'],
            'city' => $response['shipping_address']['city'],
            'country' => $response['shipping_address']['country'],
        ]);

        if (!$order) {
            return false;
        }

        $order->transaction_reference = $response['klarna_reference'];
        $order->transaction_id = $token;
        $order->payment_status = ($response['remaining_authorized_amount'] > 0) ? 'pending' : 'paid';
        $order->save();

        Event::fire('shopr.orders.created', $order);

        return $order;
    }
}
