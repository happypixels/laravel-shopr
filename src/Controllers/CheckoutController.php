<?php

namespace Happypixels\Shopr\Controllers;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;
use Happypixels\Shopr\Rules\CartNotEmpty;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;

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
     * @param  Request $request
     * @return mixed
     */
    public function charge(Request $request)
    {
        $request->validate([
            'gateway' => 'required',
            'email' => 'required|email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cart' => [new CartNotEmpty]
        ]);

        // Create the order.
        $order = $this->cart->convertToOrder($request->gateway, $request->only([
            'email', 'phone', 'first_name', 'last_name', 'address', 'zipcode', 'city', 'country'
        ]));

        if (!$order) {
            return response()->json(['message' => 'Unable to process your order.'], 400);
        }

        // Attempt to make the purchase.
        try {
            $provider = PaymentProviderManager::make($request);
            $response = $provider->charge($order);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unable to process your payment.'], 400);
        }

        // Handle the response.
        if ($response->isRedirect()) {
            // Redirect to offsite confirmation.
            $response->redirect();
        } elseif ($response->isSuccessful()) {
            $order->transaction_reference         = $response->getTransactionReference();
            $order->transaction_id                = $response->getTransactionId();
            $order->payment_status                = 'paid';
            $order->save();

            Event::fire('shopr.orders.created', $order);

            return response()->json([
                'redirect' => config('shopr.confirmation_url') . '?token=' . $order->token.'&gateway='.$order->payment_gateway
            ], 200);
        } else {
            return response()->json(['response' => $response->getMessage()], 400);
        }
    }
}
