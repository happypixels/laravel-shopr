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
            return response()->json('We were unable to process your order.', 400);
        }
    }
}
