<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class ClearCartUnitTest extends TestCase
{
    /** @test */
    public function it_clears_the_cart()
    {
        Cart::add(TestShoppable::first())->save();

        $this->assertFalse(Cart::isEmpty());

        Cart::clear();

        $this->assertTrue(Cart::isEmpty());
    }

    /** @test */
    public function it_fires_event()
    {
        Event::fake();

        Cart::clear();

        Event::assertDispatched('shopr.cart.cleared');
    }
}
