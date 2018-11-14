<?php

namespace Happypixels\Shopr\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Helpers\Tax;

abstract class BaseCart implements Cart
{
    public function subTotal()
    {
        return Tax::getSubTotal($this->total());
    }

    public function taxTotal()
    {
        return Tax::getTax($this->total());
    }

    public function total()
    {
        $total = 0;

        foreach ($this->items() as $item) {
            // This includes the sub items.
            $total += $item->total();
        }

        return $total;
    }

    public function isEmpty()
    {
        return $this->count() === 0;
    }

    public function count()
    {
        return $this->items()->sum('quantity');
    }
}
