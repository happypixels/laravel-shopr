<?php

namespace Happypixels\Shopr\Tests\Feature\Mails;

use Happypixels\Shopr\Mails\OrderCreatedCustomer;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class OrderCreatedCustomerMailTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = factory(Order::class)->create(['email' => 'test@example.org']);
        $this->order->items()->create([
            'shoppable_type' => TestShoppable::class,
            'shoppable_id' => TestShoppable::first()->id,
            'title' => 'Test product',
        ]);
    }

    /** @test */
    public function it_sends_email_to_customer_when_order_is_confirmed()
    {
        Mail::fake();

        event('shopr.orders.confirmed', $this->order);

        Mail::assertQueued(OrderCreatedCustomer::class, function ($message) {
            $message->build();

            return
                $message->order->id === $this->order->id &&
                $message->subject === 'Thank you for your order!' &&
                $message->hasTo($this->order->email) &&
                $message->view === 'shopr::mails.defaults.order-created-customer';
        });
    }

    /** @test */
    public function it_doesnt_send_email_to_customer_if_disabled()
    {
        config(['shopr.mail.customer.order_placed.enabled' => false]);

        Mail::fake();

        event('shopr.orders.confirmed', $this->order);

        Mail::assertNotQueued(OrderCreatedCustomer::class);
    }

    /** @test */
    public function it_uses_selected_customer_view()
    {
        config(['shopr.mail.customer.order_placed.template' => 'test-view']);

        Mail::fake();

        event('shopr.orders.confirmed', $this->order);

        Mail::assertQueued(OrderCreatedCustomer::class, function ($message) {
            $message->build();

            return $message->view === 'test-view';
        });
    }

    /** @test */
    public function it_uses_selected_customer_subject()
    {
        config(['shopr.mail.customer.order_placed.subject' => 'Test subject']);

        Mail::fake();

        event('shopr.orders.confirmed', $this->order);

        Mail::assertQueued(OrderCreatedCustomer::class, function ($message) {
            $message->build();

            return $message->subject === 'Test subject';
        });
    }

    /** @test */
    public function item_shoppables_are_loaded()
    {
        config(['shopr.admin_emails' => ['test@example.org']]);

        Mail::fake();

        event('shopr.orders.confirmed', $this->order);

        Mail::assertQueued(OrderCreatedCustomer::class, function ($message) {
            $message->build();

            $shoppable = $this->order->items->first()->shoppable;

            return
                $shoppable !== null &&
                $shoppable->title === 'Test product';
        });
    }
}
