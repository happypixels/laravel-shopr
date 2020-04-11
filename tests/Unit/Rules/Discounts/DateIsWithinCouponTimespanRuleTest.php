<?php

namespace Happypixels\Shopr\Tests\Unit\Rules\Discounts;

use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Rules\Discounts\DateIsWithinCouponTimespan;
use Happypixels\Shopr\Tests\TestCase;

class DateIsWithinCouponTimespanRuleTest extends TestCase
{
    /** @test */
    public function it_fails_if_code_is_invalid()
    {
        $coupon = factory(DiscountCoupon::class)->create();

        $this->assertFalse((new DateIsWithinCouponTimespan)->passes('code', 'Something else'));
    }

    /** @test */
    public function it_fails_if_the_timespan_is_invalid()
    {
        // In the future.
        $coupon = factory(DiscountCoupon::class)->create([
            'valid_from' => now()->addDays(3),
            'valid_until' => now()->addDays(5),
        ]);
        $this->assertFalse((new DateIsWithinCouponTimespan)->passes('code', $coupon->code));

        // In the past.
        $coupon = factory(DiscountCoupon::class)->create([
            'valid_from' => now()->subDays(5),
            'valid_until' => now()->subDays(3),
        ]);
        $this->assertFalse((new DateIsWithinCouponTimespan)->passes('code', $coupon->code));

        // No start date.
        $coupon = factory(DiscountCoupon::class)->create([
            'valid_from' => null,
            'valid_until' => now()->subDays(3),
        ]);
        $this->assertFalse((new DateIsWithinCouponTimespan)->passes('code', $coupon->code));

        // No end date.
        $coupon = factory(DiscountCoupon::class)->create([
            'valid_from' => now()->addDays(3),
            'valid_until' => null,
        ]);
        $this->assertFalse((new DateIsWithinCouponTimespan)->passes('code', $coupon->code));
    }

    /** @test */
    public function it_passes_if_the_timespan_is_valid()
    {
        // No end date.
        $coupon = factory(DiscountCoupon::class)->create([
            'valid_from' => now()->subDays(3),
            'valid_until' => null,
        ]);
        $this->assertTrue((new DateIsWithinCouponTimespan)->passes('code', $coupon->code));

        // No start date.
        $coupon = factory(DiscountCoupon::class)->create([
            'valid_from' => null,
            'valid_until' => now()->addDays(3),
        ]);
        $this->assertTrue((new DateIsWithinCouponTimespan)->passes('code', $coupon->code));

        // No time limit.
        $coupon = factory(DiscountCoupon::class)->create([
            'valid_from' => null,
            'valid_until' => null,
        ]);
        $this->assertTrue((new DateIsWithinCouponTimespan)->passes('code', $coupon->code));

        // Valid timespan.
        $coupon = factory(DiscountCoupon::class)->create([
            'valid_from' => now()->subDays(3),
            'valid_until' => now()->addDays(3),
        ]);
        $this->assertTrue((new DateIsWithinCouponTimespan)->passes('code', $coupon->code));
    }
}
