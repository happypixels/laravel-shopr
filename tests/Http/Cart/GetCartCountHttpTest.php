<?php

namespace Happypixels\Shopr\Tests\Http\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;

class GetCartCountHttpTest extends TestCase
{
    /** @test */
    public function it_returns_the_cart_summary()
    {
        $this->withoutExceptionHandling();

        Cart::shouldReceive('count')->once()->andReturn(123);

        $this->json('GET', 'api/shopr/cart/count')->assertStatus(200)->assertJson(['count' => 123]);
    }
}
