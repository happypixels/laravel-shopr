<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartItemUnitTest extends TestCase
{
    /** @test */
    public function it_holds_the_total_amount()
    {
        $item = Cart::add($model = TestShoppable::first())->quantity(3)->save();

        $this->assertEquals($model->price * 3, $item->total);
    }

    /** @test */
    public function the_total_amount_includes_sub_items()
    {
        Cart::add($model = TestShoppable::first())->quantity(2)->subItems([
            ['shoppable' => $model, 'price' => 50],
            ['shoppable' => $model],
        ])->save();

        // Each sub item gets the parent quantity, so 2. Which means we have 6 models.
        // 2 of these cost 50, the rest cost 500. So 2000 + 100 = 2100.
        $this->assertEquals(2100, Cart::items()->first()->total);
    }
}
