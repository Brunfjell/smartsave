<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnvironmentalDataTableSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('environmental_data')->insert([
                'user_id' => DB::table('users')->inRandomOrder()->first()->id, // Select a random existing user ID

                'timestamp' => now()->subHours(rand(0, 5)), // Random timestamp within the last 5 hours
                'temperature_celsius' => rand(15, 30), // Random temperature between 15 and 30 degrees Celsius
                'humidity_percent' => rand(30, 80), // Random humidity between 30% and 80%
                'source' => ['sensor', 'api'][array_rand(['sensor', 'api'])], // Randomly select source
            ]);
        }

    }
}
