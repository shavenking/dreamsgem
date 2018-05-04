<?php

use Faker\Generator as Faker;

$factory->define(\App\Dragon::class, function (Faker $faker) {
    return [
        'owner_id' => factory(\App\User::class)->create()->id,
        'user_id' => factory(\App\User::class)->create()->id,
    ];
});
