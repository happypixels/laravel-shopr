<?php

namespace Happypixels\Shopr\Tests\Unit\Cart;

use Happypixels\Shopr\Contracts\Cart;
use Happypixels\Shopr\Models\DiscountCoupon;
use Happypixels\Shopr\Models\Order;
use Happypixels\Shopr\Models\OrderItem;
use Happypixels\Shopr\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;

class ConvertCartToOrderUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_aborts_if_the_cart_is_empty()
    {
        $cart = app(Cart::class);

        $this->assertFalse($cart->convertToOrder('stripe'));
        $this->assertEquals(0, Order::count());
    }

    /** @test */
    public function it_creates_the_order()
    {
        config(['shopr.tax' => 25]);

        $cart = app(Cart::class);
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

        $cart->convertToOrder('stripe', $userData);

        $order = Order::first();
        $this->assertEquals('stripe', $order->payment_gateway);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals('pending', $order->delivery_status);
        $this->assertNotNull($order->token);
        $this->assertEquals(500, $order->total);
        $this->assertEquals(400, $order->sub_total);
        $this->assertEquals(100, $order->tax);
        $this->assertEquals($userData['email'], $order->email);
        $this->assertEquals($userData['first_name'], $order->first_name);
        $this->assertEquals($userData['last_name'], $order->last_name);
        $this->assertEquals($userData['phone'], $order->phone);
        $this->assertEquals($userData['address'], $order->address);
        $this->assertEquals($userData['zipcode'], $order->zipcode);
        $this->assertEquals($userData['city'], $order->city);
        $this->assertEquals($userData['country'], $order->country);
    }

    /** @test */
    public function it_creates_an_item_for_each_cart_row()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1, 1, ['color' => 'Green']);
        $cart->addItem(get_class($model), 1, 2, ['size' => 'Large']);

        $order = $cart->convertToOrder('stripe', []);
        $items = OrderItem::where('order_id', $order->id)->get();

        $this->assertEquals(2, $items->count());
        $this->assertEquals(1, $items->first()->quantity);
        $this->assertEquals(2, $items->last()->quantity);
        $this->assertEquals($model->title, $items->first()->title);
        $this->assertEquals('Green', $items->first()->options['color']);
        $this->assertEquals('Large', $items->last()->options['size']);
    }

    /** @test */
    public function it_stores_the_sub_items_in_the_database()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $item = $cart->addItem(get_class($model), $model->id, 1, [], [
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1, 'price' => 50],
            ['shoppable_type' => get_class($model), 'shoppable_id' => 1, 'options' => ['color' => 'Green']],
        ]);

        $order = $cart->convertToOrder('stripe', []);
        $items = OrderItem::with('children')->where('order_id', $order->id)->whereNull('parent_id')->get();

        $this->assertEquals(1, $items->count());
        $this->assertEquals(2, $items->first()->children->count());
        $this->assertEquals(1, $items->first()->children->first()->shoppable_id);
        $this->assertEquals(get_class($model), $items->first()->children->first()->shoppable_type);
        $this->assertEquals('Green', $items->first()->children->last()->options['color']);
        $this->assertEquals(50.00, $items->first()->children->first()->price);
        $this->assertEquals(500.00, $items->first()->children->last()->price);
    }

    /** @test */
    public function it_stores_discount_coupons_correctly()
    {
        config(['shopr.tax' => 25]);

        $discount = factory(DiscountCoupon::class)->create(['is_fixed' => true, 'value' => 300]);
        $cart = $this->addCartItem();
        $cart->addDiscount($discount);

        $order = $cart->convertToOrder('stripe', []);
        $items = OrderItem::where('order_id', $order->id)->get();

        // Tax is calculated on the reduced price.
        $this->assertEquals(200, $order->total);
        $this->assertEquals(160, $order->sub_total);
        $this->assertEquals(40, $order->tax);
        $this->assertEquals(2, $items->count());

        $coupon = $items->last();
        $this->assertEquals($discount->code, $coupon->title);
        $this->assertEquals(-300, $coupon->price);
        $this->assertEquals(1, $coupon->quantity);
    }

    /** @test */
    public function it_clears_the_cart()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1, 1, ['color' => 'Green']);
        $cart->addItem(get_class($model), 1, 2, ['size' => 'Large']);

        $this->assertFalse($cart->isEmpty());

        $order = $cart->convertToOrder('stripe', []);

        $this->assertTrue($cart->isEmpty());
    }

    /** @test */
    public function it_takes_price_overrides_into_account()
    {
        $cart = app(Cart::class);
        $model = TestShoppable::first();
        $cart->addItem(get_class($model), 1, 1, [], [], 100);

        $order = $cart->convertToOrder('stripe', []);

        $this->assertEquals(100, $order->total);
        $this->assertEquals(100, $order->items()->first()->price);
    }

    public function addCartItem()
    {
        $cart = app(Cart::class);
        $model = factory(TestShoppable::class)->create(['price' => 500]);
        $cart->addItem(get_class($model), $model->id, 1);

        return $cart;
    }
}
