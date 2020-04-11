<?php

namespace Happypixels\Shopr\Controllers\Web;

use Happypixels\Shopr\Exceptions\PaymentFailedException;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentConfirmationController extends Controller
{
    /**
     * Attempts to confirm a payment. Returns an error view if unsuccessful and reidrects to
     * the order confirmation view otherwise.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $request->validate(['gateway' => 'required']);

        try {
            $response = PaymentProviderManager::make($request)->confirmPayment();
        } catch (PaymentFailedException $e) {
            optional(
                Order::where('transaction_reference', $request->payment_intent)->first()
            )->update(['payment_status' => 'failed']);

            return view('shopr::payments.error')->with('message', $e->getMessage());
        }

        $order = Order::where('transaction_reference', $request->payment_intent)->firstOrFail();

        $previousStatus = $order->payment_status;

        $order->update([
            'payment_status' => 'paid',
            'transaction_reference' => $response['transaction_reference'],
        ]);

        // If the previous status of the order is not 'paid', fire the event to indicate
        // the order has now been confirmed.
        if ($previousStatus !== 'paid') {
            event('shopr.orders.confirmed', $order);
        }

        return redirect()->route('shopr.order-confirmation', ['token' => $order->token]);
    }
}
