<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Address;
use Faker\Generator as Faker;

$factory->define(Address::class, function (Faker $faker) {
    return [
        'address' => $faker->address,
        'updated_at' => now(),
        'lat' => $faker->latitude(41.99,42),
        'lng' => $faker->longitude(21.4,21.46),
        'user_id'=>$faker->numberBetween(4,5)
    ];
});
