<?php

namespace Happypixels\Shopr\Tests\Unit\Responses;

use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\PaymentProviders\RedirectCheckoutResponse;
use Happypixels\Shopr\Tests\TestCase;

class RedirectCheckoutResponseUnitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->response = new RedirectCheckoutResponse('the-reference', 'redirect-url');
        $this->response->setOrder($this->order = factory(Order::class)->create([
            'token' => 'order-token',
        ]));
    }

    /** @test */
    public function it_has_the_correct_values()
    {
        $this->assertFalse($this->response->isSuccessful());
        $this->assertTrue($this->response->getOrder()->is($this->order));
        $this->assertTrue($this->response->requiresConfirmation());
        $this->assertEquals('pending', $this->response->getPaymentStatus());
        $this->assertEquals('the-reference', $this->response->getTransactionReference());
        $this->assertEquals('redirect-url', $this->response->getRedirectUrl());
    }

    /** @test */
    public function to_array_returns_the_redirect_url()
    {
        $this->assertEquals(['redirect' => 'redirect-url'], $this->response->toArray());
    }

    /** @test */
    public function to_json_returns_the_order_token()
    {
        $this->assertEquals(json_encode(['redirect' => 'redirect-url']), $this->response->toJson());
    }
}
