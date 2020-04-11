<?php

namespace Happypixels\Shopr\Controllers;

use Happypixels\Shopr\Cart\Cart;
use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CheckoutController extends Controller
{
    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Charges the order sum through the desired payment gateway.
     *
     * @return mixed
     */
    public function charge(Request $request)
    {
        $request->validate([
            'gateway' => 'required',
            'email' => 'required|email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        if ($this->cart->isEmpty()) {
            return response()->json(['message' => trans('shopr::cart.cart_is_empty')], 400);
        }

        $response = PaymentProviderManager::make($request)->payForCart();

        // Make the payment and merge the response with the request data, if successful.
        $data = array_merge($request->only([
            'email', 'phone', 'first_name', 'last_name', 'address', 'zipcode', 'city', 'country',
        ]), $response);

        $order = $this->cart->convertToOrder($request->gateway, $data);

        if (! $response['success']) {
            return response()->json($response);
        }

        $response = ['token' => $order->token];

        if (config('shopr.templates.order-confirmation')) {
            $response['redirect'] = route('shopr.order-confirmation', ['token' => $order->token]);
        }

        return response()->json($response, 201);
    }
}
