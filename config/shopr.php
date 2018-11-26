<?php

return [
    /**
     * View templates for the necessary default views.
     */
    'templates' => [
        'cart'               => '',
        'checkout'           => '',
        'order-confirmation' => '',
    ],

    /**
     * The default currency. This will affect all money formatting.
     */
    'currency' => 'USD',

    /**
     * The tax percentage.
     */
    'tax' => 0,

    /**
     * Email addresses to the administrators. These will receive the
     * emails defined in the 'emails.admins' key below.
     */
    'admin_emails' => [],

    /**
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
        
        /**
         * The validation rules for adding a discount coupon to the cart.
         * You may remove or add rules as you'd like, this is simply a common suggestion.
         */
        'validation_rules' => [
            new Happypixels\Shopr\Rules\Cart\CartNotEmpty,
            new Happypixels\Shopr\Rules\Discounts\OnlyOneCouponPerOrder,
            new Happypixels\Shopr\Rules\Discounts\CouponHasNotBeenApplied,
            new Happypixels\Shopr\Rules\Discounts\CouponExists,
            new Happypixels\Shopr\Rules\Discounts\DateIsWithinCouponTimespan,
        ]
    ],

    /**
     * The available payment gateways.
     */
    'gateways' => [
        'stripe' => [
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
            'api_key'         => env('STRIPE_SECRET_KEY', '')
        ]
    ]
];
