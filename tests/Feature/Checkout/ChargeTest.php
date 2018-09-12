<?php

namespace Happypixels\Shopr\Tests\Feature\Checkout;

use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Happypixels\Shopr\Contracts\Cart;

class ChargeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_the_post_data()
    {
        $this->json('POST', '/api/shopr/checkout/charge')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'gateway', 'first_name', 'last_name']);
    }

    /** @test */
    public function it_returns_400_if_cart_is_empty()
    {
        $this->json('POST', '/api/shopr/checkout/charge', [
            'email'      => 'test@example.com',
            'gateway'    => 'stripe',
            'first_name' => 'Testy',
            'last_name'  => 'McTestface'
        ])
            ->assertStatus(400)
            ->assertJsonFragment(['message' => 'Your cart is empty.']);
    }

    /** @test */
    public function it_converts_the_cart_to_an_order()
    {
    }

    /** @test */
    public function it_clears_the_cart()
    {
    }

    /** @test */
    public function it_requires_the_gateway_to_be_available()
    {
    }

    /** @test */
    public function it_updates_the_order_status_if_purchase_is_successful()
    {
    }

    /** @test */
    public function it_returns_the_error_message_if_the_purchase_is_unsuccessful()
    {
    }
}
