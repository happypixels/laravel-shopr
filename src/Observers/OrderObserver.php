<?php

namespace Happypixels\Shopr\Observers;

use Happypixels\Shopr\Models\Order;
use Illuminate\Support\Facades\Mail;
use Happypixels\Shopr\Mails\OrderCreatedAdmins;
use Happypixels\Shopr\Mails\OrderCreatedCustomer;

class OrderObserver
{
    /**
     * Handle to the Order "created" event.
     *
     * @param  \Happypixels\Shopr\Models\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        if (config('shopr.mail.customer.order_placed.enabled') !== false && $order->email) {
            Mail::to($order->email)->queue(new OrderCreatedCustomer($order));
        }

        if (config('shopr.mail.admins.order_placed.enabled') !== false && ! empty(config('shopr.admin_emails'))) {
            Mail::to(config('shopr.admin_emails'))->queue(new OrderCreatedAdmins($order));
        }
    }

    /**
     * Handle the Order "updated" event.
     *
     * @param  \Happypixels\Shopr\Models\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     *
     * @param  \Happypixels\Shopr\Models\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        //
    }
}
