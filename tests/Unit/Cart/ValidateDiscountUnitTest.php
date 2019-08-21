<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Rules\Cart\CartNotEmpty;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class ValidateDiscountUnitTest extends TestCase
{
    /** @test */
    public function it_throws_exception_if_cart_is_empty()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);

        try {
            Cart::addDiscount($discount);

            $this->fail('The cart was empty but no exception was thrown.');
        } catch (\Exception $e) {
            $this->assertEquals(422, $e->getCode());
            $this->assertEquals(trans('shopr::cart.cart_is_empty'), $e->getMessage());
        }
    }

    /** @test */
    public function it_throws_exception_if_a_discount_is_already_added()
    {
        $discounts = factory(DiscountCoupon::class, 2)->create(['value' => 100, 'is_fixed' => 1]);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discounts->first());

        try {
            Cart::addDiscount($discounts->last());

            $this->fail('A discount was already added but no exception was thrown.');
        } catch (\Exception $e) {
            $this->assertEquals(422, $e->getCode());
            $this->assertEquals(trans('shopr::discounts.other_coupon_applied'), $e->getMessage());
        }
    }

    /** @test */
    public function it_throws_exception_if_the_coupon_does_not_exist()
    {
        Cart::add(TestShoppable::first());

        try {
            Cart::addDiscount('INVALID');

            $this->fail('The discount does not exist but no exception was thrown.');
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof ModelNotFoundException);
        }
    }

    /** @test */
    public function it_throws_exception_if_the_coupon_has_expired()
    {
        // Has expired.
        $discount1 = factory(DiscountCoupon::class)->create([
            'value' => 100,
            'is_fixed' => 1,
            'valid_from' => now()->subDays(5),
            'valid_until' => now()->subDays(2),
        ]);

        // Isn't valid yet.
        $discount2 = factory(DiscountCoupon::class)->create([
            'value' => 100,
            'is_fixed' => 1,
            'valid_from' => now()->addDays(2),
            'valid_until' => now()->addDays(5),
        ]);

        Cart::add(TestShoppable::first());

        try {
            Cart::addDiscount($discount1);

            $this->fail('The discount has expired but no exception was thrown.');
        } catch (\Exception $e) {
            $this->assertEquals(422, $e->getCode());
            $this->assertEquals(trans('shopr::discounts.invalid_coupon'), $e->getMessage());
        }

        try {
            Cart::addDiscount($discount2);

            $this->fail('The discount is not valid yet but no exception was thrown.');
        } catch (\Exception $e) {
            $this->assertEquals(422, $e->getCode());
            $this->assertEquals(trans('shopr::discounts.invalid_coupon'), $e->getMessage());
        }
    }

    /** @test */
    public function it_throws_exception_if_cart_value_is_too_low()
    {
        $discount = factory(DiscountCoupon::class)->create(['value' => 600, 'is_fixed' => 1]);

        Cart::add(TestShoppable::first());

        try {
            Cart::addDiscount($discount);

            $this->fail('The cart value is lower than the discount but no exception was thrown.');
        } catch (\Exception $e) {
            $this->assertEquals(422, $e->getCode());
            $this->assertEquals(trans('shopr::discounts.invalid_coupon'), $e->getMessage());
        }
    }

    /** @test */
    public function it_only_validates_rules_specified_in_the_config()
    {
        $discount = factory(DiscountCoupon::class)->create(['value' => 600, 'is_fixed' => 1]);

        // No rules.
        config(['shopr.discount_coupons.validation_rules' => []]);
        Cart::addDiscount($discount);
        $this->assertEquals(-600, Cart::total());

        // Only require the cart to not be empty.
        config(['shopr.discount_coupons.validation_rules' => [CartNotEmpty::class]]);

        try {
            Cart::addDiscount($discount);

            $this->fail('Cart is empty but no exception was thrown.');
        } catch (\Exception $e) {
            $this->assertEquals(trans('shopr::cart.cart_is_empty'), $e->getMessage());
        }
    }
}
