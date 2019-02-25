<?php

namespace Happypixels\Shopr\PaymentProviders;

use Illuminate\Http\Request;
use Happypixels\Shopr\Exceptions\InvalidGatewayException;

class PaymentProviderManager
{
    /**
     * Initializes and returns the expected payment provider class.
     *
     * @param  Request $request
     * @return PaymentProvider
     */
    public static function make(Request $request)
    {
        $provider = 'Happypixels\\Shopr\\PaymentProviders\\'.ucfirst($request->gateway);

        if (! class_exists($provider)) {
            throw new InvalidGatewayException;
        }

        return app($provider)->initialize()->handleRequest($request);
    }
}
