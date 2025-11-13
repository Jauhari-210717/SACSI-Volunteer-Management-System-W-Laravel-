<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LocationsTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // --- Define all barangays ---
        $locations = [
            // West District
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Ayala'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Bagong Calarian'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Bunguiao'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Cabaluay'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Cabatangan'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Curuan'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Divisoria'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Dita'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Guiwan'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Latuan'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Manicahan'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Mercedes'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Putik'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Recodo'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Rizal'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'San Jose Cawa-Cawa'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'San Roque'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Santa Barbara'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Santa Catalina'],
            ['district_id'=>1,'zone_name'=>'West','barangay'=>'Tetuan'],

            // East District
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Boalan'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Bolong'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Bugsukan'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Canelar'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Lamisahan'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Lantawan'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'La Paz'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Mampang'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Mabuhay'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Mahayag'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'San Jose Gusu'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Talon-Talon'],
            ['district_id'=>2,'zone_name'=>'East','barangay'=>'Tugbungan'],

            // North District
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Cabaluay Norte'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Calarian Norte'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Curuan Norte'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Guisao Norte'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Lunsay Norte'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Mampang Norte'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Mariki'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Pamucutan'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Pasonanca'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Putik Norte'],
            ['district_id'=>1,'zone_name'=>'North','barangay'=>'Suterville'],

            // South District
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Arena Blanco'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Cabaluay Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Canelar Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Cawit'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Dita Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Divisoria Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Guiwan Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Labuan'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Manicahan Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Mercedes Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Putik Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Recodo Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Rizal Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'San Jose Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'San Roque Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Santa Barbara Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Santa Catalina Sur'],
            ['district_id'=>2,'zone_name'=>'South','barangay'=>'Tetuan Sur'],

            // Poblacion / Other Barangays
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Curuan Proper'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Santa Maria'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Mampang Proper'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Bugo'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Lapakan'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Vitali'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Guisao Proper'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Bunguiao Proper'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Curuan East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Cabaluay Proper'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Divisoria Proper'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Canelar Proper'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'La Paz Proper'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Tugbungan Proper'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Sta. Maria East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Sta. Maria West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Arena Blanco East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Arena Blanco West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Labuan East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Labuan West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Lunzuran'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Manicahan East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Manicahan West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Putik East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Putik West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Rizal East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Rizal West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'San Jose East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'San Jose West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'San Roque East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'San Roque West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Santa Barbara East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Santa Barbara West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Santa Catalina East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Santa Catalina West'],
            ['district_id'=>1,'zone_name'=>'Poblacion','barangay'=>'Tetuan East'],
            ['district_id'=>2,'zone_name'=>'Poblacion','barangay'=>'Tetuan West'],
        ];

        // --- Insert all locations ---
        foreach ($locations as $loc) {
            DB::table('locations')->insert(
                array_merge($loc, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('Locations seeded: '.count($locations));
    }
}
