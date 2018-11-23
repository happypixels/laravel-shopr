<?php

namespace Happypixels\Shopr\Controllers;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Rules\Discounts\NotADiscount;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
            'shoppable_type' => ['required', new NotADiscount],
            'shoppable_id' => 'required',
        ]);

        $item = $this->cart->addItem(
            $request->shoppable_type,
            $request->shoppable_id,
            $request->get('quantity', 1),
            $request->get('options', []),
            $request->get('sub_items', []),
            $request->get('price', null)
        );

        return $this->cart->summary();
    }

    public function update(Request $request, $id)
    {
        $this->cart->updateItem($id, $request->all());

        return $this->cart->summary();
    }

    public function destroy($id)
    {
        $this->cart->removeItem($id);

        return $this->cart->summary();
    }
}
