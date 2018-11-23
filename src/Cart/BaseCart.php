<?php

namespace Happypixels\Shopr\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Contracts\Shoppable;
use Illuminate\Support\Facades\Event;

abstract class BaseCart implements Cart
{
    /**
     * Returns the sub total of the cart.
     *
     * @return float
     */
    public function subTotal()
    {
        return $this->total() - $this->taxTotal();
    }

    /**
     * Returns the total tax of the items in the cart.
     *
     * @return float
     */
    public function taxTotal()
    {
        $tax = config('shopr.tax');

        if (!$tax || $tax <= 0) {
            return 0;
        }

        return $this->total() * $tax / (100 + $tax);
    }

    /**
     * Returns the total amount of the items in the cart.
     *
     * @return float
     */
    public function total()
    {
        $total = 0;

        foreach ($this->items() as $item) {
            // This includes the sub items.
            $total += $item->total();
        }

        return $total;
    }

    /**
     * Returns true if the cart is empty, false if not.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * Returns the total count of the items added to the cart.
     *
     * @return integer
     */
    public function count()
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Calculates the total value of the coupon and adds it to the cart.
     *
     * @param  Shoppable $coupon
     * @return CartItem|false
     */
    public function addDiscount(Shoppable $coupon)
    {
        if (!$coupon->isDiscount()) {
            return false;
        }

        if ($coupon->is_fixed) {
            $amount = -$coupon->value;
        } else {
            $percentage = $coupon->value / 100;
            $amount = -($this->total() * $percentage);
        }

        $item = $this->addItem(get_class($coupon), $coupon->id, 1, [], [], $amount);

        Event::fire('shopr.cart.discounts.added', $item);

        return $item;
    }

    /**
     * Iterates all the current items in the cart and returns true if one of them is
     * a discount coupon matching the given code.
     *
     * @param  string  $code
     * @return boolean
     */
    public function hasDiscount($code) : bool
    {
        $items = $this->items();

        foreach ($items as $item) {
            if (
                $item->shoppable->isDiscount() &&
                $item->shoppable->getTitle() === $code
            ) {
                return true;
            }
        }

        return false;
    }
}
