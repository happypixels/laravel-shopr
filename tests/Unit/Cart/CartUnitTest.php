<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartUnitTest extends TestCase
{
    /** @test */
    public function the_summary_holds_the_correct_data()
    {
        $this->assertEquals([
            'items', 'discounts', 'sub_total', 'sub_total_formatted', 'tax_total',
            'tax_total_formatted', 'total', 'total_formatted', 'count',
        ], array_keys(Cart::get()));
    }

    /** @test */
    public function total_returns_the_full_total()
    {
        Cart::add(TestShoppable::first())->quantity(3)->save();

        $this->assertEquals(1500, Cart::total());
    }

    /** @test */
    public function total_includes_sub_item_prices()
    {
        Cart::add(TestShoppable::first())->quantity(2)->subItems([
            ['shoppable' => TestShoppable::first(), 'price' => 50],
            ['shoppable' => TestShoppable::first()],
        ])->save();

        // Each sub item gets the parent quantity, so 2.
        // We have 6 models in total. 2 of these cost 50 each.
        // So 500 * 4 + 50 * 2.
        $this->assertEquals(2100, Cart::total());
    }

    /** @test */
    public function sub_total_returns_the_full_total_minus_tax()
    {
        config(['shopr.tax' => 25]);

        Cart::add(TestShoppable::first())->quantity(5)->save();

        // 25% tax of 2500 = 500. Subtotal should be 2000.
        $this->assertEquals(2000, Cart::subTotal());
    }

    /** @test */
    public function tax_total_returns_the_total_tax()
    {
        config(['shopr.tax' => 25]);

        Cart::add(TestShoppable::first())->quantity(5)->save();

        // 25% tax of 2500 = 500
        $this->assertEquals(500, Cart::taxTotal());
    }

    /** @test */
    public function test_is_empty()
    {
        $this->assertTrue(Cart::isEmpty());

        Cart::add(TestShoppable::first())->save();

        $this->assertFalse(Cart::isEmpty());
    }
}
