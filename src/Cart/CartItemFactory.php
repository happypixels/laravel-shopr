<?php

namespace Happypixels\Shopr\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Contracts\Shoppable;

class CartItemFactory
{
    protected $item;
    protected $cart;

    public function __construct(Shoppable $shoppable, Cart $cart)
    {
        $this->item = new CartItem($shoppable);
        $this->cart = $cart;
    }

    public function quantity($quantity)
    {
        $this->item->quantity = $quantity;

        return $this;
    }

    public function options(array $options)
    {
        $this->item->options = $options;

        return $this;
    }

    public function subItems(array $subItems)
    {
        // Only add the items that have a valid shoppable.
        $this->item->addSubItems(collect($subItems)->filter(function ($subItem) {
            return $subItem['shoppable'] instanceof Shoppable;
        }));

        return $this;
    }

    public function overridePrice($price)
    {
        $this->item->price = $price;

        return $this;
    }

    public function save()
    {
        if (! $this->item) {
            return;
        }

        $items = $this->cart->getAllItems();

        // Find already added items that are identical to current selection.
        $identicals = $items->filter(function (CartItem $row) {
            return $row->isIdenticalTo($this->item);
        });

        // If an identical item already exists in the cart, add to it's quantity.
        // Otherwise, push it.
        if ($identicals->count() > 0) {
            $item = $items->where('id', $identicals->first()->id)->first();
            $item->quantity += $this->item->quantity;

            $event = 'updated';
        } else {
            $items->push($item = $this->item);

            $event = 'added';
        }

        $item->refreshPrice();

        $this->cart->driver->persist($items);

        Event::fire('shopr.cart.items.'.$event, $item);

        $this->item = null;

        return $item;
    }
}
