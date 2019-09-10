<?php

namespace Happypixels\Shopr\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Exceptions\PaymentFailedException;
use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;

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
            return view('shopr::payments.error')->with('message', $e->getMessage());
        }

        $order = Order::where('transaction_reference', $request->payment_intent)->firstOrFail();

        $order->update([
            'payment_status' => 'paid',
            'transaction_reference' => $response['transaction_reference'],
        ]);

        return redirect()->route('shopr.order-confirmation', ['token' => $order->token]);
    }
}
