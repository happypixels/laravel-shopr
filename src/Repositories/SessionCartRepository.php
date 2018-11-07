<?php

namespace Happypixels\Shopr\Repositories;

use Happypixels\Shopr\CartItem;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Helpers\SessionHelper;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Money\Formatter;
use Illuminate\Support\Collection;

class SessionCartRepository implements Cart
{
    private $cartKey = 'shopr.cart';
    private $session;

    public function __construct(SessionHelper $session)
    {
        $this->session = $session;
    }

    public function summary()
    {
        $subTotal = $this->subTotal();
        $taxTotal = $this->taxTotal();
        $total    = $this->total();

        return [
            'items'               => $this->items(),
            'sub_total'           => $subTotal,
            'sub_total_formatted' => (new Formatter)->format($subTotal),
            'tax_total'           => $taxTotal,
            'tax_total_formatted' => (new Formatter)->format($taxTotal),
            'total'               => $total,
            'total_formatted'     => (new Formatter)->format($total),
            'count'               => $this->count()
        ];
    }

    public function items() : Collection
    {
        return $this->session->get($this->cartKey) ?: collect([]);
    }

    public function subTotal()
    {
        return $this->total() - $this->taxTotal();
    }

    public function taxTotal()
    {
        return ($this->total() * config('shopr.tax')) / 100;
    }

    public function total()
    {
        $total = 0;

        foreach ($this->items() as $item) {
            // This includes the sub items.
            $total += $item->total;
        }

        return $total;
    }

    public function addItem($shoppableType, $shoppableId, $quantity = 1, $options = [], $subItems = [], $price = null) : CartItem
    {
        $quantity = (is_numeric($quantity) && $quantity > 0) ? $quantity : 1;

        $items = $this->items();
        $item  = new CartItem($shoppableType, $shoppableId, $quantity, $options, $subItems, $price);

        // Find already added items that are identical to current selection.
        $identicals = $items->filter(function ($row) use ($item) {
            return (
                $row->shoppableType === $item->shoppableType &&
                $row->shoppableId === $item->shoppableId &&
                serialize($row->options) === serialize($item->options) &&
                serialize($row->subItems) === serialize($item->subItems)
            );
        });

        // If an identical item already exists in the cart, add to it's quantity. Otherwise, push it.
        if ($identicals->count() > 0) {
            $items->where('id', $identicals->first()->id)->first()->quantity += $quantity;
        } else {
            $items->push($item);
        }

        $this->session->put($this->cartKey, $items);

        return $item;
    }

    public function updateItem($id, $data)
    {
        $items = $this->items();
        $item  = null;

        foreach ($items as $index => $item) {
            if ($item->id !== $id || empty($data['quantity'])) {
                continue;
            }

            $items[$index]->quantity = intval($data['quantity']);

            if (!empty($items[$index]->subItems)) {
                foreach ($items[$index]->subItems as $i => $subItem) {
                    $items[$index]->subItems[$i]->quantity = intval($data['quantity']);
                    $items[$index]->subItems[$i]->total    = $items[$index]->subItems[$i]->total();
                }
            }

            $items[$index]->total = $items[$index]->total();
            $item                 = $items[$index];
        }

        $this->session->put($this->cartKey, $items);

        return $item;
    }

    public function removeItem($id)
    {
        $items = $this->items();
        $item  = null;

        foreach ($items as $index => $item) {
            if ($item->id === $id) {
                $item = $items[$index];

                unset($items[$index]);
            }
        }

        $this->session->put($this->cartKey, $items);

        return $item;
    }

    public function clear()
    {
        $this->session->put($this->cartKey, collect([]));
    }

    public function isEmpty()
    {
        return $this->count() === 0;
    }

    public function count()
    {
        return $this->items()->sum('quantity');
    }

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
                'title'          => $item->shoppable->title,
                'price'          => $item->price,
                'options'        => $item->options
            ]);

            if ($item->subItems->count() > 0) {
                foreach ($item->subItems as $subItem) {
                    $parent->children()->create([
                        'order_id'       => $order->id,
                        'shoppable_type' => get_class($subItem->shoppable),
                        'shoppable_id'   => $subItem->shoppable->id,
                        'title'          => $subItem->shoppable->title,
                        'price'          => $subItem->price,
                        'options'        => $subItem->options
                    ]);
                }
            }
        }

        $this->clear();

        return $order;
    }

    /**
     * Calculates the total value of the coupon and adds it to the cart.
     *
     * @param  DiscountCoupon $coupon
     * @return CartItem
     */
    public function applyDiscountCoupon(DiscountCoupon $coupon)
    {
        if ($coupon->is_fixed) {
            $amount = -$coupon->value;
        } else {
            $percentage = $coupon->value / 100;
            $amount     = -($this->total() * $percentage);
        }

        return $this->addItem(
            'Happypixels\Shopr\Models\DiscountCoupon',
            $coupon->id,
            1,
            [],
            [],
            $amount
        );
    }

    /**
     * Iterates all the current items in the cart and returns true if one of them is
     * a discount coupon matching the given code.
     *
     * @param  string  $code
     * @return boolean
     */
    public function hasDiscountCoupon($code)
    {
        $items = $this->items();

        foreach ($items as $item) {
            if (
                $item->shoppableType === 'Happypixels\Shopr\Models\DiscountCoupon' &&
                $item->shoppable->getTitle() === $code
            ) {
                return true;
            }
        }

        return false;
    }
}
