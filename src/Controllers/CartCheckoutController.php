<?php

namespace Happypixels\Shopr\Controllers;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Models\Order;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CartCheckoutController extends Controller
{
    use ValidatesRequests;

    /**
     * Makes the payment and converts the cart into an order if everything goes well.
     *
     * @param  Request $request
     * @return Response
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'gateway' => 'required',
            'email' => 'required|email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        $checkoutResponse = Cart::checkout($request->gateway, $request->all());

        // The response can either be an array or an order. If an array, that means the payment requires additional
        // confirmation from a third party (bank). In those cases the response will include a redirect url.
        // If the response is an order, that means the payment was successful and that it only needs to be confirmed.
        if (is_array($checkoutResponse)) {
            return response()->json($checkoutResponse);
        } elseif ($checkoutResponse instanceof Order) {
            $response = ['token' => $checkoutResponse->token];

            if (config('shopr.templates.order-confirmation')) {
                $response['redirect'] = route('shopr.order-confirmation', ['token' => $checkoutResponse->token]);
            }

            return response()->json($response, 201);
        }
    }
}
