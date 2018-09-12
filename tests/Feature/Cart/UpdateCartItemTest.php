<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class UpdateCartItemTest extends TestCase
{
    /** @test */
    public function it_updates_the_item_quantity()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $item  = $cart->addItem(get_class($model), 1, 1);

        $this->assertEquals(1, $cart->items()->first()->quantity);

        $response = $this->json('PATCH', 'api/shopr/cart/items/' . $item->id, ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 2]);

        $this->assertEquals(2, $cart->count());
        $this->assertEquals(2, $cart->items()->first()->quantity);
    }
}
