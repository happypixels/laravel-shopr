<?php

namespace Happypixels\Shopr\Middleware;

use Closure;
use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;

class RequireOrderToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!$request->query('token') || !$request->query('gateway')) {
            return redirect('/');
        }

        $provider = PaymentProviderManager::make($request);

        if (!$provider->allowConfirmationPage($request->query('token'))) {
            return redirect('/');
        }
                
        return $next($request);
    }
}
