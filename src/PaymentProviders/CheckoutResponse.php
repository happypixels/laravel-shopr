<?php

namespace Happypixels\Shopr\PaymentProviders;

use Happypixels\Shopr\Models\Order;

abstract class CheckoutResponse
{
    /**
     * The created order.
     *
     * @var Happypixels\Shopr\Models\Order
     */
    public $order;

    /**
     * The payment transaction reference.
     *
     * @var string
     */
    public $transactionReference;

    /**
     * The redirect URL.
     *
     * @var string
     */
    public $redirectUrl;

    /**
     * Whether the payment requires additional confirmation.
     *
     * @return bool
     */
    abstract public function requiresConfirmation(): bool;

    /**
     * Whether the checkout was fully successful or not. Will be false if it requires confirmation.
     *
     * @return bool
     */
    abstract public function isSuccessful(): bool;

    /**
     * The status of the payment. Will be paid if successful, pending if it requires confirmation.
     *
     * @return string
     */
    abstract public function getPaymentStatus(): string;

    /**
     * Returns the order.
     *
     * @return Happypixels\Shopr\Models\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Returns the transaction reference.
     *
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->transactionReference;
    }

    /**
     * Adds the created order to the response.
     *
     * @param Order $order
     * @return void
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }
}
