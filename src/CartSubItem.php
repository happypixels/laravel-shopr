<?php

namespace Happypixels\Shopr;

class CartSubItem
{
    public $shoppableType;

    public $shoppableId;

    public $shoppable;

    public $quantity;

    public $options;

    public $total;

    public function __construct($shoppableType, $shoppableId, $quantity, $options = [])
    {
        $this->shoppableType = $shoppableType;
        $this->shoppableId   = $shoppableId;
        $this->shoppable     = (new $shoppableType)::findOrFail($shoppableId);
        $this->quantity      = $quantity;
        $this->options       = $options;
        $this->total         = $this->total();
    }

    private function total()
    {
        return $this->quantity * $this->shoppable->price;
    }
}
