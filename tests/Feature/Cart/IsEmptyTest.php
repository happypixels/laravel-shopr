<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class IsEmptyTest extends TestCase
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
