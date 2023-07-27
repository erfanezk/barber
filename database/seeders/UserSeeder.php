<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('fa_IR');

        // at least one client in DB
        User::create([
            'name' => 'client',
            'phone_number' => '09111111111',
            'password' => Hash::make('1234'),
            'role' => 'client',
            'gender' => 'male',
            'address' => $faker->address(),
        ]);

        // at least one+one barber in DB
        User::create([
            'name' => 'barber 1',
            'phone_number' => '09111111112',
            'password' => Hash::make('1234'),
            'role' => 'barber',
            'gender' => 'male',
            'address' => $faker->address(),
            // 'work_time' => '',
            'work_experience' => $faker->numberBetween(1350, 1402),
        ]);
        User::create([
            'name' => 'barber 2',
            'phone_number' => '09111111113',
            'password' => Hash::make('1234'),
            'role' => 'barber',
            'gender' => 'male',
            'address' => $faker->address(),
            // 'work_time' => '',
            'work_experience' => $faker->numberBetween(1350, 1402),
        ]);

        for ($i = 0; $i < 18; $i++) {
            User::create([
                'name' => $faker->name(),
                'phone_number' => $faker->unique()->phoneNumber(),
                'password' => Hash::make('1234'),
                'role' => $faker->randomElement(['barber', 'client']),
                'gender' => $faker->randomElement(['male', 'female']),
                'address' => $faker->address(),
                // 'work_time' => '',
                'work_experience' => $faker->numberBetween(1350, 1402),
            ]);
        }
    }
}
