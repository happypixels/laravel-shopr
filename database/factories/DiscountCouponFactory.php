<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Happypixels\Shopr\Models\DiscountCoupon::class, function (Faker $faker) {
    return [
        'valid_from' => now()->subDays(5),
        'valid_until' => now()->addDays(5),
        'uses' => 0,
        'code' => Str::random(10),
        'is_fixed' => false,
        'value' => 25,
    ];
});
