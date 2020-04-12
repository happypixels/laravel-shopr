<?php

namespace Happypixels\Shopr\PaymentProviders;

use Happypixels\Shopr\Exceptions\InvalidGatewayException;

class PaymentProviderManager
{
    /**
     * Initializes and returns the expected payment provider class.
     *
     * @param  string $provider
     * @param  array $data
     * @return PaymentProvider
     */
    public static function make($provider, $data)
    {
        $provider = 'Happypixels\\Shopr\\PaymentProviders\\'.ucfirst($provider);

        if (! class_exists($provider)) {
            throw new InvalidGatewayException;
        }

        return app($provider)->initialize()->handleRequest($data);
    }
}
