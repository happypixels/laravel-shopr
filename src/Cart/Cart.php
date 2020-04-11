<?php

namespace Happypixels\Shopr\Cart;

use Happypixels\Shopr\Contracts\Shoppable;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Money\Formatter;
use Illuminate\Support\Collection;

abstract class Cart
{
    /**
     * Retrieve all cart items from the store.
     *
     * @return array|null
     */
    abstract public function get();

    /**
     * Persists the cart items in the store.
     *
     * @return void
     */
    abstract public function persist($data);

    /**
     * Returns all items regardless of type.
     *
     * @return Collection
     */
    public function getAllItems(): Collection
    {
        return collect($this->get());
    }

    /**
     * Returns the regular cart items.
     *
     * @return Collection
     */
    public function items(): Collection
    {
        return $this->getAllItems()->filter(function ($item) {
            return $item->shoppable->isDiscount() === false;
        })->values();
    }

    /**
     * Returns the discount coupons added to the cart.
     *
     * @return Collection
     */
    public function discounts(): Collection
    {
        return $this->getAllItems()->filter(function ($item) {
            return $item->shoppable->isDiscount() === true;
        })->values();
    }

    /**
     * Returns only the relative discount coupons added to the cart.
     *
     * @return Collection
     */
    public function relativeDiscounts(): Collection
    {
        return $this->discounts()->filter(function ($discount) {
            return ! $discount->shoppable->is_fixed;
        });
    }

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
        $formatter = app(Formatter::class);

        return [
            'items' => $this->items(),
            'discounts' => $this->discounts(),
            'sub_total' => $subTotal,
            'sub_total_formatted' => $formatter->format($subTotal),
            'tax_total' => $taxTotal,
            'tax_total_formatted' => $formatter->format($taxTotal),
            'total' => $total,
            'total_formatted' => $formatter->format($total),
            'count' => $this->count(),
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

        if (! $tax || $tax <= 0) {
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

        foreach ($this->getAllItems() as $item) {
            // This includes the sub items.
            $total += $item->total();
        }

        return $total;
    }

    /**
     * Returns the total amount of the items in the cart, discounts excluded.
     *
     * @return float
     */
    public function totalWithoutDiscounts()
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
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * Returns the total count of the items added to the cart.
     *
     * @return int
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
        if (! $coupon->isDiscount()) {
            return false;
        }

        $item = $this->addItem(get_class($coupon), $coupon->id, 1, [], [], $coupon->getPrice());

        $coupon->increment('uses');

        event('shopr.cart.discounts.added', $item);

        return $item;
    }

