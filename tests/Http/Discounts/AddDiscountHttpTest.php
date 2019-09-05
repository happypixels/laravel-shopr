<?php

namespace Happypixels\Shopr\Tests\Http\Discounts;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;

class AddDiscountHttpTest extends TestCase
{
    /** @test */
    public function it_adds_the_coupon()
    {
        $this->withoutExceptionHandling();

        $discount = factory(DiscountCoupon::class)->create();

        Cart::shouldReceive('addDiscount')->once()->with($discount->code);
        Cart::shouldReceive('get')->once()->andReturn(['result']);

        $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount->code])
            ->assertStatus(200)
            ->assertJson(['result']);
    }

    /** @test */
    public function it_validates_the_code()
    {
        $this->json('POST', 'api/shopr/cart/discounts', ['code' => ''])->assertStatus(422);
    }

    /** @test */
    public function it_validates_configurated_rules()
    {
        $discount = factory(DiscountCoupon::class)->create();

        $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount->code])
            ->assertStatus(422)
            ->assertJsonFragment(['message' => trans('shopr::cart.cart_is_empty')]);
    }
}
