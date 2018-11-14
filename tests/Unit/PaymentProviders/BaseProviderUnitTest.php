<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Tests\Support\PaymentProviders\TestProvider;
use Happypixels\Shopr\Tests\TestCase;

class BaseProviderUnitTest extends TestCase
{
    /** @test */
    public function getProviderName()
    {
        $this->assertEquals('TestProvider', (new TestProvider)->getProviderName());
    }

    /** @test */
    public function getGatewayPath()
    {
        $this->assertEquals('TestProvider', (new TestProvider)->getGatewayPath());
    }

    /** @test */
    public function getConfigKey()
    {
        $this->assertEquals('test_provider', (new TestProvider)->getConfigKey());
    }
}
