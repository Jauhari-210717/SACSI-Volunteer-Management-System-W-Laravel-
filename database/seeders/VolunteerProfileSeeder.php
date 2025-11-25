<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\VolunteerProfile;
use App\Models\Course;

class VolunteerProfileSeeder extends Seeder
{
    private $days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

    private $barangays = [
        "Ayala", "Bubuan", "Tumaga", "Camino Nuevo", "Sta. Maria",
        "Divisoria", "Putik", "Canelar", "San Roque", "Tetuan"
    ];

    private function randomSchedule()
    {
        $schedule = "";

        foreach ($this->days as $day) {

            // 50% chance they have class that day
            if (rand(0,1) === 0) {
                $schedule .= "$day: No Class ";
                continue;
            }

            $blockCount = rand(1, 4); // 1–4 blocks a day
            $blocks = [];

            for ($i=0; $i<$blockCount; $i++) {
                $startHour = rand(7,18);     // 7am–6pm
                $startMin  = rand(0,1) ? "00" : "30";

                $endHour   = min($startHour + 1, 20);
                $endMin    = $startMin;

                $blocks[] = sprintf("%d:%s-%d:%s", $startHour, $startMin, $endHour, $endMin);
            }

            $schedule .= "$day: " . implode(" ", $blocks) . " ";
        }

        return trim($schedule);
    }

    public function run(): void
    {
        $courses = Course::pluck('course_id')->toArray();

        if (empty($courses)) {
            dd("⚠ No courses found. Seed your courses first.");
        }

        for ($i = 1; $i <= 50; $i++) {

            VolunteerProfile::create([
                // ❌ volunteer_id removed (auto-increment)
                
                'full_name' => fake()->name(),

                'course_id' => fake()->randomElement($courses),
                'year_level' => fake()->numberBetween(1, 4),

                'class_schedule' => $this->randomSchedule(),

                'barangay' => fake()->randomElement($this->barangays),
                'district' => fake()->numberBetween(1, 2),

                'profile_picture_url' => '/storage/defaults/default_user.png',
                'profile_picture_path' => null,
            ]);
        }
    }
}
