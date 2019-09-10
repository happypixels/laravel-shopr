<?php

namespace Happypixels\Shopr\PaymentProviders;

use Omnipay\Omnipay;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Happypixels\Shopr\Cart\Cart;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Exceptions\PaymentFailedException;

abstract class PaymentProvider
{
    protected $gateway;
    protected $config;
    protected $cart;
    protected $input;

    public function __construct()
    {
        $this->config = config('shopr.gateways.'.$this->getConfigKey());
        $this->cart = app(Cart::class);
    }

    /**
     * The gateway-specific finalization of the payment. Makes the purchase through Omnipay.
     *
     * @return ResponseInterface
     */
    abstract public function purchase();

    /**
     * Makes the purchase and returns the results if successful. Throws exception if unsuccessful.
     *
     * @return array
     */
    public function payForCart()
    {
        $response = $this->purchase();

        if ($response->isRedirect()) {
            $data = [
                'success' => false,
                'transaction_reference' => $response->getPaymentIntentReference(),
                'redirect' => $response->getRedirectUrl(),
                'payment_status' => 'pending',
            ];

            return $data;
        }

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
        $this->gateway = Omnipay::create($this->getGatewayPath());
        $this->gateway->initialize($this->config);

        return $this;
    }

    /**
     * Makes the input data available throughout the checkout flow.
     *
     * @param  Request $request
     * @return Happypixels\Shopr\PaymentProviders\PaymentProvider
     */
    public function handleRequest(Request $request)
    {
        $this->input = $request->all();

        return $this;
    }

    /**
     * Returns the name of the called provider.
     *
     * @return string
     */
    public function getProviderName()
    {
        return basename(str_replace('\\', '/', get_called_class()));
    }

    /**
     * Returns the name or namespace used to initialize the gateway.
     * Defaults to the provider name.
     *
     * @return string
     */
    public function getGatewayPath()
    {
        return $this->getProviderName();
    }

    /**
     * Returns the snake case version of the provider name.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return Str::snake($this->getProviderName());
    }

    /**
     * Returns the order identified by token from the database.
     *
     * @param  string $token
     * @return Order
     */
    public function getOrderFromDatabase($token)
    {
        return Order::where('token', $token)->first();
    }
}