    /**
     * Iterates all the current items in the cart and returns true if one of them is
     * a discount coupon matching the given code. If no code is provided, it will return false on any
     * discount coupon.
     *
     * @param  string  $code
     * @return bool
     */
    public function hasDiscount($code = null): bool
    {
        foreach ($this->discounts() as $item) {
            if (! $code) {
                return true;
            } elseif ($item->shoppable->getTitle() === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts the current cart to an order and clears the cart.
     *
     * @param  string $gateway
     * @param  array  $data
     * @return \Happypixels\Shopr\Models\Order|false
     */
    public function convertToOrder($gateway, $data = [])
    {
        if ($this->isEmpty()) {
            return false;
        }

        $order = app(Order::class)->create([
            'user_id' => auth()->id(),
            'payment_gateway' => $gateway,
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null,
            'payment_status' => $data['payment_status'] ?? 'pending',
            'delivery_status' => 'pending',
            'token' => Order::generateToken(),
            'total' => $this->total(),
            'sub_total' => $this->subTotal(),
            'tax' => $this->taxTotal(),
            'email' => optional($data)['email'],
            'phone' => optional($data)['phone'],
            'first_name' => optional($data)['first_name'],
            'last_name' => optional($data)['last_name'],
            'address' => optional($data)['address'],
            'zipcode' => optional($data)['zipcode'],
            'city' => optional($data)['city'],
            'country' => optional($data)['country'],
        ]);

        foreach ($this->getAllItems() as $item) {
            $parent = $order->items()->create([
                'shoppable_type' => get_class($item->shoppable),
                'shoppable_id'   => $item->shoppable->id,
                'quantity'       => $item->quantity,
                'title'          => $item->shoppable->getTitle(),
                'price'          => $item->price,
                'options'        => $item->options,
            ]);

            if ($item->subItems->count() > 0) {
                foreach ($item->subItems as $subItem) {
                    $parent->children()->create([
                        'order_id'       => $order->id,
                        'shoppable_type' => get_class($subItem->shoppable),
                        'shoppable_id'   => $subItem->shoppable->id,
                        'title'          => $subItem->shoppable->getTitle(),
                        'price'          => $subItem->price,
                        'options'        => $subItem->options,
                    ]);
                }
            }
        }

        event('shopr.orders.created', $order);

        return $order;
    }

    /**
     * Adds an item to the cart.
     *
     * @param string $shoppableType
     * @param int $shoppableId
     * @param int $quantity
     * @param array $options
     * @param array $subItems
     * @param float|null $price
     * @return Happypixels\Shopr\Cart\CartItem
     */
    public function addItem($shoppableType, $shoppableId, $quantity = 1, $options = [], $subItems = [], $price = null): CartItem
    {
        $quantity = (is_numeric($quantity) && $quantity > 0) ? $quantity : 1;

        $items = $this->getAllItems();
        $item = new CartItem($shoppableType, $shoppableId, $quantity, $options, $subItems, $price);

        // Find already added items that are identical to current selection.
        $identicals = $items->filter(function ($row) use ($item) {
            return
                $row->shoppableType === $item->shoppableType &&
                $row->shoppableId === $item->shoppableId &&
                serialize($row->options) === serialize($item->options) &&
                serialize($row->subItems) === serialize($item->subItems);
        });

        // If an identical item already exists in the cart, add to it's quantity.
        // Otherwise, push it.
        if ($identicals->count() > 0) {
            $items->where('id', $identicals->first()->id)->first()->quantity += $quantity;
            $item->quantity = $items->where('id', $identicals->first()->id)->first()->quantity;

            $event = 'updated';
        } else {
            $items->push($item);

            $event = 'added';
        }

        $this->persist($items);

        event('shopr.cart.items.'.$event, $item);

        return $item;
    }

    /**
     * Updates a single item in the cart.
     *
     * @param  string $id
     * @param  array $data
     * @return Happypixels\Shopr\Cart\CartItem
     */
    public function updateItem($id, $data)
    {
        $items = $this->getAllItems();
        $item = null;

        foreach ($items as $index => $item) {
            if ($item->id !== $id || empty($data['quantity'])) {
                continue;
            }

            $items[$index]->quantity = intval($data['quantity']);

            if (! empty($items[$index]->subItems)) {
                foreach ($items[$index]->subItems as $i => $subItem) {
                    $items[$index]->subItems[$i]->quantity = intval($data['quantity']);
                    $items[$index]->subItems[$i]->total = $items[$index]->subItems[$i]->total();
                }
            }

            $items[$index]->total = $items[$index]->total();
            $item = $items[$index];
        }

        $this->persist($items);

        // Refresh relative discount values.
        foreach ($items as $index => $item) {
            if (! $item->shoppable->isDiscount()) {
                continue;
            }

            $items[$index]->refreshDiscountValue();
        }

        $this->persist($items);

        event('shopr.cart.items.updated', $item);

        return $item;
    }

    /**
     * Removes a single item from the cart.
     *
     * @param  string $id
     * @return Happypixels\Shopr\Cart\CartItem
     */
    public function removeItem($id)
    {
        $items = $this->getAllItems();
        $removedItem = null;

        foreach ($items as $index => $item) {
            if ($item->id === $id) {
                $removedItem = $items[$index];

                unset($items[$index]);
            }
        }

        $this->persist($items);

        // If the cart is cleared of shoppable items, also remove any discounts.
        if ($this->items()->count() === 0) {
            $this->clear();
        }

        if ($removedItem) {
            event('shopr.cart.items.deleted', $removedItem);
        }

        return $removedItem;
    }

    /**
     * Clears the cart and fires appropriate event.
     *
     * @return void
     */
    public function clear()
    {
        $this->persist(collect([]));

        event('shopr.cart.cleared');
    }
}
