<?php

namespace Happypixels\Shopr\Tests\Unit\Discounts\Validation;

use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Rules\Discounts\CouponExists;
use Happypixels\Shopr\Tests\TestCase;

class CouponExistsRuleTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_coupon_doesnt_exist()
    {
        factory(DiscountCoupon::class)->create(['code' => 'TEST']);

        $this->assertFalse((new CouponExists)->passes('code', 'Something else'));
    }

    /** @test */
    public function it_returns_true_if_coupon_exists()
    {
        factory(DiscountCoupon::class)->create(['code' => 'TEST']);

        $this->assertTrue((new CouponExists)->passes('code', 'TEST'));
    }
}
