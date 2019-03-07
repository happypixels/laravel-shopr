<?php

namespace Happypixels\Shopr\Tests\REST\Discounts;

use Happypixels\Shopr\Cart\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Traits\InteractsWithCart;

class AddDiscountHttpTest extends TestCase
{
    use InteractsWithCart;

    /** @test */
    public function it_adds_the_coupon()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 50]);
        $cart = app(Cart::class);

        $this->addCartItem();

        $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount->code])->assertStatus(200);

        $this->assertEquals(450, $cart->total());
        $this->assertTrue($cart->discounts()->first()->shoppable->isDiscount());
    }

    /** @test */
    public function it_returns_the_cart_summary()
    {
        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 50]);

        $this->addCartItem();

        $this->json('POST', 'api/shopr/cart/discounts', ['code' => $discount->code])
            ->assertStatus(200)
            ->assertJsonStructure(['count', 'total', 'sub_total', 'tax_total', 'items']);
    }
}
