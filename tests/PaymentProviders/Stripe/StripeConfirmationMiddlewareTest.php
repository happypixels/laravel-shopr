<?php

namespace Happypixels\Shopr\Tests\PaymentProviders\Stripe;

use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Tests\TestCase;

class StripeConfirmationMiddlewareTest extends TestCase
{
    /** @test */
    public function it_redirects_if_token_is_missing()
    {
        $this->get('/order-confirmation?gateway=Stripe')->assertRedirect('/');
    }

    /** @test */
    public function it_redirects_if_gateway_is_missing()
    {
        $this->get('/order-confirmation?token=123')->assertRedirect('/');
    }

    /** @test */
    public function it_redirects_if_order_doesnt_exist()
    {
        $this->get('/order-confirmation?token=123&gateway=Stripe')->assertRedirect('/');
    }

    /** @test */
    public function it_redirects_if_the_order_is_not_paid()
    {
        $order = factory(Order::class)->create([
            'payment_status' => 'pending',
            'payment_gateway' => 'Stripe'
        ]);

        $this->get('/order-confirmation?token='.$order->token.'&gateway=Stripe')->assertRedirect('/');
    }

    /** @test */
    public function it_allows_the_request_if_paid_order_exists()
    {
        $order = factory(Order::class)->create([
            'payment_status' => 'paid',
            'payment_gateway' => 'Stripe'
        ]);
        
        $response = $this->get('/order-confirmation?token='.$order->token.'&gateway=Stripe')->assertStatus(500);

        // The middleware should not interfere, so an attempt to load the non-existent view should be made.
        $this->assertEquals('View [test] not found.', $response->exception->getMessage());
    }
}
