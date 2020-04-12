<?php

namespace Happypixels\Shopr\Tests\Support\Traits;

use Happypixels\Shopr\Exceptions\PaymentFailedException;
use Happypixels\Shopr\PaymentProviders\Stripe;

trait InteractsWithPaymentProviders
{
    protected $successfulPaymentResponse = [
        'success' => true,
        'transaction_reference' => 'the-reference',
        'transaction_id' => 'the-id',
        'payment_status' => 'pending_confirmation',
    ];

    protected $redirectPaymentResponse = [
        'success' => false,
        'transaction_reference' => 'the-reference',
        'redirect' => 'the-redirect-url',
        'payment_status' => 'pending',
    ];

    public function mockPaymentProvider($class)
    {
        $mock = $this->mock($class);
        $this->app->instance($class, $mock);

        // Default expectations.
        $mock
            ->shouldReceive('initialize')->once()->andReturnSelf()
            ->shouldReceive('handleRequest')->once()->andReturnSelf();

        return $mock;
    }

    public function mockSuccessfulPayment()
    {
        $this->mockPaymentProvider(Stripe::class)->shouldReceive('payForCart')->once()->andReturn($this->successfulPaymentResponse);
    }

    public function mockRedirectPayment()
    {
        $this->mockPaymentProvider(Stripe::class)->shouldReceive('payForCart')->once()->andReturn($this->redirectPaymentResponse);
    }

    public function mockFailedPayment($exception = PaymentFailedException::class)
    {
        $this->mockPaymentProvider(Stripe::class)
            ->shouldReceive('payForCart')
            ->once()
            ->andThrow(new $exception('Insufficient funds'));
    }
}
