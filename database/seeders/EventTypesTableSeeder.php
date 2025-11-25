<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $types = [
            ['type_key' => 'cleanup', 'label' => 'Cleanup Drive', 'icon_class' => 'fa-solid fa-broom'],
            ['type_key' => 'seminar', 'label' => 'Seminar', 'icon_class' => 'fa-solid fa-chalkboard-teacher'],
            ['type_key' => 'fundraise', 'label' => 'Fundraise Activity', 'icon_class' => 'fa-solid fa-hand-holding-dollar'],

            ['type_key' => 'workshop', 'label' => 'Workshop / Training', 'icon_class' => 'fa-solid fa-people-arrows'],
            ['type_key' => 'tree_planting', 'label' => 'Tree Planting', 'icon_class' => 'fa-solid fa-tree'],
            ['type_key' => 'medical_mission', 'label' => 'Medical Mission', 'icon_class' => 'fa-solid fa-briefcase-medical'],
            ['type_key' => 'blood_drive', 'label' => 'Blood Donation Drive', 'icon_class' => 'fa-solid fa-droplet'],
            ['type_key' => 'outreach', 'label' => 'Community Outreach', 'icon_class' => 'fa-solid fa-hands-holding-heart'],
            ['type_key' => 'orientation', 'label' => 'Volunteer Orientation', 'icon_class' => 'fa-solid fa-clipboard-user'],
            ['type_key' => 'disaster_relief', 'label' => 'Disaster Relief Operation', 'icon_class' => 'fa-solid fa-house-circle-exclamation'],
            ['type_key' => 'distribution', 'label' => 'Goods Distribution', 'icon_class' => 'fa-solid fa-boxes-packing'],
            ['type_key' => 'awareness_campaign', 'label' => 'Awareness Campaign', 'icon_class' => 'fa-solid fa-bullhorn'],
            ['type_key' => 'sports_event', 'label' => 'Sports Event', 'icon_class' => 'fa-solid fa-volleyball'],
            ['type_key' => 'food_drive', 'label' => 'Food Drive', 'icon_class' => 'fa-solid fa-bowl-food'],
            ['type_key' => 'reforestation', 'label' => 'Reforestation Activity', 'icon_class' => 'fa-solid fa-seedling'],
        ];

        foreach ($types as $t) {
            DB::table('event_types')->insert([
                'type_key' => $t['type_key'],
                'label' => $t['label'],
                'icon_class' => $t['icon_class'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
