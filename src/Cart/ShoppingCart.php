<?php

namespace Happypixels\Shopr\Cart;

use Happypixels\Shopr\Contracts\CartDriver;
use Happypixels\Shopr\Contracts\Shoppable;
use Happypixels\Shopr\Exceptions\CartItemNotFoundException;
use Happypixels\Shopr\Exceptions\DiscountValidationException;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Money\Formatter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class ShoppingCart implements Arrayable
{
    /**
     * The driver used for persisting the cart data.
     *
     * @var CartDriver
     */
    public $driver;

    /**
     * The money formatter.
     *
     * @var Formatter
     */
    protected $moneyFormatter;

    /**
     * Create a cart instance.
     *
     * @param CartDriver $driver
     */
    public function __construct(CartDriver $driver, Formatter $moneyFormatter)
    {
        $this->driver = $driver;
        $this->moneyFormatter = $moneyFormatter;
    }

    /**
     * Returns the array representation of the cart.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->get();
    }

    /**
     * Returns the regular cart items.
     *
     * @return array
     */
    public function get()
    {
        $subTotal = $this->subTotal();
        $taxTotal = $this->taxTotal();
        $total = $this->total();

        return [
            'items' => $this->items(),
            'discounts' => $this->discounts(),
            'sub_total' => $subTotal,
            'sub_total_formatted' => $this->moneyFormatter->format($subTotal),
            'tax_total' => $taxTotal,
            'tax_total_formatted' => $this->moneyFormatter->format($taxTotal),
            'total' => $total,
            'total_formatted' => $this->moneyFormatter->format($total),
            'count' => $this->count(),
        ];
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

        event('shopr.cart.items.'.$event, $item);

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

    /**
     * Validates the configurated rules and adds the discount coupon if they all pass.
     *
     * @param string|DiscountCoupon $coupon
     * @return false|CartItem
     */
    public function addDiscount($coupon)
    {
        if (is_string($coupon)) {
            $coupon = DiscountCoupon::where('code', $coupon)->firstOrFail();
        }

        // Validate the configurated rules.
        collect(config('shopr.discount_coupons.validation_rules'))->each(function ($rule) use ($coupon) {
            $rule = new $rule;

            throw_if(
                ! $rule->passes('code', $coupon->code),
                new DiscountValidationException($rule->message())
            );
        });

        if (is_string($coupon)) {
            $coupon = DiscountCoupon::where('code', $coupon)->firstOrFail();
        }

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
    public function items() : Collection
    {
        return $this->all()->filter(function ($item) {
            return $item->shoppable->shouldBeIncludedInItemList();
        })->values();
    }

    /**
     * Returns the discount coupons added to the cart.
     *
     * @return Collection
     */
    public function discounts() : Collection
    {
        return $this->all()->filter(function ($item) {
            return $item->shoppable->isDiscount();
        })->values();
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
        throw_unless($item = $this->find($id), new CartItemNotFoundException);

        return $item;
    }

    /**
     * Returns the first "real" item from the cart.
     *
     * @return CartItem|null
     */
    public function first()
    {
        return $this->items()->first();
    }

    /**
     * Returns the last "real" item from the cart.
     *
     * @return CartItem|null
     */
    public function last()
    {
        return $this->items()->last();
    }

    /**
     * Returns the total count of the items added to the cart.
     *
     * @return int
     */
    public function count() : int
    {
        return $this->items()->sum(function ($item) {
            return $item->quantity;
        });
    }

    /**
     * Returns true if the cart is empty.
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->count() === 0;
    }

    /**
     * Clears the cart and fires appropriate event.
     *
     * @return void
     */
    public function clear()
    {
        $this->driver->store(collect());

        event('shopr.cart.cleared');
    }

    /**
     * Removes an item from the cart. Returns true if successful and false otherwise.
     *
     * @param CartItem|string $id
     * @return bool
     */
    public function delete($id)
    {
        if ($id instanceof CartItem) {
            $id = $id->id;
        }

        if (! $this->find($id)) {
            return false;
        }

        foreach ($items = $this->all() as $index => $item) {
            if ($item->id === $id) {
                $removedItem = $items[$index];

                unset($items[$index]);
            }
        }

        $this->driver->store($items);

        // If the cart is cleared of shoppable items, also remove any discounts.
        if ($this->items()->count() === 0) {
            $this->clear();
        } else {
            $this->refreshRelativeDiscountValues();
        }

        if ($removedItem) {
            event('shopr.cart.items.deleted', $removedItem);

            return true;
        }

        return false;
    }

    /**
     * Iterates all the current items in the cart and returns true if one of them is
     * a discount coupon matching the given code.
     * If no code is provided, it will return true on any discount coupon.
     *
     * @param  DiscountCoupon|string  $discount
     * @return bool
     */
    public function hasDiscount($discount = null)
    {
        if ($discount instanceof DiscountCoupon) {
            $discount = $discount->code;
        }

        return $this->discounts()->filter(function ($item) use ($discount) {
            return ! $discount || $item->shoppable->getTitle() === $discount;
        })->count() > 0;
    }

    /**
     * Converts the current cart to an order and clears the cart.
     *
     * @param  string $gateway
     * @param  array  $data
     * @return \Happypixels\Shopr\Models\Order|false
     */
    public function checkoutUsing($gateway, $data = [])
    {
        if ($this->isEmpty()) {
            return false;
        }

        // Move all of the payment logics to here.

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

        foreach ($this->all() as $item) {
            $parent = $order->items()->create([
                'shoppable_type' => get_class($item->shoppable),
                'shoppable_id' => $item->shoppable->id,
                'quantity' => $item->quantity,
                'title' => $item->shoppable->getTitle(),
                'price' => $item->price,
                'options' => $item->options,
            ]);

            if ($item->subItems->count() > 0) {
                foreach ($item->subItems as $subItem) {
                    $parent->children()->create([
                        'order_id' => $order->id,
                        'shoppable_type' => get_class($subItem->shoppable),
                        'shoppable_id' => $subItem->shoppable->id,
                        'title' => $subItem->shoppable->getTitle(),
                        'price' => $subItem->price,
                        'options' => $subItem->options,
                    ]);
                }
            }
        }

        $this->clear();

        return $order;
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
