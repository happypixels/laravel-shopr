<?php

namespace Happypixels\Shopr\Tests\Feature\Shoppable;

use Happypixels\Shopr\Cart\CartItem;
use Happypixels\Shopr\Facades\Cart;
use Happypixels\Shopr\Tests\Support\Models\TestShoppable;
use Happypixels\Shopr\Tests\TestCase;

class AddToCartTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->shoppable = TestShoppable::first();
    }

    /** @test */
    public function it_calls_the_cart_method_with_the_given_options()
    {
        $options = [
            'options' =>  ['color' => 'Green', 'size' => 'L'],
            'quantity' => 3,
            'price' => 400,
            'sub_items' => [],
        ];

        Cart::shouldReceive('add')->once()->with($this->shoppable, $options);

        $this->shoppable->addToCart($options);
    }

    /** @test */
    public function it_returns_the_cart_item()
    {
        $item = $this->shoppable->addToCart();

        $this->assertTrue($item instanceof CartItem);
    }
}
