<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    private array $services = [
        ['gender' => 'no-gender', 'title' => 'کوتاه کردن مو'],
        ['gender' => 'male', 'title' => 'اطلاح ریش'],
        ['gender' => 'male', 'title' => 'آماده کردن داماد'],
        ['gender' => 'female', 'title' => 'آرایش عروس'],
        ['gender' => 'female', 'title' => 'کاشت ناخون'],
        // ['gender' => '', 'title' => ''],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->services as $service) {
            Service::create($service);
        }
    }
}
