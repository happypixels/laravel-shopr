<?php

namespace Happypixels\Shopr\Tests\Feature\Mails;

use Happypixels\Shopr\Mails\OrderCreatedAdmins;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

class OrderCreatedAdminsMailTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = factory(Order::class)->create();
        $this->order->items()->create([
            'shoppable_type' => TestShoppable::class,
            'shoppable_id' => TestShoppable::first()->id,
            'title' => 'Test product',
        ]);
    }

    /** @test */
    public function it_sends_email_to_admins_when_order_is_created()
    {
        config(['shopr.admin_emails' => ['test@example.org', 'test2@example.org']]);

        Mail::fake();

        event('shopr.orders.confirmed', $this->order);

        Mail::assertQueued(OrderCreatedAdmins::class, function ($message) {
            $message->build();

            return
                $message->order->id === $this->order->id &&
                $message->subject === 'A new order has been placed!' &&
                $message->hasTo('test@example.org') &&
                $message->hasTo('test2@example.org') &&
                $message->view === 'shopr::mails.defaults.order-created-admins';
        });
    }

    /** @test */
    public function it_doesnt_send_email_to_admins_if_disabled_or_no_emails_are_provided()
    {
        Mail::fake();

        config(['shopr.admin_emails' => []]);

        event('shopr.orders.confirmed', $this->order);

        Mail::assertNotQueued(OrderCreatedAdmins::class);

        config(['shopr.admin_emails' => ['test@example.org', 'test2@example.org']]);
        config(['shopr.mail.admins.order_placed.enabled' => false]);

        event('shopr.orders.confirmed', $this->order);
        Mail::assertNotQueued(OrderCreatedAdmins::class);
    }

    /** @test */
    public function it_uses_selected_view()
    {
        config(['shopr.admin_emails' => ['test@example.org', 'test2@example.org']]);
        config(['shopr.mail.admins.order_placed.template' => 'test-view']);

        Mail::fake();

        event('shopr.orders.confirmed', $this->order);

        Mail::assertQueued(OrderCreatedAdmins::class, function ($message) {
            $message->build();

            return $message->view === 'test-view';
        });
    }

    /** @test */
    public function it_uses_selected_subject()
    {
        config(['shopr.mail.admins.order_placed.subject' => 'Test subject']);
        config(['shopr.admin_emails' => ['test@example.org', 'test2@example.org']]);

        Mail::fake();

        event('shopr.orders.confirmed', $this->order);

        Mail::assertQueued(OrderCreatedAdmins::class, function ($message) {
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

        Mail::assertQueued(OrderCreatedAdmins::class, function ($message) {
            $message->build();

            $shoppable = $this->order->items->first()->shoppable;

            return
                $shoppable !== null &&
                $shoppable->title === 'Test product';
        });
    }
}
