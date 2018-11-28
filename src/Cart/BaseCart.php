<?php

namespace Happypixels\Shopr\Cart;

use Happypixels\Shopr\Contracts\Cart;

abstract class BaseCart implements Cart
{
    public function subTotal()
    {
        return $this->total() - $this->taxTotal();
    }

    public function taxTotal()
    {
        $tax = config('shopr.tax');

        if (! $tax || $tax <= 0) {
            return 0;
        }

        return $this->total() * $tax / (100 + $tax);
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
