<?php

namespace Happypixels\Shopr\Tests\REST\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class RemoveCartItemHttpTest extends TestCase
{
    /** @test */
    public function it_deletes_the_item()
    {
        Cart::add(TestShoppable::first())->save();
        $item = Cart::add(TestShoppable::first())->options(['color' => 'Green'])->save();

        Cart::shouldReceive('delete')->once()->with($item->id);
        Cart::shouldReceive('get')->once()->andReturn(['result']);

        $this->json('DELETE', 'api/shopr/cart/items/'.$item->id)
            ->assertStatus(200)
            ->assertJson(['result']);
    }
}
