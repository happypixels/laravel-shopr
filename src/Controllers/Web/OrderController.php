<?php

namespace Happypixels\Shopr\Controllers\Web;

use Illuminate\Routing\Controller;
use Happypixels\Shopr\Models\Order;

class OrderController extends Controller
{
    /**
     * Display the order confirmation.
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmation()
    {
        $order = app(Order::class)
            ->with('items')
            ->where('token', request('token'))
            ->where('payment_status', 'paid')
            ->firstOrFail();

        return view(config('shopr.templates.order-confirmation'))->with('order', $order);
    }
}
