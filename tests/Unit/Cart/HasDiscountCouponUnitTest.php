<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class HasDiscountCouponUnitTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_discount_is_not_applied()
    {
        Cart::add(TestShoppable::first());

        $this->assertFalse(Cart::hasDiscount('CODE'));
    }

    /** @test */
    public function it_returns_true_if_match_is_found()
    {
        $discount = factory(DiscountCoupon::class)->create(['code' => 'Code']);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $this->assertTrue(Cart::hasDiscount('Code'));
    }

    /** @test */
    public function it_is_case_sensitive()
    {
        $discount = factory(DiscountCoupon::class)->create(['code' => 'Code']);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $this->assertFalse(Cart::hasDiscount('CODE'));
        $this->assertFalse(Cart::hasDiscount('CodE'));
        $this->assertFalse(Cart::hasDiscount('CoDe'));
        $this->assertTrue(Cart::hasDiscount('Code'));
    }

    /** @test */
    public function it_looks_for_any_discount_if_input_is_empty()
    {
        $discount = factory(DiscountCoupon::class)->create(['code' => 'Code']);

        Cart::add(TestShoppable::first());

        $this->assertFalse(Cart::hasDiscount());

        Cart::addDiscount($discount);

        $this->assertTrue(Cart::hasDiscount());
    }

    /** @test */
    public function it_accepts_code_or_full_discount_object()
    {
        $discount = factory(DiscountCoupon::class)->create(['code' => 'Code']);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $this->assertTrue(Cart::hasDiscount($discount));
        $this->assertTrue(Cart::hasDiscount($discount->code));
    }
}
