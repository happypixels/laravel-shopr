<?php

namespace Happypixels\Shopr\Contracts;

use Happypixels\Shopr\CartItem;
use Illuminate\Support\Collection;

interface Cart
{
    public function summary();

    public function items() : Collection;

    public function subTotal();

    public function taxTotal();

    public function total();

    public function addItem($shoppableType, $shoppableId, $quantity = 1, $options = [], $subItems = [], $price = null) : CartItem;

    public function addDiscount(Shoppable $coupon);

    public function updateItem($id, $data);

    public function removeItem($id);

    public function clear();

    public function isEmpty();

    public function count();

    public function convertToOrder($gateway, $userData = []);

    public function hasDiscount($code = null) : bool;
}
