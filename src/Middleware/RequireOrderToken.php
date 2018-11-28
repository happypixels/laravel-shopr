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
        $token = $request->query('token');

        if (! $token || Order::where('token', $token)->where('payment_status', 'paid')->count() === 0) {
            return redirect('/');
        }

        return $next($request);
    }
}
