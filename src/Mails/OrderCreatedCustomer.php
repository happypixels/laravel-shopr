<?php

namespace Happypixels\Shopr\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Happypixels\Shopr\Models\Order;

class OrderCreatedCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public $subject = 'Thank you for your order!';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $order->load('items');

        $this->order = $order;

        if (config('shopr.mail.customer.order_placed.subject') !== null) {
            $this->subject = config('shopr.mail.customer.order_placed.subject');
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = config('shopr.mail.customer.order_placed.template') ?: 'shopr::mails.defaults.order-created-customer';

        return $this->view($view);
    }
}
