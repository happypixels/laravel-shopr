<?php

namespace Happypixels\Shopr\PaymentProviders;

use Happypixels\Shopr\Exceptions\PaymentFailedException;
use Happypixels\Shopr\Models\Order;
use Illuminate\Support\Str;
use Omnipay\Omnipay;

abstract class PaymentProvider
{
    protected $gateway;
    protected $config;
    protected $input;

    public function __construct()
    {
        $this->config = config('shopr.gateways.'.$this->getConfigKey());
    }

    /**
     * The gateway-specific finalization of the payment. Makes the purchase through Omnipay.
     *
     * @return \Omnipay\Common\Message\ResponseInterface
     */
    abstract public function purchase();

    /**
     * The data used for confirming a payment, used for example when confirming a payment using SCA.
     * The payment reference should be found in the $this->input-array.
     *
     * @return array
     */
    abstract public function getPaymentConfirmationData(): array;

    /**
     * Makes the purchase and returns a checkoutresponse. Throws exception if unsuccessful.
     * This method wraps up the results of the purchase method, which is provider specific.
     *
     * @return CheckoutResponse
     */
    public function payForCart()
    {
        $response = $this->purchase();

        if ($response->isRedirect()) {
            return new RedirectCheckoutResponse(
                $response->getPaymentIntentReference(),
                $response->getRedirectUrl()
            );
        }

        if ($response->isSuccessful()) {
            return new SuccessfulCheckoutResponse($response->getTransactionReference());
        }

        throw new PaymentFailedException($response->getMessage());
    }

    /**
     * Confirms a payment if needed.
     *
     * @return SuccessfulCheckoutResponse
     */
    public function confirmPayment()
    {
        $response = $this->gateway->confirm($this->getPaymentConfirmationData())->send();

        if (! $response->isSuccessful()) {
            throw new PaymentFailedException($response->getMessage());
        }

        return new SuccessfulCheckoutResponse($response->getTransactionReference());
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
     * @param  array $data
     * @return Happypixels\Shopr\PaymentProviders\PaymentProvider
     */
    public function handleRequest($data)
    {
        $this->input = $data;

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
