<?php

namespace Happypixels\Shopr;

use Happypixels\Shopr\Money\Formatter;

class CartItem
{
    public $id;

    public $quantity;

    public $shoppableType;

    public $shoppableId;

    public $shoppable;

    public $options;

    public $subItems;

    public $total;

    public $price;

    public function __construct($shoppableType, $shoppableId, $quantity, $options, $subItems, $price = null)
    {
        $this->id              = uniqid(time());
        $this->shoppableType   = $shoppableType;
        $this->shoppableId     = $shoppableId;
        $this->shoppable       = (new $shoppableType)::findOrFail($shoppableId);
        $this->quantity        = $quantity;
        $this->options         = $options;
        $this->subItems        = $this->addSubItems($subItems);
        $this->price           = ($price) ?? $this->shoppable->getPrice();
        $this->price_formatted = (new Formatter)->format($this->price);
        $this->total           = $this->total();
    }

    private function addSubItems($subItems = [])
    {
        $items = collect([]);

        if (empty($subItems)) {
            return $items;
        }

        foreach ($subItems as $item) {
            $options = (!empty($item['options'])) ? $item['options'] : [];
            $price   = (!empty($item['price']) && is_numeric($item['price'])) ? $item['price'] : null;

            $items->push(new CartSubItem(
                $item['shoppable_type'],
                $item['shoppable_id'],
                $this->quantity,
                $options,
                $price
            ));
        }

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
}
