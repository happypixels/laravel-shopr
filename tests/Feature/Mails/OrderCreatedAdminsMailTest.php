<?php

namespace Happypixels\Shopr\Tests\Feature\Mails;

use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Contracts\Cart;
use Illuminate\Support\Facades\Mail;
use Happypixels\Shopr\Mails\OrderCreatedAdmins;
use Illuminate\Support\Facades\Event;

class OrderCreatedAdminsMailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_email_to_admins_when_order_is_created()
    {
        config(['shopr.admin_emails' => ['test@example.org', 'test2@example.org']]);

        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertQueued(OrderCreatedAdmins::class, function ($message) use ($order) {
            $message->build();

            return (
                $message->order->id === $order->id &&
                $message->subject === 'A new order has been placed!' &&
                $message->hasTo('test@example.org') &&
                $message->hasTo('test2@example.org') &&
                $message->view === 'shopr::mails.defaults.order-created-admins'
            );
        });
    }

    /** @test */
    public function it_doesnt_send_email_to_admins_if_disabled_or_no_emails_are_provided()
    {
        Mail::fake();

        config(['shopr.admin_emails' => []]);

        $order = $this->createTestOrder();

        Mail::assertNotQueued(OrderCreatedAdmins::class);

        config(['shopr.admin_emails' => ['test@example.org', 'test2@example.org']]);
        config(['shopr.mail.admins.order_placed.enabled' => false]);

        $order = $this->createTestOrder();
        Mail::assertNotQueued(OrderCreatedAdmins::class);
    }

    /** @test */
    public function it_uses_selected_view()
    {
        config(['shopr.admin_emails' => ['test@example.org', 'test2@example.org']]);
        config(['shopr.mail.admins.order_placed.template' => 'test-view']);

        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertQueued(OrderCreatedAdmins::class, function ($message) use ($order) {
            $message->build();

            return ($message->view === 'test-view');
        });
    }

    /** @test */
    public function it_uses_selected_subject()
    {
        config(['shopr.mail.admins.order_placed.subject' => 'Test subject']);
        config(['shopr.admin_emails' => ['test@example.org', 'test2@example.org']]);

        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertQueued(OrderCreatedAdmins::class, function ($message) use ($order) {
            $message->build();

            return ($message->subject === 'Test subject');
        });
    }

    /** @test */
    public function item_shoppables_are_loaded()
    {
        config(['shopr.admin_emails' => ['test@example.org']]);

        Mail::fake();

        $order = $this->createTestOrder();

        Mail::assertQueued(OrderCreatedAdmins::class, function ($message) use ($order) {
            $message->build();

            $shoppable = $order->items->first()->shoppable;

            return (
                $shoppable !== null &&
                $shoppable->title === 'Test product'
            );
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

        Event::fire('shopr.orders.created', $order);

        return $order;
    }
}
