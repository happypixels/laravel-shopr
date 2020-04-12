<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ClearCartTest extends TestCase
{
    /** @test */
    public function it_clears_the_cart()
    {
        Cart::add(TestShoppable::first());

        $this->assertFalse(Cart::isEmpty());

        Cart::clear();

        $this->assertTrue(Cart::isEmpty());
    }

    /** @test */
    public function it_removes_any_added_discounts()
    {
        // Create a discount coupon for 50%.
        $discount = factory(DiscountCoupon::class)->create(['value' => 50, 'is_fixed' => 0]);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        Cart::clear();

        $this->assertFalse(Cart::hasDiscount($discount->code));
    }

    /** @test */
    public function it_fires_event()
    {
        Event::fake();

        Cart::clear();

        Event::assertDispatched('shopr.cart.cleared');
    }
}
