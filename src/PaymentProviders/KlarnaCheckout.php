<?php

namespace Happypixels\Shopr\PaymentProviders;

use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Traits\PaymentProviders\HandlesProviderOrders;
use Illuminate\Support\Facades\Event;

class KlarnaCheckout extends PaymentProvider
{
    use HandlesProviderOrders;

    /**
     * Creates an incomplete order in Klarnas system.
     * The response has an html snippet which is used to finish the checkout.
     *
     * @return array
     */
    public function createProviderOrder()
    {
        $taxRate = config('shopr.tax');

        $data['amount']           = $this->cart->total();
        $data['currency']         = config('shopr.currency');
        $data['locale']           = 'sv-se'; //en-us, en-gb
        $data['purchase_country'] = 'se'; //gb, us
        $data['tax_amount']       = $this->cart->taxTotal();
        $data['notify_url']       = $this->config['push_url'];
        $data['confirmation_url'] = $this->config['confirmation_url'];
        $data['return_url']       = $this->config['checkout_url'];
        $data['terms_url']        = $this->config['terms_url'];
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

        $response = $this->gateway->authorize($data)->send()->getData();

        if (empty($response['order_id']) || empty($response['html_snippet'])) {
            throw new \Exception('Unable to process the order.', 400);
        }

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
     * Require the order reference (token) to proceed to the order confirmation.
     *
     * @param  string $token
     * @return boolean
     */
    public function allowConfirmationPage($token)
    {
        return (!empty($token));
    }

    /**
     * Returns the order identified by token from the database.
     *
     * @param  string $token
     * @return Order
     */
    public function getOrderFromDatabase($token)
    {
        return Order::where('transaction_id', $token)->first();
    }

    /**
     * Retrieves the confirmed order from Klarna, creates a matching order in our database and fires the created-event.
     *
     * @param  string $identifier The order ID stored in Klarna's system.
     * @return Happypixels\Shopr\Models\Order|false
     */
    public function storeConfirmedProviderOrder($identifier)
    {
        $response = $this->gateway->fetchTransaction(['transactionReference' => $identifier])->send()->getData();
        
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
        $order->transaction_id = $identifier;
        $order->payment_status = ($response['remaining_authorized_amount'] > 0) ? 'pending' : 'paid';
        $order->save();

        Event::fire('shopr.orders.created', $order);

        return $order;
    }
}
