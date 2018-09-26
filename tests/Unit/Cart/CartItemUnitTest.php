<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartItemUnitTest extends TestCase
{
    /** @test */
    public function it_holds_the_total_amount()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 3);

        $item = $cart->items()->first();
        $this->assertEquals($model->price * 3, $item->total);
    }

    /** @test */
    public function the_total_amount_includes_sub_items()
    {
        $cart  = app(Cart::class);
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
            ]
        ]);

        // Each sub item gets the parent quantity, so 2. Which means we have 6 models.
        // 2 of these cost 50, the rest cost 500. So 2000 + 100 = 2100.
        $this->assertEquals(2100, $cart->items()->first()->total);
    }
}
