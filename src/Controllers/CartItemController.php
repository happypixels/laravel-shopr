<?php

namespace Happypixels\Shopr\Controllers;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Rules\Discounts\NotADiscount;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CartItemController extends Controller
{
    use ValidatesRequests;

    /**
     * Adds an item to the cart. Returns the full cart summary.
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

        Cart::add($shoppable, [
            'quantity' => $request->get('quantity', 1),
            'options' => $request->get('options', null),
            'sub_items' => $subItems,
            'price' => $request->get('price', null),
        ]);

        return Cart::get();
    }

    /**
     * Updates a cart item. Returns the full cart summary.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        Cart::update($id, $request->all());

        return Cart::get();
    }

    /**
     * Removes an item from the cart and returns the full cart summary.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Cart::delete($id);

        return Cart::get();
    }
}
