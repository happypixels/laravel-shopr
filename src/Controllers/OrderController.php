<?php

namespace Happypixels\Shopr\Controllers;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\PaymentProviders\PaymentProviderManager;
use Happypixels\Shopr\Rules\CartNotEmpty;
use Happypixels\Shopr\Rules\GatewayCreatesProviderOrders;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Creates an order in the third party provider system.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'gateway' => ['required', 'string', new GatewayCreatesProviderOrders],
            'cart' => [new CartNotEmpty]
        ]);

        try {
            $provider = PaymentProviderManager::make($request);

            return $provider->createProviderOrder();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'We were unable to process your order.',
                'exception' => json_encode($e)
            ], 400);
        }
    }

    /**
     * Confirms a placed order and stores it in the database.
     * Applicable when the provider creates an unconfirmed order before we can create it in our database.
     *
     * @param  Request $request
     * @return Response
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'gateway' => ['required', 'string', new GatewayCreatesProviderOrders],
            'token' => 'required|string'
        ]);

        $provider = PaymentProviderManager::make($request);

        // If the order exists locally, we use it.
        $order = $provider->getOrderFromDatabase($request->token);

        // If the order doesn't exist locally, we get it from the provider and create it locally.
        if (!$order) {
            $order = $provider->storeConfirmedProviderOrder($request->token);
        }

        // If no order is found, we redirect to home page.
        if (!$order) {
            return response()->json('The order could not be found.', 404);
        }

        return response()->json(['order' => $order]);
    }
}
