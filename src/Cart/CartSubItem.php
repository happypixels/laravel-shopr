<?php

namespace Happypixels\Shopr\Cart;

use Happypixels\Shopr\Money\Formatter;

class CartSubItem
{
    public $shoppableType;

    public $shoppableId;

    public $shoppable;

    public $quantity;

    public $options;

    public $total;

    public $price;

    public function __construct($shoppableType, $shoppableId, $quantity, $options = [], $price = null)
    {
        $this->shoppableType = $shoppableType;
        $this->shoppableId = $shoppableId;
        $this->shoppable = (new $shoppableType)::findOrFail($shoppableId);
        $this->quantity = $quantity;
        $this->options = $options;
        $this->price = (is_numeric($price)) ? $price : $this->shoppable->getPrice();
        $this->price_formatted = app(Formatter::class)->format($this->price);
        $this->total = $this->total();
    }

    public function total()
    {
        return $this->quantity * $this->price;
    }
}
