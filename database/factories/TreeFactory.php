<?php

use Faker\Generator as Faker;

$factory->define(App\Tree::class, function (Faker $faker) {
    return [
        'user_id' => factory(\App\User::class)->create()->id,
        'capacity' => 0,
        'progress' => '0.0',
    ];
});

$factory->state(App\Tree::class, 'capacity_available', function (Faker $faker) {
    return [
        'capacity' => $faker->numberBetween(1, 90),
    ];
});
