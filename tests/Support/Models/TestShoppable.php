<?php

namespace Happypixels\Shopr\Tests\Support\Models;

use Happypixels\Shopr\Models\Shoppable;

class TestShoppable extends Shoppable
{
    protected $fillable = ['title', 'price'];
}
