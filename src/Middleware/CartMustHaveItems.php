<?php

namespace Happypixels\Shopr\Middleware;

use Closure;
use Happypixels\Shopr\Contracts\Cart;

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
        $cart = app(Cart::class);

        if ($cart->isEmpty()) {
            return redirect('/');
        }

        return $next($request);
    }
}
