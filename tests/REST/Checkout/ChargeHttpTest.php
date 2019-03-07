<?php

namespace Happypixels\Shopr\Tests\REST\Checkout;

use Happypixels\Shopr\Cart\Cart;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Happypixels\Shopr\PaymentProviders\Stripe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Happypixels\Shopr\Exceptions\PaymentFailedException;
use Happypixels\Shopr\Tests\Support\Traits\InteractsWithCart;
use Happypixels\Shopr\Tests\Support\Traits\InteractsWithPaymentProviders;

class ChargeHttpTest extends TestCase
{
    use RefreshDatabase, InteractsWithCart, InteractsWithPaymentProviders;

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
            'last_name'  => 'McTestface',
        ])
        ->assertStatus(400)
        ->assertJsonFragment(['message' => 'Your cart is empty.']);
    }

    /** @test */
    public function it_returns_422_if_gateway_is_invalid()
    {
        $this->addCartItem();

        $this->json('POST', '/api/shopr/checkout/charge', [
            'email'      => 'test@example.com',
            'gateway'    => 'invalid',
            'first_name' => 'Testy',
            'last_name'  => 'McTestface',
        ])
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'Invalid payment gateway.']);
    }

    /** @test */
    public function test_failed_payment()
    {
        Event::fake();

        $this->addCartItem();

        $this->mockPaymentProvider(Stripe::class)
            ->shouldReceive('payForCart')
            ->once()
            ->andThrow(new PaymentFailedException('Insufficient funds'));

        $this->json('POST', '/api/shopr/checkout/charge', [
            'email' => 'test@example.com',
            'gateway' => 'stripe',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ])->assertStatus(400)->assertJsonFragment([
            'message' => 'The payment failed.',
            'reason' => 'Insufficient funds',
        ]);

        // The cart is persisted and no order is created.
        $this->assertEquals(1, $this->getCartCount());
        $this->assertEquals(0, Order::count());

        Event::assertNotDispatched('shopr.orders.created');
    }

    /** @test */
    public function test_successful_payment()
    {
        Event::fake();

        $orderData = [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'success' => true,
            'last_name' => 'McTestface',
            'transaction_reference' => 'the-reference',
            'transaction_id' => 'the-id',
            'payment_status' => 'paid',
        ];

        // The cart receives the expected methods.
        $this->mockCart()
            ->shouldReceive('isEmpty')->once()->andReturn(false)
            ->shouldReceive('convertToOrder')->once()->with('stripe', $orderData)->andReturn(
                $order = factory(Order::class)->create()
            );

        $this->mockPaymentProvider(Stripe::class)->shouldReceive('payForCart')->once()->andReturn([
            'success' => true,
            'transaction_reference' => $orderData['transaction_reference'],
            'transaction_id' => $orderData['transaction_id'],
            'payment_status' => $orderData['payment_status'],
        ]);

        // The response is a 201 and it holds the token of the order.
        $response = $this->json('POST', '/api/shopr/checkout/charge', [
            'email' => $orderData['email'],
            'gateway' => 'stripe',
            'first_name' => $orderData['first_name'],
            'last_name' => $orderData['last_name'],
        ])->assertStatus(201)->assertJsonFragment(['token' => $order->token]);

        // The order created event is fired.
        Event::assertDispatched('shopr.orders.created', function ($event, $data) use ($order) {
            return serialize($order) === serialize($data);
        });
    }
}
