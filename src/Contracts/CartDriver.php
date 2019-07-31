<?php

namespace Happypixels\Shopr\Contracts;

interface CartDriver
{
    public function get();

    #public function find(string $id);

    public function persist($data);
}
