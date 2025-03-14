<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnergyDataTableSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('energy_data')->insert([
                'user_id' => DB::table('users')->inRandomOrder()->first()->id, // Select a random existing user ID
                'device_id' => DB::table('devices')->inRandomOrder()->first()->id, // Select a random existing device ID

                'timestamp' => now()->subHours(rand(0, 5)),
                'power_usage_watts' => rand(50, 300), // Random power usage between 50 and 300 watts
                'voltage_volts' => rand(220, 240), // Random voltage between 220 and 240 volts
                'current_amperes' => round(rand(1, 10) / 10, 2), // Random current between 0.1 and 1.0 amperes
                'energy_consumption_kwh' => round(rand(1, 100) / 100, 2), // Random energy consumption between 0.01 and 1.00 kWh
            ]);
        }

    }
}
