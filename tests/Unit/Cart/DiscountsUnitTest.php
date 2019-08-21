<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class DiscountsUnitTest extends TestCase
{
    /** @test */
    public function it_returns_a_collection_of_cart_items()
    {
        $discount = factory(DiscountCoupon::class)->create(['value' => 50]);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $this->assertEquals('Illuminate\Support\Collection', get_class(Cart::discounts()));
        $this->assertEquals('Happypixels\Shopr\Cart\CartItem', get_class(Cart::discounts()->first()));
        $this->assertTrue(Cart::discounts()->first()->shoppable->is($discount));
    }

    /** @test */
    public function it_does_not_include_regular_items()
    {
        $discount = factory(DiscountCoupon::class)->create(['value' => 50]);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $this->assertEquals(1, Cart::discounts()->count());
    }
}
