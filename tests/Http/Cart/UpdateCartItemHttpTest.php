<?php

namespace Happypixels\Shopr\Tests\Http\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class UpdateCartItemHttpTest extends TestCase
{
    /** @test */
    public function it_updates_the_item()
    {
        $this->withoutExceptionHandling();

        $item = Cart::add(TestShoppable::first());

        Cart::shouldReceive('update')->once()->with($item->id, ['quantity' => 2]);
        Cart::shouldReceive('get')->once()->andReturn(['result']);

        $this->json('PATCH', 'api/shopr/cart/items/'.$item->id, ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonFragment(['result']);
    }
}
