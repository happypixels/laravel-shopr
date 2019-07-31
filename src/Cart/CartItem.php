<?php

namespace Happypixels\Shopr\Cart;

use Illuminate\Support\Collection;
use Happypixels\Shopr\Money\Formatter;
use Happypixels\Shopr\Contracts\Shoppable;

class CartItem
{
    public $id;

    public $quantity = 1;

    //public $shoppableType;

    //public $shoppableId;

    public $shoppable;

    public $options;

    public $subItems;

    public $total;

    public $price;

    public $isSubItem = false;

    public function __construct(Shoppable $shoppable)
    {
        $this->id = uniqid(time());
        $this->shoppable = $shoppable;
        $this->subItems = collect();
    }

    // public function __construct($shoppableType, $shoppableId, $quantity, $options, $subItems, $price = null)
    // {
    //     $this->id = uniqid(time());
    //     $this->shoppableType = $shoppableType;
    //     $this->shoppableId = $shoppableId;
    //     $this->shoppable = (new $shoppableType)::findOrFail($shoppableId);
    //     $this->quantity = $quantity;
    //     $this->options = $options;
    //     $this->subItems = $this->addSubItems($subItems);
    //     $this->price = ($price) ?? $this->shoppable->getPrice();
    //     $this->price_formatted = app(Formatter::class)->format($this->price);
    //     $this->total = $this->total();
    // }

    public function asSubItem()
    {
        $this->id = null;
        $this->isSubItem = true;

        return $this;
    }

    public function addSubItems(Collection $subItems)
    {
        $items = collect();

        if ($subItems->count() === 0) {
            return $items;
        }

        foreach ($subItems as $item) {
            $options = (! empty($item['options'])) ? $item['options'] : [];
            $price = (! empty($item['price']) && is_numeric($item['price'])) ? $item['price'] : $item['shoppable']->getPrice();
            $item = (new self($item['shoppable']))->asSubItem();

            $item->quantity = $this->quantity;
            $item->price = $price;

            if ($options) {
                $item->options = $options;
            }

            $items->push($item);
        }

        $this->subItems = $items;

        $this->refreshPrice();

        return $items;
    }

    public function total()
    {
        $total = 0;

        $total += $this->quantity * $this->price;

        if ($this->subItems->count()) {
            foreach ($this->subItems as $subItem) {
                $total += $subItem->total();
            }
        }

        return $total;
    }

    public function refreshDiscountValue()
    {
        if (! $this->shoppable->isDiscount() || $this->shoppable->is_fixed) {
            return;
        }

        $value = $this->shoppable->getPrice();

        $this->price = $value;
        $this->price_formatted = app(Formatter::class)->format($value);
        $this->total = $this->total();
    }

    public function refreshPrice()
    {
        $this->price = ($this->price !== null) ? $this->price : $this->shoppable->getPrice();
        $this->price_formatted = app(Formatter::class)->format($this->price);
        $this->total = $this->total();
    }

    public function isIdenticalTo(self $item)
    {
        return $this->shoppable->is($item->shoppable) &&
            serialize($this->options) === serialize($item->options) &&
            serialize($this->subItems) === serialize($item->subItems);
    }
}
