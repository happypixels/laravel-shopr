<?php

namespace Happypixels\Shopr\Tests\PaymentProviders\KlarnaCheckout;

use Happypixels\Shopr\Tests\TestCase;

class KlarnaCheckoutConfirmationMiddlewareTest extends TestCase
{
    /** @test */
    public function it_redirects_if_token_is_missing()
    {
        $this->get('/order-confirmation?gateway=KlarnaCheckout')->assertRedirect('/');
    }

    /** @test */
    public function it_redirects_if_gateway_is_missing()
    {
        $this->get('/order-confirmation?token=123')->assertRedirect('/');
    }

    /** @test */
    public function it_allows_the_request_if_all_parameters_exist()
    {
        $response = $this->get('/order-confirmation?token=6b1d5e22-9c8e-449a-a731-5b5a8fe31532&gateway=KlarnaCheckout')->assertStatus(500);

        // The middleware should not interfere, so an attempt to load the non-existent view should be made.
        $this->assertEquals('View [test] not found.', $response->exception->getMessage());
    }
}
