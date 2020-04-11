<?php

namespace Happypixels\Shopr\Tests\Feature\Money;

use Happypixels\Shopr\Cart\Cart;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class PriceFormattingTest extends TestCase
{
    /** @test */
    public function it_formats_order_amounts()
    {
        config(['shopr.currency' => 'USD']);

        $order = new Order;
        $order->total = 100;

        $this->assertEquals('$100.00', $order->total_formatted);
        $this->assertEquals('$0.00', $order->sub_total_formatted);
        $this->assertEquals('$0.00', $order->tax_formatted);
    }

    /** @test */
    public function it_formats_order_item_amounts()
    {
        config(['shopr.currency' => 'USD']);

        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1, 2);

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

        $cart->convertToOrder('stripe', $userData);

        $order = Order::with('items')->first();

        $this->assertEquals('$500.00', $order->items->first()->price_formatted);
        $this->assertEquals('$1,000.00', $order->items->first()->total_formatted);
    }

    /** @test */
    public function it_formats_cart_summary_amounts()
    {
        config(['shopr.currency' => 'USD']);

        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1, 1);
        $summary = $cart->summary();

        $this->assertEquals('$500.00', $summary['sub_total_formatted']);
        $this->assertEquals('$0.00', $summary['tax_total_formatted']);
        $this->assertEquals('$500.00', $summary['total_formatted']);
    }

    /** @test */
    public function it_formats_cart_item_amounts()
    {
        config(['shopr.currency' => 'USD']);

        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1, 1);
        $items = $cart->items();

        $this->assertEquals('$500.00', $items->first()->price_formatted);
    }
}
