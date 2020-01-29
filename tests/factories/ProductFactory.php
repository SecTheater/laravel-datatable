<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(\Laravel\DataTables\Tests\Models\Product::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'stock' => $faker->numberBetween(10, 100),
        'user_id' => function () {
            return factory(\Laravel\DataTables\Tests\Models\User::class)->create()->id;
        },
    ];
});
