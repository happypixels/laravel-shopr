<?php

namespace Happypixels\Shopr\PaymentProviders;

use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Traits\PaymentProviders\HandlesProviderOrders;
use Illuminate\Support\Facades\Event;

class KlarnaCheckout extends PaymentProvider
{
    use HandlesProviderOrders;

    /**
     * Returns the order data from Klarna.
     *
     * @param  string $token
     * @return array
     */
    public function getProviderOrder($token)
    {
        return $this->gateway->fetchTransaction(['transactionReference' => $token])->send()->getData();
    }

    /**
     * Acknowledges an order in Klarnas system.
     *
     * @param  string $token
     * @return boolean
     */
    public function acknowledgeOrder($token)
    {
        return $this->gateway->acknowledge(['transactionReference' => $token])->send()->isSuccessful();
    }

    /**
     * Captures the given amount for the order matching the given token.
     *
     * @param  string $token
     * @param  float $amount
     * @return boolean
     */
    public function captureAmount($token, $amount)
    {
        return $this->gateway->capture(['transactionReference' => $token, 'amount' => $amount])->send()->isSuccessful();
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
        $data['locale']           = $this->config['store_locale']; //en-us, en-gb, sv-se
        $data['purchase_country'] = $this->config['store_country']; //gb, us, se
        $data['tax_amount']       = $this->cart->taxTotal();
        $data['notify_url']       = config('app.url').'/api/shopr/webhooks/kco/push?token={checkout.order.id}';
        $data['validation_url']   = config('app.url').'/api/shopr/webhooks/kco/validate';
        $data['confirmation_url'] = $this->getConfirmationUrl().'?token={checkout.order.id}&gateway=KlarnaCheckout';
        $data['return_url']       = $this->getCheckoutUrl().'?token={checkout.order.id}&gateway=KlarnaCheckout';
        $data['terms_url']        = $this->config['terms_url'];

        $data['items'] = [];

        foreach ($this->cart->items() as $item) {
            $data['items'][] = [
                'type' => 'physical',
                'name' => $item->shoppable->getTitle(),
                'quantity' => $item->quantity,
                'tax_rate' => $taxRate,
                'price' => $item->price,
                'total_tax_amount' => $item->total() * $taxRate / (100 + $taxRate),
                'merchant_data' => json_encode([
                    'shoppable_id' => $item->shoppableId,
                    'shoppable_type' => $item->shoppableType,
                    'options' => $item->options
                ])
            ];

            if ($item->subItems->count() > 0) {
                foreach ($item->subItems as $subItem) {
                    $data['items'][] = [
                        'type' => 'physical',
                        'name' => $subItem->shoppable->getTitle(),
                        'quantity' => $subItem->quantity,
                        'tax_rate' => $taxRate,
                        'price' => $subItem->price,
                        'total_tax_amount' => $subItem->total() * $taxRate / (100 + $taxRate),
                        'merchant_data' => json_encode([
                            'parent' => $item->id,
                            'shoppable_id' => $subItem->shoppableId,
                            'shoppable_type' => $subItem->shoppableType,
                            'options' => $subItem->options
                        ])
                    ];
                }
            }
        }

        $response = $this->gateway->authorize($data)->send()->getData();

        if (empty($response['order_id']) || empty($response['html_snippet'])) {
            throw new \Exception('Unable to process the order.', 400);
        }

        return $response;
    }

    /**
     * Retrieves the confirmed order from Klarna, creates a matching order in our database and fires the created-event.
     *
     * @param  string $identifier The order ID stored in Klarna's system.
     * @return Happypixels\Shopr\Models\Order|false
     */
    public function storeConfirmedProviderOrder($identifier)
    {
        $response = $this->getProviderOrder($identifier);
        
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

    /**
     * Determines the selected payment method. All methods except invoice means direct payment.
     * Full list of available payment methods can be found here:
     * https://developers.klarna.com/api/#order-management-api__orderinitial_payment_method__type
     *
     * @param  array $orderResponse
     * @return string
     */
    public function determinePaymentStatus($orderResponse)
    {
        if ($orderResponse['checkout']['status'] !== 'checkout_complete') {
            return 'pending';
        }

        $paymentMethod = strtolower($orderResponse['management']['initial_payment_method']['type']);

        if ($paymentMethod === 'invoice') {
            return 'billed';
        }

        return 'paid';
    }

    /**
     * Returns the checkout url.
     *
     * @return string
     */
    protected function getCheckoutUrl()
    {
        return $this->config['checkout_url'] ?? route('shopr.checkout');
    }

    /**
     * Returns the confirmation url.
     *
     * @return string
     */
    protected function getConfirmationUrl()
    {
        return $this->config['confirmation_url'] ?? route('shopr.order-confirmation');
    }
}
