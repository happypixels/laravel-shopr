<?php

namespace Happypixels\Shopr\Tests\Unit\Rules\Discounts;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Rules\Discounts\CouponHasNotBeenApplied;
use Happypixels\Shopr\Tests\TestCase;

class CouponHasNotBeenAppliedRuleTest extends TestCase
{
    /** @test */
    public function it_fails_if_coupon_has_been_applied()
    {
        Cart::shouldReceive('hasDiscount')->with('TEST')->andReturn(true);

        $this->assertFalse((new CouponHasNotBeenApplied)->passes('code', 'TEST'));
    }

    /** @test */
    public function it_passes_if_coupon_has_not_been_applied()
    {
        Cart::shouldReceive('hasDiscount')->with('TEST')->andReturn(false);

        $this->assertTrue((new CouponHasNotBeenApplied)->passes('code', 'TEST'));
    }
}
