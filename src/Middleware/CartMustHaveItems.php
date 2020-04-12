<?php

namespace Happypixels\Shopr\Middleware;

use Closure;
use Happypixels\Shopr\Facades\Cart;

class CartMustHaveItems
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
        if (Cart::isEmpty()) {
            return redirect('/');
        }

        return $next($request);
    }
}
