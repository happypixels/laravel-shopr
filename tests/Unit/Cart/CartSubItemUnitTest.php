<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartSubItemUnitTest extends TestCase
{
    /** @test */
    public function the_total_amount_includes_sub_items()
    {
        Cart::add($model = TestShoppable::first())->quantity(2)->subItems([
            ['shoppable' => $model],
            ['shoppable' => $model],
        ])->save();

        $this->assertEquals($model->price * 2, Cart::items()->first()->subItems->first()->total);
    }

    /** @test */
    public function it_has_price()
    {
        Cart::add($model = TestShoppable::first())->quantity(2)->subItems([
            ['shoppable' => $model],
        ])->save();

        $subItem = Cart::items()->first()->subItems->first();

        $this->assertEquals(500, $subItem->price);
        $this->assertEquals('$500.00', $subItem->price_formatted);
    }
}
