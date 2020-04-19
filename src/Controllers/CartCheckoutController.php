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

        $response = Cart::checkout($request->gateway, $request->all());

        return $response->isSuccessful()
            ? response()->json($response, 201)
            : response()->json($response, 200);
    }
}
