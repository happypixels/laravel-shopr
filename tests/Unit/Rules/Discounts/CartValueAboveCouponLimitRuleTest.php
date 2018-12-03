<?php

namespace Happypixels\Shopr\Tests\Unit\Rules\Discounts;

use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Rules\Discounts\CartValueAboveCouponLimit;

class CartValueAboveCouponLimitRuleTest extends TestCase
{
    /** @test */
    public function it_fails_if_cart_value_is_below_coupon_lower_limit()
    {
        factory(DiscountCoupon::class)->create(['code' => 'TEST', 'lower_cart_limit' => 501]);

        // Worth 500.
        $this->addCartItem();

        $this->assertFalse((new CartValueAboveCouponLimit)->passes('code', 'TEST'));
    }

    /** @test */
    public function it_passes_if_cart_value_is_above_coupon_lower_limit()
    {
        factory(DiscountCoupon::class)->create(['code' => 'TEST', 'lower_cart_limit' => 500]);

        // Worth 500.
        $this->addCartItem();

        $this->assertTrue((new CartValueAboveCouponLimit)->passes('code', 'TEST'));
    }

    /** @test */
    public function it_requires_cart_value_to_be_higher_than_coupon_value_if_limit_is_null()
    {
        factory(DiscountCoupon::class)->create(['code' => 'TEST', 'value' => 1, 'is_fixed' => 1, 'lower_cart_limit' => null]);

        $this->assertFalse((new CartValueAboveCouponLimit)->passes('code', 'TEST'));

        // Worth 500.
        $this->addCartItem();

        $this->assertTrue((new CartValueAboveCouponLimit)->passes('code', 'TEST'));
    }

    /** @test */
    public function it_returns_the_correct_message()
    {
        $this->assertEquals(
            trans('shopr::discounts.invalid_coupon'),
            (new CartValueAboveCouponLimit)->message()
        );
    }
}
