<?php

namespace Happypixels\Shopr\Tests\Unit\Discounts\Validation;

use Happypixels\Shopr\Rules\Discounts\CouponHasNotBeenApplied;
use Happypixels\Shopr\Tests\TestCase;

class CouponHasNotBeenAppliedRuleTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_coupon_has_been_applied()
    {
        $this->mockCart()->shouldReceive('hasDiscountCoupon')->with('TEST')->andReturn(true);

        $this->assertFalse((new CouponHasNotBeenApplied)->passes('code', 'TEST'));
    }

    /** @test */
    public function it_returns_true_if_coupon_exists()
    {
        $this->mockCart()->shouldReceive('hasDiscountCoupon')->with('TEST')->andReturn(false);

        $this->assertTrue((new CouponHasNotBeenApplied)->passes('code', 'TEST'));
    }
}
