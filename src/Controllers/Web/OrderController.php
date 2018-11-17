<?php

namespace Happypixels\Shopr\Controllers\Web;

use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    /**
     * Confirms a placed order and stores it in the database.
     * Applicable when the provider creates an unconfirmed order before we can create it in our database.
     *
     * @param  Request $request
     * @return Response
     */
    public function confirm(Request $request)
    {
        $provider = PaymentProviderManager::make($request);

        // If the order exists locally, we use it.
        $order = $provider->getOrderFromDatabase($request->query('token'));

        // If the order doesn't exist locally, we get it from the provider and create it locally.
        if (!$order) {
            $order = $provider->storeConfirmedProviderOrder($request->query('token'));
        }

        // If no order is found, we redirect to home page.
        if (!$order) {
            return redirect('/');
        }

        return view(config('shopr.templates.order-confirmation'))->with('order', $order);
    }
}
