<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartUnitTest extends TestCase
{
    /** @test */
    public function the_summary_holds_the_correct_data()
    {
        $cart = app(Cart::class);

        $this->assertEquals([
            'items', 'discounts', 'sub_total', 'sub_total_formatted', 'tax_total',
            'tax_total_formatted', 'total', 'total_formatted', 'count',
        ], array_keys($cart->summary()));
    }

    /** @test */
    public function total_returns_the_full_total()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 3);

        $this->assertEquals(1500, $cart->total());
    }

    /** @test */
    public function total_includes_sub_item_prices()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 2, [], [
            [
                'shoppable_type' => get_class($model),
                'shoppable_id'   => 1,
                'price'          => 50,
            ],
            [
                'shoppable_type' => get_class($model),
                'shoppable_id'   => 1,
            ],
        ]);

        // Each sub item gets the parent quantity, so 2. We have 6 models in total. 2 of these cost 50 each.
        // So 500*4 + 50*2.
        $this->assertEquals(2100, $cart->total());
    }

    /** @test */
    public function sub_total_returns_the_full_total_minus_tax()
    {
        config(['shopr.tax' => 25]);

        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 5);

        // 25% tax of 2500 = 500. Subtotal should be 2000.
        $this->assertEquals(2000, $cart->subTotal());
    }

    /** @test */
    public function tax_total_returns_the_total_tax()
    {
        config(['shopr.tax' => 25]);

        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 5);

        // 25% tax of 2500 = 500
        $this->assertEquals(500, $cart->taxTotal());
    }

    /** @test */
    public function the_count_returns_the_full_quantity_of_all_items()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 3);
        $cart->addItem(get_class($model), $model->id, 2, ['color' => 'Green']);

        $this->assertEquals(5, $cart->count());
    }

    /** @test */
    public function the_count_does_not_include_discount_coupons()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 1);
        $cart->addDiscount(factory(DiscountCoupon::class)->create());

        $this->assertEquals(1, $cart->count());
    }

    /** @test */
    public function items_are_a_collection_of_cart_items()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1);

        $this->assertEquals('Illuminate\Support\Collection', get_class($cart->items()));
        $this->assertEquals('Happypixels\Shopr\CartItem', get_class($cart->items()->first()));
    }

    /** @test */
    public function items_dont_include_discounts()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1);
        $cart->addDiscount(factory(DiscountCoupon::class)->create());

        $this->assertEquals(1, $cart->items()->count());
    }

    /** @test */
    public function discounts_are_a_collection_of_cart_items_with_only_added_discounts()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $discount = factory(DiscountCoupon::class)->create();
        $cart->addItem(get_class($model), 1);
        $cart->addDiscount($discount);

        $this->assertEquals('Illuminate\Support\Collection', get_class($cart->discounts()));
        $this->assertEquals('Happypixels\Shopr\CartItem', get_class($cart->discounts()->first()));
        $this->assertEquals(1, $cart->discounts()->count());
        $this->assertEquals($discount->code, $cart->discounts()->first()->shoppable->getTitle());
    }

    /** @test */
    public function sub_items_are_a_collection_of_cart_sub_items()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 1, [], [
            [
                'shoppable_type' => get_class($model),
                'shoppable_id'   => 1,
            ],
        ]);

        $this->assertEquals('Illuminate\Support\Collection', get_class($cart->items()->first()->subItems));
        $this->assertEquals('Happypixels\Shopr\CartSubItem', get_class($cart->items()->first()->subItems->first()));
    }

    /** @test */
    public function test_is_empty()
    {
        $cart = app(Cart::class);

        $this->assertTrue($cart->isEmpty());

        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1);

        $this->assertFalse($cart->isEmpty());
    }
}
