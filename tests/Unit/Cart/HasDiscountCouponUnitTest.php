<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class HasDiscountCouponUnitTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_discount_is_not_applied()
    {
        $cart = $this->addCartItem();

        $this->assertFalse($cart->hasDiscount('CODE'));
    }

    /** @test */
    public function it_returns_true_if_match_is_found()
    {
        $discount = factory(DiscountCoupon::class)->create(['code' => 'Code']);
        $cart = $this->addCartItem();
        $item = $cart->addDiscount($discount);

        $this->assertTrue($cart->hasDiscount('Code'));
    }

    /** @test */
    public function it_is_case_sensitive()
    {
        $discount = factory(DiscountCoupon::class)->create(['code' => 'Code']);
        $cart = $this->addCartItem();
        $item = $cart->addDiscount($discount);

        $this->assertFalse($cart->hasDiscount('CODE'));
        $this->assertFalse($cart->hasDiscount('CodE'));
        $this->assertFalse($cart->hasDiscount('CoDe'));
        $this->assertTrue($cart->hasDiscount('Code'));
    }

    protected function addCartItem()
    {
        $cart = app(Cart::class);
        $model = factory(TestShoppable::class)->create(['price' => 500]);
        $cart->addItem(get_class($model), $model->id, 3);

        return $cart;
    }
}
