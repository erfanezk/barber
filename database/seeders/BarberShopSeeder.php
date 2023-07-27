<?php

namespace Database\Seeders;

use App\Models\BarberShop;
use App\Models\Province;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class BarberShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('fa_IR');

        $exampleLocation = '940.392030-202-3.5949299484';
        $prefixAddress = 'خوزستان، دزفول، ';
        // at least two barber shop in dezful (khozestan id=13, dezful id=183)
        BarberShop::create([
            'name' => 'barber shop 1',
            'phone_number' => $faker->unique()->phoneNumber(),
            'location' => $exampleLocation,
            'address' => $prefixAddress . $faker->streetAddress(),
            'gender' => $faker->randomElement(['male', 'female']),
            'start_time' => $faker->randomElement(['08:00','08:30','09:00']),
            'end_time' => $faker->randomElement(['21:00','21:30','22:00']),
            'province_id' => 13,
            'city_id' => 183,
            'owner_id' => 1,
        ]);
        BarberShop::create([
            'name' => 'barber shop 1',
            'phone_number' => $faker->unique()->phoneNumber(),
            'location' => $exampleLocation,
            'address' => $prefixAddress . $faker->streetAddress(),
            'gender' => $faker->randomElement(['male', 'female']),
            'start_time' => $faker->randomElement(['08:00','08:30','09:00']),
            'end_time' => $faker->randomElement(['21:00','21:30','22:00']),
            'province_id' => 13,
            'city_id' => 183,
            'owner_id' => 1,
        ]);

        // add two barber to first barbershop
        User::find(2)->update(['barber_shop_id' => 1]);
        User::find(3)->update(['barber_shop_id' => 1]);

        for ($i = 0; $i < 18; $i++) {
            $randomProvince = Province::inRandomOrder()->first();
            $randomProvinceCity = $randomProvince->cities()->inRandomOrder()->first();

            BarberShop::create([
                'name' => $faker->company(),
                'phone_number' => $faker->phoneNumber(),
                'location' => $exampleLocation,
                'address' => $faker->address(),
                'gender' => $faker->randomElement(['male', 'female']),
                'start_time' => $faker->randomElement(['08:00','08:30','09:00']),
                'end_time' => $faker->randomElement(['21:00','21:30','22:00']),
                'province_id' => $randomProvince->id,
                'city_id' => $randomProvinceCity->id,
                'owner_id' => 1,
            ]);
        }
    }
}
