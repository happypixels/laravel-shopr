<?php

namespace Happypixels\Shopr\Controllers;

use Illuminate\Routing\Controller;
use Happypixels\Shopr\Contracts\Cart;
use Illuminate\Http\Request;
use Omnipay\Omnipay;
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
     * @return mixed
     */
    public function charge(Request $request)
    {
        $request->validate([
            'gateway'    => 'required',
            'email'      => 'required|email',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255'
        ]);

        if ($this->cart->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 400);
        }

        // Create the order.
        $order = $this->cart->convertToOrder($request->gateway, $request->only([
            'email', 'phone', 'first_name', 'last_name', 'address', 'zipcode', 'city', 'country'
        ]));

        if (!$order) {
            return response()->json(['message' => 'Unable to process your order.'], 400);
        }

        // Make the purchase.
        $gateway = Omnipay::create($request->gateway);
        $gateway->initialize(config('shopr.gateways.' . str_slug($request->gateway, '_')));
        $response = $gateway->purchase(['amount' => $order->total, 'currency' => 'sek', 'token' => $request->token])->send();

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

            return response()->json(['redirect' => route('shopr.order-confirmation') . '?token=' . $order->token], 200);
        } else {
            return response()->json(['response' => $response->getMessage()], 400);
        }
    }
}
