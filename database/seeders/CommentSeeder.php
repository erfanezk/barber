<?php

namespace Database\Seeders;

use App\Models\Comment;
use Faker\Factory;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('fa_IR');

        for ($i = 0; $i < 200; $i++) {
            Comment::create([
                'text' => $faker->realText(),
                'score' => $faker->numberBetween(1, 5),
                'client_id' => 1,
                'barber_id' => null,
                'barber_shop_id' => $faker->numberBetween(1, 20), // we have 20 barbershop in barbershop seeder
            ]);
        }
    }
}
