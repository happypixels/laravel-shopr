<?php

namespace Happypixels\Shopr\Contracts;

interface Cart
{
    public function get() : array;

    public function count() : int;

    public function clear();

    // public function find(string $id) : self;

    public function add(Shoppable $shoppable);

    public function quantity($quantity);

    // public function withOptions(array $options) : self;

    // public function withSubItems(array $subItems) : self;

    // public function overridePrice($price) : self;

    public function save();

    // public function update(array $data);

    // public function delete(string $id);
}
