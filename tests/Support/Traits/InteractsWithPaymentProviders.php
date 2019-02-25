<?php

namespace Happypixels\Shopr\Tests\Support\Traits;

use Mockery;

trait InteractsWithPaymentProviders
{
    public function mockPaymentProvider($class)
    {
        $mock = Mockery::mock($class);
        $this->app->instance($class, $mock);

        // Default expectations.
        $mock
            ->shouldReceive('initialize')->once()->andReturnSelf()
            ->shouldReceive('handleRequest')->once()->andReturnSelf();

        return $mock;
    }
}
