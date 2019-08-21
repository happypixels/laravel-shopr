<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Cart\CartItem;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

/**
 * Discount validation tests are found in ValidateDiscountUnitTest.php.
 * This test class only tests the behaviour related to actually adding a discount.
 */
class AddDiscountUnitTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_model_is_not_a_discount()
    {
        config(['shopr.discount_coupons.validation_rules' => []]);

        $this->assertFalse(Cart::addDiscount(TestShoppable::first()));
    }

    /** @test */
    public function it_returns_the_cart_item_when_successful()
    {
        config(['shopr.discount_coupons.validation_rules' => []]);

        $item = Cart::addDiscount(
            factory(DiscountCoupon::class)->create()
        );

        $this->assertTrue($item instanceof CartItem);
        $this->assertTrue($item->shoppable->isDiscount());
    }

    /** @test */
    public function it_calculates_the_total_value_when_not_fixed()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => false, 'value' => 50]);

        Cart::add(TestShoppable::first(), ['quantity' => 3]);

        // 1500 / 2 = 750.
        $item = Cart::addDiscount($discount);
        $this->assertEquals(-750, $item->total_price);
        $this->assertEquals(750, Cart::total());
    }

    /** @test */
    public function it_applies_the_given_value_when_fixed()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);

        Cart::add(TestShoppable::first(), ['quantity' => 3]);

        // 1500 - 300 = 1200.
        $item = Cart::addDiscount($discount);
        $this->assertEquals(-300, $item->total_price);
        $this->assertEquals(1200, Cart::total());
    }

    /** @test */
    public function it_fires_the_added_event()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);

        Cart::add(TestShoppable::first(), ['quantity' => 3]);

        Event::fake();

        $item = Cart::addDiscount($discount);

        // The first time the added event is fired.
        Event::assertDispatched('shopr.cart.discounts.added', function ($event, $data) use ($item) {
            return $data->is($item);
        });
    }

    /** @test */
    public function it_increments_the_coupon_uses()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);

        Cart::add(TestShoppable::first(), ['quantity' => 3]);

        $this->assertEquals(0, $discount->uses);

        Cart::addDiscount($discount);

        $this->assertEquals(1, $discount->refresh()->uses);
    }
}
