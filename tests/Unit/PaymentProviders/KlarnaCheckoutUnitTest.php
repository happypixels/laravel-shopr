<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\PaymentProviders\KlarnaCheckout;
use Happypixels\Shopr\Tests\TestCase;

class KlarnaCheckoutUnitTest extends TestCase
{
    /** @test */
    public function getProviderName()
    {
        $this->assertEquals('KlarnaCheckout', (new KlarnaCheckout)->getProviderName());
    }

    /** @test */
    public function getGatewayPath()
    {
        $this->assertEquals('\MyOnlineStore\Omnipay\KlarnaCheckout\Gateway', (new KlarnaCheckout)->getGatewayPath());
    }

    /** @test */
    public function getConfigKey()
    {
        $this->assertEquals('klarna_checkout', (new KlarnaCheckout)->getConfigKey());
    }
}
