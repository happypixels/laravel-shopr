<?php

namespace Happypixels\Shopr\Contracts;

interface Shoppable
{
    public function getId();

    public function getTitle();

    public function getPrice($input = []);
}
