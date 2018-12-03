<?php

use Faker\Generator as Faker;

$factory->define(Happypixels\Shopr\Models\Order::class, function (Faker $faker) {
    return [
        'user_id' => null,
        'payment_status' => 'pending',
        'delivery_status' => 'pending',
        'token' => str_random(10),
        'payment_gateway' => 'Stripe',
        'transaction_id' => null,
        'transaction_reference' => null,
        'total' => $faker->numberBetween(0, 500),
        'sub_total' => $faker->numberBetween(0, 500),
        'tax' => $faker->numberBetween(0, 500),
        'email' => $faker->safeEmail,
        'phone' => null,
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'address' => null,
        'zipcode' => null,
        'city' => null,
        'country' => null,
    ];
});
