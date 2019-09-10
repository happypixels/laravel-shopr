<?php

namespace Happypixels\Shopr\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Exceptions\PaymentFailedException;
use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;

class PaymentConfirmationController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['gateway' => 'required']);

        try {
            $response = PaymentProviderManager::make($request)->confirm($request->payment_intent);
        } catch (PaymentFailedException $e) {
            return view('shopr::payments.error')->with('message', $e->getMessage());
        }

        info('Confirmation response', $response);

        $order = Order::where('transaction_reference', $request->payment_intent)->firstOrFail();

        $order->update([
            'payment_status' => 'paid',
            'transaction_reference' => $response['transaction_reference'],
        ]);

        // Return redirectable response.
        return redirect()->route('shopr.order-confirmation', [
            'token' => $order->token,
        ]);
    }
}
