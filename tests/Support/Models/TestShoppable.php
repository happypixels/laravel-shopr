<?php

namespace Happypixels\Shopr\Tests\Support\Models;

use Happypixels\Shopr\Models\Shoppable;

class TestShoppable extends Shoppable
{
    protected $fillable = ['title', 'price'];

    /**
     * The price of the model.
     *
     * @return mixed
     */
    public function getPrice()
    {
        return 500;
    }
}
