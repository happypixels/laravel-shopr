<?php

namespace Happypixels\Shopr\Traits\PaymentProviders;

trait HandlesProviderOrders
{
    /**
     * Determines whether the gateway needs to create orders in the provider system.
     *
     * @return boolean
     */
    public function createsProviderOrders()
    {
        return true;
    }

    /**
     * Creates an order in the provider system based on the current cart.
     *
     * @return mixed
     */
    abstract public function createProviderOrder();

    /**
     * Retrieves a confirmed order from the provider system and stores it in the database.
     *
     * @param  string $identifier
     * @return Happypixels\Shopr\Models\Order|false
     */
    abstract public function storeConfirmedProviderOrder($identifier);
}
