<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Laravel\DataTables\Tests\Models\User;
use Laravel\DataTables\Tests\Models\Product;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'stock' => $faker->numberBetween(10, 100),
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
    ];
});
