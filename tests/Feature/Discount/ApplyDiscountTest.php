<?php

namespace Happypixels\Shopr\Tests\Feature\Discount;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class ApplyDiscountTest extends TestCase
{
    /** @test */
    public function it_validates_the_code()
    {
        $this->addItem();

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => 'TESTCODE'])
            ->assertStatus(422)
            ->assertJsonFragment(['code' => ['Invalid discount coupon.']]);
    }

    /** @test */
    public function it_requires_the_discount_to_be_valid()
    {
        $this->addItem();

        DiscountCoupon::create([
            'code'        => 'The Code',
            'valid_from'  => now()->subDays(2),
            'valid_until' => now()->subDays(1)
        ]);

        $response = $this->json('POST', 'api/shopr/cart/discounts', ['code' => 'The Code'])
            ->assertStatus(422)
            ->assertJsonFragment(['code' => ['Invalid discount coupon.']]);
    }

    /** @test */
    public function it_applies_fixed_price_discounts()
    {
        $this->addItem();

        DiscountCoupon::create([
            'code'        => 'The Code',
            'valid_from'  => now()->subDays(1),
            'valid_until' => now()->addDays(1),
            'is_fixed'    => 1,
            'value'       => 50
        ]);

        $this->assertEquals(450, app(Cart::class)->total());
    }

    /** @test */
    public function it_applies_percentage_discounts()
    {
    }

    /** @test */
    public function it_aborts_if_code_is_already_applied()
    {
    }

    /** @test */
    public function the_discount_is_applied_on_the_order_as_well()
    {
    }

    /** @test */
    public function it_fires_event()
    {
    }

    /** @test */
    public function it_returns_the_cart_summary()
    {
    }

    protected function addItem()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 1);
    }
}
