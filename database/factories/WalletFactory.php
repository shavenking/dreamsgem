<?php

use Faker\Generator as Faker;

$factory->define(App\Wallet::class, function (Faker $faker) {
    return [
        'gem' => $faker->randomDigit,
        'amount' => "{$faker->numberBetween(0, 10000)}.{$faker->numberBetween(0, 9)}",
    ];
});
