<?php

use Faker\Generator as Faker;

$factory->define(Happypixels\Shopr\Tests\Support\Models\TestShoppable::class, function (Faker $faker) {
    return [
        'title' => ucfirst($faker->word),
        'price' => $faker->numberBetween(1, 500),
    ];
});
