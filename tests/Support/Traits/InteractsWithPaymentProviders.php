<?php

namespace Happypixels\Shopr\Tests\Support\Traits;

use Happypixels\Shopr\Exceptions\PaymentFailedException;
use Happypixels\Shopr\PaymentProviders\RedirectCheckoutResponse;
use Happypixels\Shopr\PaymentProviders\Stripe;
use Happypixels\Shopr\PaymentProviders\SuccessfulCheckoutResponse;

trait InteractsWithPaymentProviders
{
    public function mockPaymentProvider($class)
    {
        $mock = $this->mock($class);
        $this->app->instance($class, $mock);

        $mock
            ->shouldReceive('initialize')->once()->andReturnSelf()
            ->shouldReceive('handleRequest')->once()->andReturnSelf();

        return $mock;
    }

    public function mockSuccessfulPayment()
    {
        $this->mockPaymentProvider(Stripe::class)->shouldReceive('payForCart')->once()->andReturn(
            new SuccessfulCheckoutResponse('the-reference')
        );
    }

    public function mockRedirectPayment($redirectUrl = 'the-redirect-url')
    {
        $this->mockPaymentProvider(Stripe::class)->shouldReceive('payForCart')->once()->andReturn(
            new RedirectCheckoutResponse('the-reference', $redirectUrl)
        );
    }

    public function mockFailedPayment($exception = PaymentFailedException::class, $message = 'Insufficient funds')
    {
        $this->mockPaymentProvider(Stripe::class)
            ->shouldReceive('payForCart')
            ->once()
            ->andThrow(new $exception($message));
    }
}
