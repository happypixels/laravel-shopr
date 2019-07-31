<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartItemsUnitTest extends TestCase
{
    /** @test */
    public function items_are_a_collection_of_cart_items()
    {
        Cart::add(TestShoppable::first())->save();

        $this->assertEquals('Illuminate\Support\Collection', get_class(Cart::items()));
        $this->assertEquals('Happypixels\Shopr\Cart\CartItem', get_class(Cart::items()->first()));
    }

    /** @test */
    public function items_dont_include_discounts()
    {
        Cart::add(TestShoppable::first())->save();
        Cart::add(factory(DiscountCoupon::class)->create())->quantity(3)->save();

        $this->assertEquals(1, Cart::items()->count());
    }

    /** @test */
    public function discounts_are_a_collection_of_cart_items_with_only_added_discounts()
    {
        Cart::add(TestShoppable::first())->save();
        Cart::add($discount = factory(DiscountCoupon::class)->create())->save();

        $this->assertEquals('Illuminate\Support\Collection', get_class(Cart::discounts()));
        $this->assertEquals('Happypixels\Shopr\Cart\CartItem', get_class(Cart::discounts()->first()));
        $this->assertEquals(1, Cart::discounts()->count());
        $this->assertEquals($discount->code, Cart::discounts()->first()->shoppable->getTitle());
    }

    /** @test */
    public function sub_items_are_a_collection_of_cart_items()
    {
        Cart::add(TestShoppable::first())->subItems([
            ['shoppable' => TestShoppable::first()],
        ])->save();

        $this->assertEquals('Illuminate\Support\Collection', get_class(Cart::items()->first()->subItems));
        $this->assertEquals('Happypixels\Shopr\Cart\CartItem', get_class(Cart::items()->first()->subItems->first()));
    }
}
