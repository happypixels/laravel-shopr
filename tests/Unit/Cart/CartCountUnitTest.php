<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartCountUnitTest extends TestCase
{
    /** @test */
    public function it_includes_the_full_quantity_of_all_countable_items()
    {
        Cart::add(TestShoppable::first())->quantity(2)->save();
        Cart::add(TestShoppable::first())->quantity(3)->options(['color' => 'green'])->save();

        $this->assertEquals(5, Cart::count());
    }

    /** @test */
    public function it_does_not_include_discount_coupons()
    {
        $coupon = factory(DiscountCoupon::class)->create();

        Cart::add(TestShoppable::first())->quantity(2)->save();
        Cart::add($coupon)->quantity(3)->save();

        $this->assertEquals(2, Cart::count());
    }
}
