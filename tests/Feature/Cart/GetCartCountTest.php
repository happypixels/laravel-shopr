<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class GetCartCountTest extends TestCase
{
    /** @test */
    public function it_returns_the_full_quantity()
    {
        Cart::add(TestShoppable::first(), ['quantity' => 2]);
        Cart::add(TestShoppable::first(), ['options' => ['size' => 'L']]);

        $this->assertEquals(3, Cart::count());
    }

    /** @test */
    public function it_does_not_include_discount_coupons()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => false, 'value' => 50]);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $this->assertEquals(1, Cart::count());
    }

    /** @test */
    public function it_does_not_include_sub_items()
    {
        Cart::add(TestShoppable::first(), [
            'sub_items' => [
                ['shoppable' => TestShoppable::first()],
            ],
        ]);

        $this->assertEquals(1, Cart::count());
        $this->assertEquals(1, Cart::items()->first()->sub_items->count());
    }

    /** @test */
    public function it_returns_0_if_empty()
    {
        $this->assertEquals(0, Cart::count());
    }
}
