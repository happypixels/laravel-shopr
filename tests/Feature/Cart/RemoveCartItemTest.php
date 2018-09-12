<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class RemoveCartItemTest extends TestCase
{
    /** @test */
    public function it_removes_the_item()
    {
        $cart   = app(Cart::class);
        $model  = TestShoppable::first();
        $item   = $cart->addItem(get_class($model), 1, 1, $options = []);
        $item2  = $cart->addItem(get_class($model), 1, 1, $options = ['color' => 'Green']);

        $this->assertEquals(2, $cart->count());

        $this->json('DELETE', 'api/shopr/cart/items/' . $item2->id)
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 1]);

        $this->assertEquals(1, $cart->count());
        $this->assertEquals($item->id, $cart->items()->first()->id);
    }
}
