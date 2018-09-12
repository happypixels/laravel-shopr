<?php

namespace Happypixels\Shopr\Tests\Feature\Mails;

use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Contracts\Cart;
use Illuminate\Support\Facades\Mail;
use Happypixels\Shopr\Mails\OrderCreatedCustomer;
use Illuminate\Support\Facades\Event;

class OrderCreatedCustomerMailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_email_to_customer_when_order_is_created()
    {
        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertQueued(OrderCreatedCustomer::class, function ($message) use ($order) {
            $message->build();

            return (
                $message->order->id === $order->id &&
                $message->subject === 'Thank you for your order!' &&
                $message->hasTo($order->email) &&
                $message->view === 'shopr::mails.defaults.order-created-customer'
            );
        });
    }

    /** @test */
    public function it_doesnt_send_email_to_customer_if_disabled()
    {
        config(['shopr.mail.customer.order_placed.enabled' => false]);

        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertNotQueued(OrderCreatedCustomer::class);
    }

    /** @test */
    public function it_uses_selected_customer_view()
    {
        config(['shopr.mail.customer.order_placed.template' => 'test-view']);

        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertQueued(OrderCreatedCustomer::class, function ($message) use ($order) {
            $message->build();

            return ($message->view === 'test-view');
        });
    }

    /** @test */
    public function it_uses_selected_customer_subject()
    {
        config(['shopr.mail.customer.order_placed.subject' => 'Test subject']);

        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertQueued(OrderCreatedCustomer::class, function ($message) use ($order) {
            $message->build();

            return ($message->subject === 'Test subject');
        });
    }

    private function createTestOrder()
    {
        $cart  = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1, 1);

        $userData = [
            'email'      => 'test@example.com',
            'first_name' => 'Testy',
            'last_name'  => 'McTestface',
            'phone'      => '111222333',
            'address'    => 'Street 1',
            'zipcode'    => '12312',
            'city'       => 'New York',
            'country'    => 'US',
        ];

        $order = $cart->convertToOrder('stripe', $userData);

        Event::fire('shopr.order.created', $order);

        return $order;
    }
}
