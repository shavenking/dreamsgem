<?php

use Faker\Generator as Faker;

$factory->define(App\Tree::class, function (Faker $faker) {
    return [
        'user_id' => factory(\App\User::class)->create()->id,
        'remain' => 90,
        'capacity' => 90,
        'progress' => '0.0',
    ];
});
