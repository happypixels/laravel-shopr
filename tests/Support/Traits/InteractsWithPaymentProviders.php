<?php

namespace Happypixels\Shopr\Tests\Support\Traits;

trait InteractsWithPaymentProviders
{
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
}
