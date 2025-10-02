<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 25) as $index) {
            Employee::create([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->optional()->phoneNumber,
                'position' => $faker->optional()->jobTitle,
                'salary' => $faker->randomFloat(2, 1000, 10000),
                'hired_at' => $faker->date('Y-m-d', 'now'),
                'status' => $faker->randomElement(['active', 'inactive']),
            ]);
        }
    }
}