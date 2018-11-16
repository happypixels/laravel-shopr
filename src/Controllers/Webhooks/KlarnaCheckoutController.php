<?php

namespace Happypixels\Shopr\Controllers\Webhooks;

use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class KlarnaCheckoutController extends Controller
{
    /**
     * The push endpoint used to acknowledge and confirm an order. It determines whether the customer has paid or been billed,
     * updates the order in the database accordingly and makes the calls to Klarna to confirm and finalize the order.
     *
     * See https://developers.klarna.com/api/#checkout-api__create-a-new-ordermerchant_urls__push for more info.
     *
     * @param  Request $request
     * @return Response
     */
    public function push(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        // Get the order.
        $request->gateway = 'KlarnaCheckout';
        $provider = PaymentProviderManager::make($request);

        // Determine the payment status.
        $paymentStatus = $provider->determinePaymentStatus(
            $provider->getProviderOrder($request->token)
        );

        // Make sure the checkout is actually completed.
        if ($paymentStatus === 'pending') {
            return response()->json('Checkout incomplete.', 400);
        }

        $order = $provider->getOrderFromDatabase($request->token);

        if (!$order) {
            return response()->json('The order could not be found.', 404);
        }

        // Update the database order.
        $order->payment_status = $paymentStatus;
        $order->save();

        // Acknowledge the order in Klarnas system.
        $provider->acknowledgeOrder($request->token);

        // If the order was paid, capture the full amount to finalize the payment.
        if ($order->payment_status === 'paid') {
            $provider->captureAmount($request->token, $order->total);
        }

        return response()->json(null, 200);
    }

    /**
     * The Klarna validation. Currently not supported, allow all.
     *
     * @param  Request $request
     * @return Response
     */
    public function validate(Request $request)
    {
        return response()->json(null, 200);
    }
}
