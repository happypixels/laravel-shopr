<?php

namespace Happypixels\Shopr\Tests\REST\Discounts;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Rules\DiscountTestRule;
use Happypixels\Shopr\Tests\TestCase;

class AddDiscountValidationHttpTest extends TestCase
{
    /** @test */
    public function the_code_is_required()
    {
        $this->json('POST', 'api/shopr/cart/discounts')
            ->assertStatus(422)
            ->assertJsonFragment(['The code field is required.']);
    }

    /** @test */
    public function cart_must_have_items()
    {
        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => 'TEST'])
            ->assertStatus(422)
            ->assertJsonFragment(['Your cart is empty.']);
    }

    /** @test */
    public function only_one_coupon_allowed()
    {
        $discounts = factory(DiscountCoupon::class, 2)->create();

        $this->addCartItem();

        app(Cart::class)->addDiscount($discounts->first());

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discounts->last()->code])
            ->assertStatus(422)
            ->assertJsonFragment(['A discount coupon has already been applied.']);
    }

    /** @test */
    public function coupon_has_not_been_applied()
    {
        $discount = factory(DiscountCoupon::class)->create();

        $this->addCartItem();

        app(Cart::class)->addDiscount($discount);

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount->code])
            ->assertStatus(422)
            ->assertJsonFragment(['That discount coupon has already been applied.']);
    }

    /** @test */
    public function coupon_exists()
    {
        $this->addCartItem();

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => 'Test'])
            ->assertStatus(422)
            ->assertJsonFragment(['Invalid discount coupon.']);
    }

    /** @test */
    public function coupon_is_valid()
    {
        $discount = factory(DiscountCoupon::class)->create(['valid_until' => now()->subDays(1)]);

        $this->addCartItem();

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount->code])
            ->assertStatus(422)
            ->assertJsonFragment(['Invalid discount coupon.']);
    }

    /** @test */
    public function cart_value_is_high_enough()
    {
        $discount = factory(DiscountCoupon::class)->create(['lower_cart_limit' => 600]);
        $discount2 = factory(DiscountCoupon::class)->create(['value' => 600, 'is_fixed' => 1]);

        $this->addCartItem();

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount->code])
            ->assertStatus(422)
            ->assertJsonFragment([trans('shopr::discounts.invalid_coupon')]);

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount2->code])
            ->assertStatus(422)
            ->assertJsonFragment([trans('shopr::discounts.invalid_coupon')]);
    }

    /** @test */
    public function it_validates_custom_rules()
    {
        $rules = config('shopr.discount_coupons.validation_rules');
        $rules[] = new DiscountTestRule;
        config(['shopr.discount_coupons.validation_rules' => $rules]);

        $discount = factory(DiscountCoupon::class)->create();

        $this->addCartItem();

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount->code])
            ->assertStatus(422)
            ->assertJsonFragment(['The test rule failed.']);
    }

    /** @test */
    public function it_skips_rules_that_are_not_enabled()
    {
        config(['shopr.discount_coupons.validation_rules' => []]);

        // An invalid discount coupon.
        $discount = factory(DiscountCoupon::class)->create(['valid_until' => now()->subDays(1)]);

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount->code])
            ->assertStatus(200);
    }
}
