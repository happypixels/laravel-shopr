<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Happypixels\Shopr\Cart\Cart;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class CartControllerTest extends TestCase
{
    /** @test */
    public function cart_summary()
    {
        $cart = app(Cart::class);

        $response = $this->json('GET', 'api/shopr/cart')
            ->assertStatus(200)
            ->assertJsonFragment($cart->summary());
    }

    /** @test */
    public function cart_count()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 2);

        $this->json('GET', 'api/shopr/cart/count')
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 2]);
    }

    /** @test */
    public function it_clears_the_cart()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), $model->id, 1);

        Event::fake();

        $this->json('DELETE', 'api/shopr/cart')
            ->assertStatus(200)
            ->assertJsonFragment(['count' => 0]);

        $this->assertEquals(0, $cart->items()->count());

        Event::assertDispatched('shopr.cart.cleared');
    }
}
