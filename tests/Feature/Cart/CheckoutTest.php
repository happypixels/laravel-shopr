<?php

namespace Happypixels\Shopr\Tests\Feature\Cart;

use Exception;
use Happypixels\Shopr\Exceptions\CartEmptyException;
use Happypixels\Shopr\Exceptions\InvalidCheckoutDataException;
use Happypixels\Shopr\Exceptions\InvalidGatewayException;
use Happypixels\Shopr\Exceptions\PaymentFailedException;
use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\PaymentProviders\RedirectCheckoutResponse;
use Happypixels\Shopr\PaymentProviders\SuccessfulCheckoutResponse;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\Support\Traits\InteractsWithPaymentProviders;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class CheckoutTest extends TestCase
{
    use InteractsWithPaymentProviders;

    /** @test */
    public function it_validates_selected_payment_provider()
    {
        Cart::add(TestShoppable::first());

        try {
            Cart::checkout('Invalid Provider', [
                'first_name' => 'Test',
                'last_name' => 'Tester',
                'email' => 'test@example.org',
            ]);
        } catch (InvalidGatewayException $e) {
            $this->assertTrue(true, 'Works');
        } catch (Exception $e) {
            $this->fail('Checkout does not validate provider correctly.');
        }
    }

    /** @test */
    public function it_validates_required_customer_data()
    {
        Cart::add(TestShoppable::first());

        try {
            Cart::checkout('Stripe', []);
        } catch (InvalidCheckoutDataException $e) {
            $this->assertTrue(true, 'Works');
        } catch (Exception $e) {
            $this->fail('Checkout does not validate data correctly.');
        }
    }

    /** @test */
    public function it_throws_exception_if_cart_is_empty()
    {
        try {
            Cart::checkout('Stripe', [
                'first_name' => 'Test',
                'last_name' => 'Tester',
                'email' => 'test@example.org',
            ]);
        } catch (CartEmptyException $e) {
            $this->assertTrue(true, 'Works');
        } catch (Exception $e) {
            $this->fail('Checkout does not cart content correctly.');
        }
    }

    /** @test */
    public function it_does_not_convert_cart_to_an_order_if_payment_fails()
    {
        Event::fake();

        $this->mockFailedPayment(PaymentFailedException::class);

        Cart::add(TestShoppable::first());

        try {
            Cart::checkout('Stripe', [
                'first_name' => 'Test',
                'last_name' => 'Tester',
                'email' => 'test@example.org',
            ]);
        } catch (PaymentFailedException $e) {
            $this->assertTrue(true, 'Works');
        } catch (Exception $e) {
            $this->fail('Checkout does not cart content correctly.');
        }

        // Cart is untouched.
        $this->assertEquals(0, Order::count());
        $this->assertEquals(1, Cart::count());

        // No success-event fired.
        Event::assertNotDispatched('shopr.orders.created');
    }

    /** @test */
    public function it_creates_the_order_if_payment_is_successful()
    {
        Event::fake();

        $data = [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ];

        $this->mockSuccessfulPayment();

        Cart::add(TestShoppable::first());

        $cartSummary = Cart::get();

        $response = Cart::checkout('Stripe', $data);

        $this->assertTrue($response instanceof SuccessfulCheckoutResponse);

        $this->assertTrue($response->order instanceof Order);
        $this->assertEquals($data['email'], $response->order->email);
        $this->assertEquals($data['first_name'], $response->order->first_name);
        $this->assertEquals($data['last_name'], $response->order->last_name);
        $this->assertEquals('Stripe', $response->order->payment_gateway);
        $this->assertEquals('the-reference', $response->order->transaction_reference);
        $this->assertEquals('paid', $response->order->payment_status);
        $this->assertEquals($cartSummary['total'], $response->order->total);
        $this->assertEquals($cartSummary['sub_total'], $response->order->sub_total);
        $this->assertEquals($cartSummary['tax_total'], $response->order->tax);
        $this->assertNotNull($response->order->token);
    }

    /** @test */
    public function it_creates_a_pending_order_and_returns_redirect_response_in_case_of_redirect()
    {
        Event::fake();

        $this->mockRedirectPayment();

        Cart::add(TestShoppable::first(), ['price' => 100]);

        $response = Cart::checkout('Stripe', [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ]);

        $this->assertTrue($response instanceof RedirectCheckoutResponse);

        // The order is still created, but in a pending state.
        $order = Order::where('transaction_reference', $response->getTransactionReference())->first();
        $this->assertEquals('pending', $order->payment_status);

        // The event is still fired.
        Event::assertDispatched('shopr.orders.created', function ($event, $data) use ($order) {
            return $data->is($order);
        });
    }

    /** @test */
    public function it_stores_the_items()
    {
        $this->mockSuccessfulPayment();

        Cart::add($shoppable = TestShoppable::first());

        $response = Cart::checkout('Stripe', [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ]);
        $response->order->load('items');

        $this->assertEquals(1, $response->order->items->count());
        $this->assertEquals(TestShoppable::class, $response->order->items->first()->shoppable_type);
        $this->assertEquals($shoppable->id, $response->order->items->first()->shoppable_id);
        $this->assertEquals(1, $response->order->items->first()->quantity);
        $this->assertEquals('Test product', $response->order->items->first()->title);
        $this->assertEquals(500, $response->order->items->first()->price);
        $this->assertNull($response->order->items->first()->options);
    }

    /** @test */
    public function it_stores_the_sub_items()
    {
        $this->mockSuccessfulPayment();

        Cart::add(TestShoppable::first(), [
            'sub_items' => [
                ['shoppable' => $shoppable = TestShoppable::first(), 'options' => ['color' => 'Green']],
            ],
        ]);

        $response = Cart::checkout('Stripe', [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ]);
        $response->order->load('parentItems.children');

        $this->assertEquals(1, $response->order->parentItems->count());
        $this->assertEquals(1, $response->order->parentItems->first()->children->count());

        $subItem = $response->order->parentItems->first()->children->first();
        $this->assertEquals(TestShoppable::class, $subItem->shoppable_type);
        $this->assertEquals($shoppable->id, $subItem->shoppable_id);
        $this->assertEquals(1, $subItem->quantity);
        $this->assertEquals('Test product', $subItem->title);
        $this->assertEquals(500, $subItem->price);
        $this->assertEquals(['color' => 'Green'], $subItem->options);
    }

    /** @test */
    public function it_stores_discount_coupons_correctly()
    {
        config(['shopr.tax' => 25]);

        $this->mockSuccessfulPayment();

        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);

        Cart::add(TestShoppable::first());
        Cart::addDiscount($discount);

        $response = Cart::checkout('Stripe', [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ]);
        $response->order->load('items');

        // Tax is calculated on the reduced price.
        $this->assertEquals(200, $response->order->total);
        $this->assertEquals(160, $response->order->sub_total);
        $this->assertEquals(40, $response->order->tax);

        $this->assertEquals(2, $response->order->items->count());
        $this->assertEquals($discount->code, $response->order->items->last()->title);
        $this->assertEquals(-300, $response->order->items->last()->price);
        $this->assertEquals(1, $response->order->items->last()->quantity);
    }

    /** @test */
    public function it_clears_the_cart_if_order_is_paid()
    {
        $this->mockSuccessfulPayment();

        Cart::add(TestShoppable::first());

        $this->assertEquals(1, Cart::count());

        Cart::checkout('Stripe', [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ]);

        $this->assertEquals(0, Cart::count());
    }

    /** @test */
    public function it_takes_price_overrides_into_account()
    {
        $this->mockSuccessfulPayment();

        Cart::add(TestShoppable::first(), ['price' => 100]);

        $response = Cart::checkout('Stripe', [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ]);

        $this->assertEquals(100, $response->order->total);
        $this->assertEquals(100, $response->order->items->first()->price);
    }

    /** @test */
    public function it_fires_order_created_event()
    {
        Event::fake();

        $this->mockRedirectPayment();

        Cart::add(TestShoppable::first());

        Cart::checkout('Stripe', [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ]);

        $this->assertEquals(1, Order::count());

        Event::assertDispatched('shopr.orders.created', function ($event, $data) {
            return $data->is(Order::first());
        });

        Event::assertNotDispatched('shopr.orders.confirmed');
    }

    /** @test */
    public function if_successful_it_fires_both_created_and_confirmed_events()
    {
        Event::fake();

        $this->mockSuccessfulPayment();

        Cart::add(TestShoppable::first());

        $response = Cart::checkout('Stripe', [
            'email' => 'test@example.com',
            'first_name' => 'Testy',
            'last_name' => 'McTestface',
        ]);

        Event::assertDispatched('shopr.orders.created', function ($event, $data) use ($response) {
            return $data->is($response->order);
        });

        Event::assertDispatched('shopr.orders.confirmed', function ($event, $data) use ($response) {
            return $data->is($response->order);
        });
    }
}
