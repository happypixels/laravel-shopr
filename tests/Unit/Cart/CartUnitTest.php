<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartUnitTest extends TestCase
{
    /** @test */
    public function the_summary_holds_the_correct_data()
    {
        $cart = app(Cart::class);

        $this->assertEquals([
            'items', 'sub_total', 'sub_total_formatted', 'tax_total', 'tax_total_formatted',
            'total', 'total_formatted', 'count'
        ], array_keys($cart->summary()));
    }

    /** @test */
    public function total_returns_the_full_total()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 3);

        $this->assertEquals($model->price * 3, $cart->total());
    }

    /** @test */
    public function total_includes_sub_item_prices()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 2, [], [
            [
                'shoppable_type' => get_class($model),
                'shoppable_id'   => 1,
            ],
            [
                'shoppable_type' => get_class($model),
                'shoppable_id'   => 1,
            ]
        ]);

        // Each sub item gets the parent quantity, so 2. Which means 2 + 2 + 2 = 6.
        $this->assertEquals($model->price * 6, $cart->total());
    }

    /** @test */
    public function sub_total_returns_the_full_total_minus_tax()
    {
        config(['shopr.tax' => 20]);

        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 3);

        // 20% of 1500 is 300. Subtotal should be 1200.
        $this->assertEquals(1200, $cart->subTotal());
    }

    /** @test */
    public function tax_total_returns_the_total_tax()
    {
        config(['shopr.tax' => 20]);

        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 3);

        // 20% of 1500 is 300.
        $this->assertEquals(300, $cart->taxTotal());
    }

    /** @test */
    public function the_count_returns_the_full_quantity_of_all_items()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 3);
        $cart->addItem(get_class($model), $model->id, 2, ['color' => 'Green']);

        $this->assertEquals(5, $cart->count());
    }

    /** @test */
    public function items_are_a_collection_of_cart_items()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1);

        $this->assertEquals('Illuminate\Support\Collection', get_class($cart->items()));
        $this->assertEquals('Happypixels\Shopr\CartItem', get_class($cart->items()->first()));
    }

    /** @test */
    public function sub_items_are_a_collection_of_cart_sub_items()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 1, [], [
            [
                'shoppable_type' => get_class($model),
                'shoppable_id'   => 1,
            ]
        ]);

        $this->assertEquals('Illuminate\Support\Collection', get_class($cart->items()->first()->subItems));
        $this->assertEquals('Happypixels\Shopr\CartSubItem', get_class($cart->items()->first()->subItems->first()));
    }

    /** @test */
    public function test_is_empty()
    {
        $cart  = app(Cart::class);

        $this->assertTrue($cart->isEmpty());

        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1);

        $this->assertFalse($cart->isEmpty());
    }
}
