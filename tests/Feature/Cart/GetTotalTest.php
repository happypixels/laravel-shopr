<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class GetTotalTest extends TestCase
{
    /** @test */
    public function it_returns_the_sum_of_all_items()
    {
        Cart::add(TestShoppable::first(), ['quantity' => 3]);

        $this->assertEquals(1500, Cart::total());
    }

    /** @test */
    public function it_includes_sub_item_prices()
    {
        Cart::add(TestShoppable::first(), ['quantity' => 2, 'sub_items' => [
            ['shoppable' => TestShoppable::first(), 'price' => 50],
            ['shoppable' => TestShoppable::first()],
        ]]);

        // (500 + 50 + 500) * 2.
        $this->assertEquals(2100, Cart::total());
    }
}
