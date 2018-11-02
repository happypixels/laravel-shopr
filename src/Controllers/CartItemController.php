<?php

namespace Happypixels\Shopr\Controllers;

use Happypixels\Shopr\Contracts\Cart;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;

class CartItemController extends Controller
{
    use ValidatesRequests;

    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'shoppable_type' => 'required',
            'shoppable_id'   => 'required'
        ]);

        $item = $this->cart->addItem(
            $request->shoppable_type,
            $request->shoppable_id,
            $request->get('quantity', 1),
            $request->get('options', []),
            $request->get('sub_items', []),
            $request->get('price', null)
        );

        Event::fire('shopr.cart.items.added', $item);

        return $this->cart->summary();
    }

    public function update(Request $request, $id)
    {
        $item = $this->cart->updateItem($id, $request->all());

        Event::fire('shopr.cart.items.updated', $item);

        return $this->cart->summary();
    }

    public function destroy($id)
    {
        $item = $this->cart->removeItem($id);

        Event::fire('shopr.cart.items.deleted', $item);

        return $this->cart->summary();
    }
}
