<?php

namespace Happypixels\Shopr\Contracts;

use Happypixels\Shopr\Cart\CartItemFactory;

interface Cart
{
    public function get() : array;

    public function count() : int;

    public function clear();

    // public function find(string $id) : self;

    public function add(Shoppable $shoppable) : CartItemFactory;

    // public function update(array $data);

    // public function delete(string $id);
}
