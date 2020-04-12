<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Cart\CartItem;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class CartItemUnitTest extends TestCase
{
    /** @test */
    public function it_holds_the_price_and_total()
    {
        $item = new CartItem(TestShoppable::first());
        $item->setQuantity(2);

        $this->assertEquals(1000, $item->total_price);
        $this->assertEquals('$1,000.00', $item->total_price_formatted);
        $this->assertEquals(500, $item->price);
        $this->assertEquals('$500.00', $item->price_formatted);
    }

    /** @test */
    public function the_price_and_total_amount_includes_sub_items()
    {
        $item = new CartItem(TestShoppable::first());
        $item->setQuantity(2);
        $item->setSubItems([
            ['shoppable' => TestShoppable::first()],
            ['shoppable' => TestShoppable::first(), 'price' => 50],
        ]);

        $this->assertEquals(1050, $item->price);
        $this->assertEquals('$1,050.00', $item->price_formatted);
        $this->assertEquals(2100, $item->total_price);
        $this->assertEquals('$2,100.00', $item->total_price_formatted);
    }
}
