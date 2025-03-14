<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevicesTableSeeder extends Seeder
{
    public function run()
    {
        $deviceTypes = ['Sensor', 'API', 'Actuator'];
        $statuses = ['Online', 'Offline']; // Updated to match the enum values

        for ($i = 1; $i <= 10; $i++) {
            DB::table('devices')->insert([
                'device_name' => 'Device ' . $i,
                'device_type' => $deviceTypes[array_rand($deviceTypes)],
                'status' => $statuses[array_rand($statuses)], // Use updated statuses
                'user_id' => rand(1, 2), // Assuming user IDs 1 and 2 exist
            ]);
        }
    }
}
