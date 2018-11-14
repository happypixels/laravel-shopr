<?php

return [
    /**
     * View templates for the necessary default views.
     */
    'templates' => [
        'cart' => '',
        'checkout' => '',
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

    /**
     * The available payment gateways.
     */
    'gateways' => [
        'stripe' => [
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
            'api_key' => env('STRIPE_SECRET_KEY', '')
        ],

        'klarna_checkout' => [
            'username' => env('KLARNA_USERNAME', ''),
            'secret' => env('KLARNA_SECRET', ''),

            'checkout_url' => env('APP_URL').'/se/shop?sid={checkout.order.id}',
            'confirmation_url' => env('APP_URL').'/se/confirmation?token={checkout.order.id}&gateway=KlarnaCheckout',
            'terms_url' => env('APP_URL').'/se/shop/terms',
            'push_url' => env('APP_URL').'/shopr/webhooks/kco',
            'validation_url' => null,
        ]
    ]
];
