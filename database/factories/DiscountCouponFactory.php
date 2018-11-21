<?php

use Faker\Generator as Faker;

$factory->define(Happypixels\Shopr\Models\DiscountCoupon::class, function (Faker $faker) {
    return [
        'valid_from' => now()->subDays(5),
        'valid_until' => now()->addDays(5),
        'uses' => 0,
        'code' => str_random(10),
        'is_fixed' => false,
        'value' => 25,
    ];
});
