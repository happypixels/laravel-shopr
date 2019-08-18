<?php

namespace Happypixels\Shopr\Cart;

use Happypixels\Shopr\Money\Formatter;
use Happypixels\Shopr\Contracts\Shoppable;
use Illuminate\Contracts\Support\Arrayable;

class CartItem implements Arrayable
{
    /**
     * The data of the cart item.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create an instance of the cart item.
     *
     * @param Shoppable $shoppable
     */
    public function __construct(Shoppable $shoppable)
    {
        $this->data = [
            'id' => uniqid(time()),
            'shoppable' => $shoppable,
        ];

        $this->setQuantity();
        $this->setOptions();
        $this->setSubItems();
        $this->setPrice();
    }

    /**
     * Returns an attribute on the data property.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Get the item's array representation.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Sets the quantity of the item. Defaults to 1.
     *
     * @param int $quantity
     * @return self
     */
    public function setQuantity($quantity = 1)
    {
        $this->data['quantity'] = $quantity;

        return $this;
    }

    /**
     * Sets the options of the item.
     *
     * @param array|null $options
     * @return self
     */
    public function setOptions($options = null)
    {
        $this->data['options'] = $options;

        return $this;
    }

    /**
     * Sets the price of the cart item. Also calculates total price and formats the values.
     *
     * @param float|null $priceOverride
     * @return self
     */
    public function setPrice($priceOverride = null)
    {
        $this->data['price'] = $priceOverride ?: $this->shoppable->getPrice();
        $this->data['price_formatted'] = app(Formatter::class)->format($this->price);

        $this->refreshTotalPrice();

        return $this;
    }

    /**
     * Adds sub items to the item.
     *
     * @param array|null $subItems
     * @return self
     */
    public function setSubItems($subItems = null)
    {
        $this->sub_items = collect();

        collect($subItems)->filter(function ($subItem) {
            return $subItem['shoppable'] instanceof Shoppable;
        })->each(function ($subItem) {
            $this->sub_items->push(
                (new self($subItem['shoppable']))
                    ->asSubItem()
                    ->setQuantity($this->quantity)
                    ->setOptions($subItem['options'] ?? null)
                    ->setPrice($subItem['price'] ?? null)
            );
        });

        return $this;
    }

    /**
     * Checks if the current item is identical to the given item.
     *
     * @param CartItem $item
     * @return bool
     */
    public function is(self $item)
    {
        return $this->shoppable->is($item->shoppable) &&
            serialize($this->options) === serialize($item->options) &&
            serialize($this->sub_items) === serialize($item->sub_items);
    }

    /**
     * Marks that this item is a sub item.
     *
     * @return self
     */
    protected function asSubItem()
    {
        $this->data['id'] = null;

        return $this;
    }

    /**
     * Calculates the total price of the item and adds it to the data property.
     *
     * @return void
     */
    protected function refreshTotalPrice()
    {
        $this->data['total_price'] = $this->quantity * $this->price;

        $this->sub_items->each(function ($subItem) {
            $this->data['total_price'] += $subItem->price * $subItem->quantity;
        });

        $this->data['total_price_formatted'] = app(Formatter::class)->format($this->data['total_price']);
    }
}
