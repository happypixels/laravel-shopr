<?php

namespace Happypixels\Shopr\Tests\Http\Cart;

use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\Support\Traits\InteractsWithPaymentProviders;
use Happypixels\Shopr\Tests\TestCase;

class CheckoutHttpTest extends TestCase
{
    use InteractsWithPaymentProviders;

    /** @test */
    public function it_validates_the_post_data()
    {
        $this->json('POST', '/api/shopr/cart/checkout')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'gateway', 'first_name', 'last_name']);
    }

    /** @test */
    public function it_returns_400_if_cart_is_empty()
    {
        $this->json('POST', '/api/shopr/cart/checkout', [
            'email' => 'test@example.com',
            'gateway' => 'stripe',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ])
        ->assertStatus(400)
        ->assertJsonFragment(['message' => 'Your cart is empty.']);
    }

    /** @test */
    public function it_returns_422_if_gateway_is_invalid()
    {
        Cart::add(TestShoppable::first());

        $this->json('POST', '/api/shopr/cart/checkout', [
            'email'      => 'test@example.com',
            'gateway'    => 'invalid',
            'first_name' => 'Testy',
            'last_name'  => 'McTestface',
        ])
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'Invalid payment gateway.']);
    }

    /** @test */
    public function it_runs_the_checkout_method_with_the_provided_gateway_and_data()
    {
        Cart::add(TestShoppable::first());

        $data = [
            'email' => 'test@example.com',
            'gateway' => 'Stripe',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ];

        Cart::shouldReceive('checkout')->once()->with('Stripe', $data);

        $this->json('POST', '/api/shopr/cart/checkout', $data);
    }

    /** @test */
    public function it_returns_payment_redirect_info_if_payment_response_is_a_redirect()
    {
        $this->mockRedirectPayment('the-redirect-url');

        Cart::add(TestShoppable::first());

        $this->json('POST', '/api/shopr/cart/checkout', [
            'email' => 'test@example.com',
            'gateway' => 'Stripe',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ])->assertStatus(200)->assertJsonFragment(['redirect' => 'the-redirect-url']);
    }

    /** @test */
    public function it_returns_success_response_if_successful()
    {
        $this->mockSuccessfulPayment();

        Cart::add(TestShoppable::first());

        $this->json('POST', '/api/shopr/cart/checkout', [
            'email' => 'test@example.com',
            'gateway' => 'Stripe',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ])->assertStatus(201)->assertJsonFragment([
            'token' => Order::first()->token,
        ]);
    }
}
