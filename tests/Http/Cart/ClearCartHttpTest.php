<?php

namespace Happypixels\Shopr\Tests\Http\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;

class ClearCartHttpTest extends TestCase
{
    /** @test */
    public function it_clears_the_cart()
    {
        $this->withoutExceptionHandling();

        Cart::shouldReceive('clear')->once();
        Cart::shouldReceive('get')->once()->andReturn(['result']);

        $this->json('DELETE', 'api/shopr/cart')->assertStatus(200)->assertJson(['result']);
    }
}
