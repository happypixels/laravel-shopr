<?php

return [
    /*
     * View templates for the necessary default views.
     */
    'templates' => [
        'cart' => '',
        'checkout' => '',
        'order-confirmation' => '',
    ],

    /*
     * The database models. You may swap these with your own models, just make sure they extend the
     * corresponding package model rather than the standard Eloquent model.
     */
    'models' => [
        'Order' => Happypixels\Shopr\Models\Order::class,
        'OrderItem' => Happypixels\Shopr\Models\OrderItem::class,
    ],

    /*
     * The default currency. This will affect all money formatting.
     */
    'currency' => 'USD',

    /*
     * The money formatter class. You may provide your own class here, just make sure it extends
     * the default Happypixels\Shopr\Money\Formatter class.
     */
    'money_formatter' => Happypixels\Shopr\Money\Formatter::class,

    /*
     * The tax percentage.
     */
    'tax' => 0,

    /*
     * Email addresses to the administrators. These will receive the
     * emails defined in the 'emails.admins' key below.
     */
    'admin_emails' => [],

    /*
     * Here you may define your own email views in order to fully customize their appearances.
     * Each email has access to the full Order model.
     *
     * Set options to 'null' to use default template or subject.
     * Set enabled to 'false' to disable the email.
     */
    'mail' => [
        'customer' => [
            'order_placed' => ['enabled' => true, 'template' => null, 'subject' => null],
        ],
        'admins' => [
            'order_placed' => ['enabled' => true, 'template' => null, 'subject' => null],
        ],
    ],

    'discount_coupons' => [

        /*
         * The validation rules for adding a discount coupon to the cart.
         * You may remove or add rules as you'd like, this is simply a common suggestion.
         */
        'validation_rules' => [
            Happypixels\Shopr\Rules\Cart\CartNotEmpty::class,
            Happypixels\Shopr\Rules\Discounts\OnlyOneCouponPerOrder::class,
            Happypixels\Shopr\Rules\Discounts\CouponHasNotBeenApplied::class,
            Happypixels\Shopr\Rules\Discounts\CouponExists::class,
            Happypixels\Shopr\Rules\Discounts\DateIsWithinCouponTimespan::class,
            Happypixels\Shopr\Rules\Discounts\CartValueAboveCouponLimit::class,
        ],
    ],

    /*
     * The available payment gateways.
     */
    'gateways' => [
        'stripe' => [
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
            'api_key' => env('STRIPE_SECRET_KEY', ''),
        ],
    ],

    /**
     * Configuration options for the optional REST API.
     */
    'rest_api' => [
        'enabled' => true,
        'prefix' => 'api/shopr',
        'middleware' => [
            // YourCustomMiddleware::class
        ],
    ],
];
