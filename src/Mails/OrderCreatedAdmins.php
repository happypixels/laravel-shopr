<?php

namespace Happypixels\Shopr\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Happypixels\Shopr\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderCreatedAdmins extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public $subject = 'A new order has been placed!';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $order->load('items');

        $this->order = $order;

        if (config('shopr.mail.admins.order_placed.subject') !== null) {
            $this->subject = config('shopr.mail.admins.order_placed.subject');
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = config('shopr.mail.admins.order_placed.template') ?: 'shopr::mails.defaults.order-created-admins';

        return $this->view($view);
    }
}
