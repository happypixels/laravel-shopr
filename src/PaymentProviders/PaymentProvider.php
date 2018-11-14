<?php

namespace Happypixels\Shopr\PaymentProviders;

use Happypixels\Shopr\Contracts\Cart;
use Illuminate\Http\Request;
use Omnipay\Omnipay;

class PaymentProvider
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
        return snake_case($this->getProviderName());
    }
}
