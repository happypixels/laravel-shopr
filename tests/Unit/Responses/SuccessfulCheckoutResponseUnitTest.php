<?php

namespace Happypixels\Shopr\Tests\Unit\Responses;

use Exception;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\PaymentProviders\SuccessfulCheckoutResponse;
use Happypixels\Shopr\Tests\TestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class SuccessfulCheckoutResponseUnitTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->response = new SuccessfulCheckoutResponse('the-reference');
        $this->response->setOrder($this->order = factory(Order::class)->create([
            'token' => 'order-token',
        ]));
    }

    /** @test */
    public function it_has_the_correct_values()
    {
        $this->assertTrue($this->response->isSuccessful());
        $this->assertTrue($this->response->getOrder()->is($this->order));
        $this->assertFalse($this->response->requiresConfirmation());
        $this->assertEquals('paid', $this->response->getPaymentStatus());
        $this->assertEquals('the-reference', $this->response->getTransactionReference());
    }

    /** @test */
    public function to_array_returns_the_order()
    {
        $this->assertEquals($this->order->toArray(), $this->response->toArray());
        $this->assertEquals([], (new SuccessfulCheckoutResponse('the-reference'))->toArray());
    }

    /** @test */
    public function to_json_returns_the_order_token()
    {
        $this->assertEquals(json_encode(['token' => 'order-token']), $this->response->toJson());
    }

    /** @test */
    public function to_json_includes_redirect_url_if_order_confirmation_template_is_set()
    {
        app('config')->set('shopr.templates.order-confirmation', 'test');

        try {
            $this->response->toJson();
        } catch (RouteNotFoundException $e) {
            $this->assertEquals(
                'Route [shopr.order-confirmation] not defined.',
                $e->getMessage(),
                'The redirect URL throws exception because the route is disabled.'
            );
        } catch (Exception $e) {
            $this->fail('Wrong exception thrown: '.$e->getMessage());
        }
    }
}
