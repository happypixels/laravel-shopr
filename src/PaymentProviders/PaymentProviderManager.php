<?php

namespace Happypixels\Shopr\PaymentProviders;

use Illuminate\Http\Request;

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
        $provider = 'Happypixels\\Shopr\\PaymentProviders\\'.$request->gateway;

        return (new $provider)->initialize()->handleRequest($request);
    }
}
