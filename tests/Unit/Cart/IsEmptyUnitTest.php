<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class IsEmptyUnitTest extends TestCase
{
    /** @test */
    public function returns_true_if_empty()
    {
        $this->assertTrue(Cart::isEmpty());
    }

    /** @test */
    public function returns_false_if_not_empty()
    {
        Cart::add(TestShoppable::first());

        $this->assertFalse(Cart::isEmpty());
    }
}
