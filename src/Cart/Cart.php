<?php

namespace Happypixels\Shopr\Cart;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Contracts\Shoppable;
use Happypixels\Shopr\Contracts\CartDriver;
use Happypixels\Shopr\Exceptions\CartItemNotFoundException;

class Cart
{
    /**
     * The driver used for persisting the cart data.
     *
     * @var CartDriver
     */
    public $driver;

    /**
     * Create a cart instance.
     *
     * @param CartDriver $driver
     */
    public function __construct(CartDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Adds an item to the cart. If it already exists the quantity is incremented instead.
     * Returns the added CartItem.
     *
     * @param Shoppable $shoppable
     * @param array $data
     * @return CartItem
     */
    public function add(Shoppable $shoppable, $data = [])
    {
        $event = null;
        $items = $this->all();

        // Initialize the new item.
        $item = (new CartItem($shoppable))
            ->setQuantity($data['quantity'] ?? 1)
            ->setOptions($data['options'] ?? null)
            ->setSubItems($data['sub_items'] ?? null)
            ->setPrice($data['price'] ?? null);

        // Update the quantity of any existing identical items instead of adding a new item.
        foreach ($items as $currentItem) {
            if ($currentItem->is($item)) {
                $item = $currentItem
                    ->setQuantity($currentItem->quantity + $item->quantity)
                    ->setPrice();

                $event = 'updated';

                break;
            }
        }

        // If no identical items are found, push the new one to the list of items.
        if (! $event) {
            $items->push($item);

            $event = 'added';
        }

        $this->driver->store($items);

        $this->refreshRelativeDiscountValues();

        Event::fire('shopr.cart.items.'.$event, $item);

        return $item;
    }

    /**
     * Updates an item in the cart. Returns the updated item.
     *
     * @param CartItem|string $item
     * @param array $data
     * @return CartItem
     */
    public function update($item, $data)
    {
        if (is_string($item)) {
            $item = $this->findOrFail($item);
        }

        foreach ($items = $this->all() as $currentItem) {
            if ($currentItem->is($item)) {
                $item = $currentItem
                    ->setQuantity($data['quantity'])
                    ->setPrice($currentItem->price);

                break;
            }
        }

        $this->driver->store($items);

        $this->refreshRelativeDiscountValues();

        event('shopr.cart.items.updated', $item);

        return $item;
    }

    public function addDiscount(Shoppable $coupon)
    {
        if (! $coupon->isDiscount()) {
            return false;
        }

        $item = $this->add($coupon);

        $coupon->increment('uses');

        event('shopr.cart.discounts.added', $item);

        return $item;
    }

    /**
     * Returns all items regardless of type.
     *
     * @return Collection
     */
    public function all() : Collection
    {
        return collect($this->driver->get());
    }

    /**
     * Returns the regular cart items.
     *
     * @return Collection
     */
    public function get() : Collection
    {
        return $this->all()->filter(function ($item) {
            return $item->shoppable->shouldBeIncludedInItemList();
        })->values();
    }

    /**
     * Alias for the "get" method.
     *
     * @return Collection
     */
    public function items()
    {
        return $this->get();
    }

    /**
     * Returns the total amount of the items in the cart.
     *
     * @return float
     */
    public function total()
    {
        return $this->all()->sum(function ($item) {
            return $item->total_price;
        });
    }

    /**
     * Returns the total amount of the items in the cart, discounts excluded.
     *
     * @return float
     */
    public function totalWithoutDiscounts()
    {
        return $this->items()->sum(function ($item) {
            return $item->total_price;
        });
    }

    /**
     * Finds a single item in the cart.
     *
     * @param string $id
     * @return CartItem|null
     */
    public function find($id)
    {
        return $this->all()->filter(function ($item) use ($id) {
            return $item->id === $id;
        })->first();
    }

    /**
     * Returns the cart item with the given ID. Throws exception if not found.
     *
     * @param string $id
     * @return CartItem|null
     * @throws CartItemNotFoundException
     */
    public function findOrFail($id)
    {
        $item = $this->find($id);

        if (! $item) {
            throw new CartItemNotFoundException;
        }

        return $item;
    }

    /**
     * Returns the first "real" item from the cart.
     *
     * @return CartItem|null
     */
    public function first()
    {
        return $this->get()->first();
    }

    /**
     * Returns the last "real" item from the cart.
     *
     * @return CartItem|null
     */
    public function last()
    {
        return $this->get()->last();
    }

    /**
     * Returns the total count of the items added to the cart.
     *
     * @return int
     */
    public function count() : int
    {
        return $this->get()->sum(function ($item) {
            return $item->quantity;
        });
    }

    /**
     * Refreshes the discount values that aren't fixed. Called when adding
     * or removing items from the cart.
     *
     * @return void
     */
    protected function refreshRelativeDiscountValues()
    {
        // Refresh relative discount values.
        foreach ($items = $this->all() as $index => $currentItem) {
            if (! $currentItem->shoppable->isDiscount()) {
                continue;
            }

            $items[$index]->setPrice();
        }

        $this->driver->store($items);
    }
}
