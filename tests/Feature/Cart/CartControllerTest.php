<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\TestCase;

class CartControllerTest extends TestCase
{
    /** @test */
    public function cart_summary()
    {
        Cart::shouldReceive('get')->once()->andReturn(['result']);

        $this->json('GET', 'api/shopr/cart')
            ->assertStatus(200)
            ->assertJson(['result']);
    }

    /** @test */
    public function cart_count()
    {
        Cart::shouldReceive('count')->once()->andReturn(123);

        $this->json('GET', 'api/shopr/cart/count')
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 123]);
    }

    /** @test */
    public function clear_cart()
    {
        Cart::shouldReceive('clear')->once();
        Cart::shouldReceive('get')->once()->andReturn(['result']);

        $this->json('DELETE', 'api/shopr/cart')
            ->assertStatus(200)
            ->assertJsonFragment(['result']);
    }
}
