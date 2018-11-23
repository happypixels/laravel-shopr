<?php

namespace Happypixels\Shopr\Tests\Unit\Rules\Discounts;

use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Rules\Discounts\CouponExists;
use Happypixels\Shopr\Tests\TestCase;

class CouponExistsRuleTest extends TestCase
{
    /** @test */
    public function it_fails_if_coupon_doesnt_exist()
    {
        factory(DiscountCoupon::class)->create(['code' => 'TEST']);

        $this->assertFalse((new CouponExists)->passes('code', 'Something else'));
    }

    /** @test */
    public function it_passes_if_coupon_exists()
    {
        // Valid.
        factory(DiscountCoupon::class)->create(['code' => 'TEST']);
        $this->assertTrue((new CouponExists)->passes('code', 'TEST'));

        // Invalid should also pass, as we have a different rule for validating the validity.
        factory(DiscountCoupon::class)->create([
            'code' => 'INVALID',
            'valid_from' => now()->addDays(2),
            'valid_until' => now()->addDays(3)
        ]);
        $this->assertTrue((new CouponExists)->passes('code', 'INVALID'));
    }
}
