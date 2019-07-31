<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class AddDiscountCouponUnitTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_model_is_not_a_discount()
    {
        $this->assertFalse(Cart::addDiscount(factory(TestShoppable::class)->create()));
        $this->assertFalse(Cart::hasDiscount());
    }

    /** @test */
    public function it_calculates_the_total_value_when_not_fixed()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => false, 'value' => 50]);

        Cart::add(TestShoppable::first())->overridePrice(500)->quantity(3)->save();
        $item = Cart::addDiscount($discount);

        // 1500 / 2 = 750.
        $this->assertEquals(-750, $item->total());
        $this->assertEquals(750, Cart::total());
    }

    /** @test */
    public function it_applies_the_given_value_when_fixed()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);
        Cart::add(TestShoppable::first())->overridePrice(500)->quantity(3)->save();
        $item = Cart::addDiscount($discount);

        // 1500 - 300 = 1200.
        $this->assertEquals(-300, $item->total());
        $this->assertEquals(1200, Cart::total());
    }

    /** @test */
    public function it_fires_event()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);
        Cart::add(TestShoppable::first())->overridePrice(500)->quantity(3)->save();

        Event::fake();

        $item = Cart::addDiscount($discount);

        // The first time the added event is fired.
        Event::assertDispatched('shopr.cart.discounts.added', function ($event, $data) use ($item) {
            return
                $data->price === -300 &&
                serialize($item) === serialize($data);
        });
    }

    /** @test */
    public function it_increments_the_coupon_uses()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);
        Cart::add(TestShoppable::first())->overridePrice(500)->quantity(3)->save();

        $this->assertEquals(0, $discount->uses);

        Cart::addDiscount($discount);

        $this->assertEquals(1, $discount->uses);
    }
}
