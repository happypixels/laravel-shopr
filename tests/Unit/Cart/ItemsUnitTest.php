<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class ItemsUnitTest extends TestCase
{
    /** @test */
    public function it_does_not_include_discounts()
    {
        $discount = factory(DiscountCoupon::class)->create();

        $item = Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $this->assertEquals(1, Cart::items()->count());
        $this->assertEquals($item->id, Cart::items()->first()->id);
    }

    /** @test */
    public function it_returns_a_collection_of_cart_items()
    {
        Cart::add(TestShoppable::first());

        $this->assertEquals('Illuminate\Support\Collection', get_class(Cart::items()));
        $this->assertEquals('Happypixels\Shopr\Cart\CartItem', get_class(Cart::items()->first()));
    }

    /** @test */
    public function sub_items_are_a_collection_of_cart_items()
    {
        Cart::add(TestShoppable::first(), [
            'sub_items' => [
                ['shoppable' => TestShoppable::first()],
            ],
        ]);

        $this->assertEquals('Illuminate\Support\Collection', get_class(Cart::items()->first()->sub_items));
        $this->assertEquals('Happypixels\Shopr\Cart\CartItem', get_class(Cart::items()->first()->sub_items->first()));
    }
}
