<?php

namespace Happypixels\Shopr\Contracts;

interface CartDriver
{
    public function get();

    public function store($data);
}
