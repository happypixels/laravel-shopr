<?php

namespace Happypixels\Shopr\Middleware;

use Closure;
use Happypixels\Shopr\Models\Order;

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
        if (! $this->hasValidOrderToken($request)) {
            return redirect('/');
        }

        return $next($request);
    }

    /**
     * Returns true if the request has a token matching a paid order.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function hasValidOrderToken($request)
    {
        $token = $request->query('token');

        return $token && Order::where('token', $token)->where('payment_status', 'paid')->count() > 0;
    }
}
