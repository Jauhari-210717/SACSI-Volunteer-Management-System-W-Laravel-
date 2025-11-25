<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocationsTableSeeder::class,      // OK
            CoursesTableSeeder::class,        // OK
            AdminAccountsSeeder::class,
            EventTypesTableSeeder::class,
            VolunteerProfileSeeder::class,    // Your NEW seeder (we will generate it)
        ]);
    }
}
