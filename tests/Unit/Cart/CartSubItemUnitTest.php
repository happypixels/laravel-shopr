<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartSubItemUnitTest extends TestCase
{
    /** @test */
    public function the_total_amount_includes_sub_items()
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

        $this->assertEquals($model->price * 2, $cart->items()->first()->subItems->first()->total);
    }
}
