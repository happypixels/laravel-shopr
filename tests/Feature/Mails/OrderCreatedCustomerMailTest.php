<?php

namespace Happypixels\Shopr\Tests\Feature\Mails;

use Happypixels\Shopr\Cart\Cart;
use Illuminate\Support\Facades\Mail;
use Happypixels\Shopr\Tests\TestCase;
use Happypixels\Shopr\Mails\OrderCreatedCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class OrderCreatedCustomerMailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_email_to_customer_when_order_is_confirmed()
    {
        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertQueued(OrderCreatedCustomer::class, function ($message) use ($order) {
            $message->build();

            return
                $message->order->id === $order->id &&
                $message->subject === 'Thank you for your order!' &&
                $message->hasTo($order->email) &&
                $message->view === 'shopr::mails.defaults.order-created-customer';
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

            return $message->view === 'test-view';
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

            return $message->subject === 'Test subject';
        });
    }

    /** @test */
    public function item_shoppables_are_loaded()
    {
        config(['shopr.admin_emails' => ['test@example.org']]);

        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertQueued(OrderCreatedCustomer::class, function ($message) use ($order) {
            $message->build();

            $shoppable = $order->items->first()->shoppable;

            return
                $shoppable !== null &&
                $shoppable->title === 'Test product';
        });
    }

    private function createTestOrder()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1, 1);

        $data = [
            'payment_status' => 'paid',
            'email'      => 'test@example.com',
            'first_name' => 'Testy',
            'last_name'  => 'McTestface',
            'phone'      => '111222333',
            'address'    => 'Street 1',
            'zipcode'    => '12312',
            'city'       => 'New York',
            'country'    => 'US',
        ];

        $order = $cart->convertToOrder('stripe', $data);

        event('shopr.orders.created', $order);

        return $order;
    }
}
