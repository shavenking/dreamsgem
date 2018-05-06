<?php

use Faker\Generator as Faker;

$factory->define(App\Wallet::class, function (Faker $faker) {
    return [
        'gem' => $faker->randomDigit,
        'amount' => $faker->randomFloat(10),
    ];
});
