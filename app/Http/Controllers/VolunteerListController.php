<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VolunteerProfile;
use App\Models\Course;
use App\Models\Location;
use Illuminate\Support\Facades\Log;

class VolunteerListController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::orderBy('course_name')->get()->map(function($c) {

            $majorWords = [
                'Bachelor','Science','Arts','Education','Engineering',
                'Technology','Accountancy','Business','Management',
                'Communication','Media','New','Computer'
            ];

            $abbr = '';
            foreach (explode(' ', $c->course_name) as $word) {
                if (in_array($word, $majorWords)) {
                    $abbr .= strtoupper($word[0]);
                }
            }

            $c->abbr = $abbr;
            return $c;
        });

        $barangays = Location::pluck('barangay')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $districts = Location::pluck('district_id')
            ->filter()
            ->unique()
            ->sort()
            ->map(fn($id) => (object)[
                'district_id' => $id,
                'district_name' => "District $id"
            ])
            ->values();

        return view('volunteer_list.volunteer_list', compact(
            'courses', 'barangays', 'districts'
        ));
    }

    private $DAYS = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

    private function parseRangeStr(string $range): ?array
    {
        $range = preg_replace('/\s+/', '', $range);

        if (!preg_match('/^(\d{1,2}):(\d{2})-(\d{1,2}):(\d{2})$/', $range, $m)) {
            return null;
        }

        $start = ((int)$m[1]) * 60 + (int)$m[2];
        $end   = ((int)$m[3]) * 60 + (int)$m[4];

        return $end > $start ? [$start, $end] : null;
    }

    private function extractScheduleByDay(?string $schedule): array
    {
        $output = [];
        foreach ($this->DAYS as $d) $output[$d] = [];

        if (!$schedule) return $output;

        foreach ($this->DAYS as $day) {
            if (!preg_match(
                "/{$day}:\s*(.*?)(?=(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|$))/is",
                $schedule, $m
            )) continue;

            $content = trim($m[1]);
            if (stripos($content, 'No Class') !== false) continue;

            if (preg_match_all('/\d{1,2}:\d{2}\s*-\s*\d{1,2}:\d{2}/', $content, $found)) {
                foreach ($found[0] as $range) {
                    $parsed = $this->parseRangeStr($range);
                    if ($parsed) $output[$day][] = $parsed;
                }
            }
        }

        return $output;
    }

    private function overlaps(array $a, array $b): bool
    {
        return !($a[1] <= $b[0] || $a[0] >= $b[1]);
    }

    public function data(Request $request)
    {
        Log::info("=== VOLUNTEER SEARCH DEBUG START ===");
        Log::info("Incoming Query Params:", $request->query());

        $perPage    = (int) $request->query('per_page', 12);
        $searchRaw  = $request->query('search', '');
        $search     = strtolower(trim($searchRaw));

        $courseId   = $request->query('course_id');
        $barangay   = $request->query('barangay');
        $district   = $request->query('district');
        $yearLevel  = $request->query('year_level');
        $selectedDay = $request->query('day');
        $selectedBlock = $request->query('schedule_day');

        /* ------------------------------
           FIXED SCHEDULE PARSING
        ------------------------------ */
        $parsedRange = null;
        if ($selectedBlock) {
            $clean = str_ireplace([' AM',' PM'], '', $selectedBlock);
            $parsedRange = $this->parseRangeStr($clean);
        }

        Log::info("Parsed Filters:", [
            "search" => $search,
            "course_id" => $courseId,
            "barangay" => $barangay,
            "district" => $district,
            "yearLevel" => $yearLevel,
            "selectedDay" => $selectedDay,
            "selectedBlock" => $selectedBlock
        ]);

        if ($parsedRange) {
            Log::info("Parsed schedule range: ", $parsedRange);
        }

        /* ------------------------------
           BASE QUERY
        ------------------------------ */
        $query = VolunteerProfile::with('course')->select(
            'volunteer_id','full_name','course_id','year_level',
            'class_schedule','barangay','district',
            'profile_picture_url','profile_picture_path'
        );

        $scheduleMode = ($selectedDay || $selectedBlock) ? true : false;

        /* ============================================================
           SMART SEARCH — FIXED
           (DISABLED WHEN DOING SCHEDULE-BASED SEARCH)
        ============================================================ */
        if (!$scheduleMode && !empty($search)) {

            Log::info("Search activated for: $search");

            $query->where(function($q) use ($search) {

                $s = strtolower($search);
                $like = "%{$s}%";

                Log::info("Applying name search: $like");

                $q->whereRaw("LOWER(full_name) LIKE ?", [$like])
                  ->orWhereRaw("LOWER(barangay) LIKE ?", [$like]);

                if (in_array($s, ['1','district 1','d1'])) {
                    $q->orWhere('district', 1);
                }
                if (in_array($s, ['2','district 2','d2'])) {
                    $q->orWhere('district', 2);
                }

                Log::info("Applying course name search: $like");
                $q->orWhereHas('course', function($qc) use ($like) {
                    $qc->whereRaw("LOWER(course_name) LIKE ?", [$like]);
                });

                /* ------------------------------
                   FIXED ACRONYM MATCH
                ------------------------------ */
                $q->orWhereHas('course', function($qc) use ($s) {

                    static $abbr = null;

                    if ($abbr === null) {
                        $abbr = Course::all()->map(function ($c) {
                            $majorWords = [
                                'Bachelor','Science','Arts','Education','Engineering',
                                'Technology','Accountancy','Business','Management',
                                'Communication','Media','New','Computer'
                            ];
                            $ac = '';
                            foreach (explode(' ', $c->course_name) as $w) {
                                if (in_array($w, $majorWords)) {
                                    $ac .= strtolower($w[0]);
                                }
                            }
                            return (object)[ 'id' => $c->course_id, 'abbr' => $ac ];
                        });
                    }

                    $matched = $abbr
                        ->filter(fn($c) => str_starts_with($c->abbr, $s))
                        ->pluck('id');

                    Log::info("Acronym match:", [
                        "search" => $s,
                        "matchedIDs" => $matched->toArray()
                    ]);

                    if ($matched->isNotEmpty()) {
                        $qc->whereIn('course_id', $matched);
                    } else {
                        $qc->whereRaw("0=1");
                    }
                });
            });
        }

        /* ------------------------------
           FIELD FILTERS
        ------------------------------ */
        if ($courseId && $courseId !== 'remove') $query->where('course_id', $courseId);
        if ($barangay && $barangay !== 'remove') $query->where('barangay', $barangay);
        if ($district && $district !== 'remove') $query->where('district', $district);
        if ($yearLevel && $yearLevel !== 'remove') $query->where('year_level', $yearLevel);

        /* ------------------------------
           LOG RAW SQL
        ------------------------------ */
        Log::info("SQL BEFORE EXECUTION:");
        Log::info($query->toSql());
        Log::info("SQL Bindings:", $query->getBindings());

        $items = $query->get();
        Log::info("Fetched items count: " . $items->count());

        /* ============================================================
           FIXED SCHEDULE FILTERING (NOW WORKS AGAIN!)
        ============================================================ */
        if ($selectedDay && $parsedRange) {
            Log::info("Applying schedule availability filter...");

            $items = $items->filter(function ($v) use ($selectedDay, $parsedRange) {
                $blocks = $this->extractScheduleByDay($v->class_schedule);

                foreach ($blocks[$selectedDay] ?? [] as $block) {
                    if ($this->overlaps($parsedRange, $block)) {
                        return false;
                    }
                }
                return true;
            });
        }

        Log::info("Final items after schedule filtering: " . $items->count());
        Log::info("=== VOLUNTEER SEARCH DEBUG END ===");

        /* ------------------------------
           PAGINATION
        ------------------------------ */
        $total = $items->count();
        $currentPage = max(1, (int)$request->page);
        $lastPage = max(1, (int)ceil($total / $perPage));

        $results = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $results->map(function ($item) {
                return [
                    'volunteer_id'  => $item->volunteer_id,
                    'full_name'     => $item->full_name,
                    'year_level'    => $item->year_level,
                    'class_schedule'=> $item->class_schedule,
                    'avatar_url'    => $item->avatar_url,
                    'course'        => $item->course ? [
                        'course_id'   => $item->course->course_id,
                        'course_name' => $item->course->course_name,
                    ] : null,
                    'barangay'      => $item->barangay,
                    'district'      => $item->district,
                ];
            }),
            'total'         => $total,
            'per_page'      => $perPage,
            'current_page'  => $currentPage,
            'last_page'     => $lastPage,
            'prev_page_url' => $currentPage > 1 ? url("/volunteers/data?page=" . ($currentPage - 1)) : null,
            'next_page_url' => $currentPage < $lastPage ? url("/volunteers/data?page=" . ($currentPage + 1)) : null,
        ]);
    }
}
