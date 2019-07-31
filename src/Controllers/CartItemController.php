<?php

namespace Happypixels\Shopr\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Rules\Discounts\NotADiscount;
use Illuminate\Foundation\Validation\ValidatesRequests;

class CartItemController extends Controller
{
    use ValidatesRequests;

    /**
     * Stores an item in the cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'shoppable_type' => ['required', new NotADiscount],
            'shoppable_id' => 'required',
        ]);

        $shoppable = $request->shoppable_type::findOrFail($request->shoppable_id);

        $subItems = collect($request->get('sub_items', []))->map(function ($subItem) {
            $subItem['shoppable'] = $subItem['shoppable_type']::findOrFail($subItem['shoppable_id']);

            return $subItem;
        })->toArray();

        Cart::add($shoppable)
            ->quantity($request->get('quantity', 1))
            ->options($request->get('options', []))
            ->subItems($subItems)
            ->overridePrice($request->get('price', null))
            ->save();

        return Cart::get();
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
