<?php

namespace Happypixels\Shopr\Tests\Http\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;

class GetCartHttpTest extends TestCase
{
    /** @test */
    public function it_returns_the_cart_summary()
    {
        $this->withoutExceptionHandling();

        Cart::shouldReceive('get')->once()->andReturn(['result']);

        $this->json('GET', 'api/shopr/cart')->assertStatus(200)->assertJson(['result']);
    }
}
