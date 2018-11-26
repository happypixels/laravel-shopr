<?php

namespace Happypixels\Shopr\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Contracts\Shoppable;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Money\Formatter;
use Illuminate\Support\Facades\Event;

abstract class BaseCart implements Cart
{
    /**
     * Returns the full cart summary.
     *
     * @return array
     */
    public function summary()
    {
        $subTotal = $this->subTotal();
        $taxTotal = $this->taxTotal();
        $total = $this->total();
        $formatter = new Formatter;

        return [
            'items'               => $this->items(),
            'sub_total'           => $subTotal,
            'sub_total_formatted' => $formatter->format($subTotal),
            'tax_total'           => $taxTotal,
            'tax_total_formatted' => $formatter->format($taxTotal),
            'total'               => $total,
            'total_formatted'     => $formatter->format($total),
            'count'               => $this->count()
        ];
    }

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
        return $this->items()->filter(function ($row) {
            return $row->shoppable->isDiscount() === false;
        })->sum('quantity');
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

        $item = $this->addItem(get_class($coupon), $coupon->id, 1, [], [], $coupon->getPrice());

        $coupon->increment('uses');

        Event::fire('shopr.cart.discounts.added', $item);

        return $item;
    }

    /**
     * Iterates all the current items in the cart and returns true if one of them is
     * a discount coupon matching the given code. If no code is provided, it will return false on any
     * discount coupon.
     *
     * @param  string  $code
     * @return boolean
     */
    public function hasDiscount($code = null) : bool
    {
        $items = $this->items();

        foreach ($items as $item) {
            $shoppable = $item->shoppable;

            if (!$code && $shoppable->isDiscount()) {
                return true;
            } elseif ($shoppable->getTitle() === $code && $shoppable->isDiscount()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts the current cart to an order and clears the cart.
     *
     * @param  string $gateway
     * @param  array  $userData
     * @return \Happypixels\Shopr\Models\Order|false
     */
    public function convertToOrder($gateway, $userData = [])
    {
        if ($this->isEmpty()) {
            return false;
        }

        $order = Order::create([
            'user_id'          => auth()->id(),
            'payment_gateway'  => $gateway,
            'payment_status'   => 'pending',
            'delivery_status'  => 'pending',
            'token'            => Order::generateToken(),
            'total'            => $this->total(),
            'sub_total'        => $this->subTotal(),
            'tax'              => $this->taxTotal(),
            'email'            => optional($userData)['email'],
            'phone'            => optional($userData)['phone'],
            'first_name'       => optional($userData)['first_name'],
            'last_name'        => optional($userData)['last_name'],
            'address'          => optional($userData)['address'],
            'zipcode'          => optional($userData)['zipcode'],
            'city'             => optional($userData)['city'],
            'country'          => optional($userData)['country'],
        ]);

        foreach ($this->items() as $item) {
            $parent = $order->items()->create([
                'shoppable_type' => get_class($item->shoppable),
                'shoppable_id'   => $item->shoppable->id,
                'quantity'       => $item->quantity,
                'title'          => $item->shoppable->getTitle(),
                'price'          => $item->price,
                'options'        => $item->options
            ]);

            if ($item->subItems->count() > 0) {
                foreach ($item->subItems as $subItem) {
                    $parent->children()->create([
                        'order_id'       => $order->id,
                        'shoppable_type' => get_class($subItem->shoppable),
                        'shoppable_id'   => $subItem->shoppable->id,
                        'title'          => $subItem->shoppable->getTitle(),
                        'price'          => $subItem->price,
                        'options'        => $subItem->options
                    ]);
                }
            }
        }

        $this->clear();

        return $order;
    }
}
