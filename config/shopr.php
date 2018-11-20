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
     * URLs to the pages necessary for the checkout flow.
     * These pages are required by many payment providers.
     */
    'checkout_url' => env('APP_URL').'/checkout',
    'confirmation_url' => env('APP_URL').'/confirmation',
    'terms_url' => env('APP_URL').'/terms',

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

            // See "Locale & Country" on https://developers.klarna.com/api/#data-types for details.
            'store_locale' => 'en-us',
            'store_country' => 'us'
        ]
    ]
];
