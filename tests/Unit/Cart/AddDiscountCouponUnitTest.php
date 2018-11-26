<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class AddDiscountCouponUnitTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_model_is_not_a_discount()
    {
        $shoppable = factory(TestShoppable::class)->create();

        $this->assertFalse(app(Cart::class)->addDiscount($shoppable));
    }

    /** @test */
    public function it_calculates_the_total_value_when_not_fixed()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => false, 'value' => 50]);
        $cart = $this->addCartItem();

        // 1500 / 2 = 750.
        $item = $cart->addDiscount($discount);
        $this->assertEquals(-750, $item->total());
        $this->assertEquals(750, $cart->total());
    }

    /** @test */
    public function it_applies_the_given_value_when_fixed()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);
        $cart = $this->addCartItem();

        // 1500 - 300 = 1200.
        $item = $cart->addDiscount($discount);
        $this->assertEquals(-300, $item->total());
        $this->assertEquals(1200, $cart->total());
    }

    /** @test */
    public function it_fires_event()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);
        $cart = $this->addCartItem();

        Event::fake();

        $item = $cart->addDiscount($discount);

        // The first time the added event is fired.
        Event::assertDispatched('shopr.cart.discounts.added', function ($event, $data) use ($item) {
            return (
                $data->price === -300 &&
                serialize($item) === serialize($data)
            );
        });
    }

    /** @test */
    public function it_increments_the_coupon_uses()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);
        $cart = $this->addCartItem();

        $this->assertEquals(0, $discount->uses);

        $cart->addDiscount($discount);

        $this->assertEquals(1, $discount->uses);
    }

    public function addCartItem()
    {
        $cart = app(Cart::class);
        $model = factory(TestShoppable::class)->create(['price' => 500]);
        $cart->addItem(get_class($model), $model->id, 3);

        return $cart;
    }
}
