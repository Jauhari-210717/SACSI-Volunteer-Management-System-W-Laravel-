@php
    $pageTitle = 'Volunteer Profile';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Profile</title>

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('assets/volunteer_list/css/Volunteer_List.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/volunteer_profile/css/volunteer_profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Reusable-Searchbar+Filter.css') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

{{-- Loader & Navbar --}}
@include('layouts.page_loader')
@include('layouts.navbar')

<section id="Student-Section">
    <div class="container-fluid main-content py-4">
        <div class="student-section-wrapper">

            <!-- LEFT COLUMN -->
            <div class="left-col">
                <div class="left-section" style="background-color: #f2f5f8;">

                    <!-- PROFILE SECTION -->
                    <div class="profile-section p-3 border rounded mb-3">
                        <table class="table table-borderless w-100 mb-0">
                            <tbody>
                                <tr>

                                    <!-- LEFT SIDE (Avatar + Name) -->
                                    <td class="text-center align-middle" style="width:100%;">
                                        <img src="{{ $volunteer->avatar_url }}"
                                             alt="{{ e($volunteer->full_name ?? 'Volunteer') }}"
                                             class="profile-photo mb-2 border rounded-circle">

                                        <h2 class="volunteer-name mb-1">
                                            {{ $volunteer->full_name ?? '—' }}
                                        </h2>

                                        <p class="volunteer-title mb-2">
                                            {{ $volunteer->course->course_name ?? 'Volunteer' }}
                                        </p>
                                    </td>

                                    <!-- RIGHT SIDE (Action Buttons) -->
                                    <td class="align-middle position-relative">
                                        <div class="action-tools d-flex flex-column gap-2 position-absolute top-0 end-0 m-2">

                                            <!-- Status -->
                                            <div class="info-card d-flex align-items-center gap-2 px-2 py-1">
                                                <i class="fas fa-check-circle"></i>
                                                <span class="status-text active">Active</span>
                                            </div>

                                            <!-- Print -->
                                            <button class="info-card" onclick="printLeftColumn()">
                                                <i class="fas fa-print"></i> Print
                                            </button>

                                            <!-- Edit (placeholder) -->
                                            <button class="info-card">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>

                                        </div>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- VOLUNTEER DETAILS -->
                    <div class="volunteer-details p-3 border rounded mb-3 position-relative">

                        <!-- COPY BUTTON -->
                        <button class="copy-volunteer-btn" onclick="copyVolunteerData(this)">
                            Copy <i class="fas fa-copy"></i>
                        </button>

                        <h4 class="text-center mb-3">Volunteer Information</h4>

                        <table class="table table-borderless mb-0">
                            <tbody>

                                <tr>
                                    <td>
                                        <div class="detail-card">
                                            <h6><i class="fas fa-graduation-cap"></i> Course & Year</h6>
                                            <p>
                                                {{ $volunteer->course->course_name ?? '—' }}
                                                — {{ $volunteer->year_level ?? '—' }}
                                            </p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="detail-card">
                                            <h6><i class="fas fa-phone"></i> Contact #</h6>
                                            <p>{{ $volunteer->contact_number ?? '—' }}</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="detail-card">
                                            <h6><i class="fas fa-ambulance"></i> Emergency #</h6>
                                            <p>{{ $volunteer->emergency_contact ?? '—' }}</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="detail-card">
                                            <h6><i class="fas fa-envelope"></i> Email</h6>
                                            <p>{{ $volunteer->email ?? '—' }}</p>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="detail-card">
                                            <h6><i class="fas fa-map-marker-alt"></i> Barangay</h6>
                                            <p>{{ $volunteer->barangay ?? '—' }}</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="detail-card">
                                            <h6><i class="fas fa-city"></i> District</h6>
                                            <p>{{ $volunteer->district ? "District $volunteer->district" : '—' }}</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="detail-card">
                                            <h6><i class="fas fa-chart-line"></i> Attendance Rate</h6>
                                            <p>69%</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="detail-card">
                                            <h6><i class="fas fa-user-clock"></i> Service</h6>
                                            <p>2 Years</p>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    </div>

                    <!-- WEEKLY SCHEDULE -->
                    <div class="schedule-section p-3 border rounded position-relative">

                        <button class="copy-schedule-btn" onclick="copySchedule(this)">
                            Copy <i class="fas fa-copy"></i>
                        </button>

                        <h4 class="text-center mb-3">Weekly Class Schedule</h4>

                        @php
                            $raw = trim($volunteer->class_schedule ?? '');
                            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                            $schedule = array_fill_keys($days, []);

                            if ($raw !== '') {
                                foreach ($days as $day) {
                                    if (preg_match("/$day:\s*(.*?)(?=(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|$))/is", $raw, $m)) {
                                        $content = trim($m[1]);
                                        if (strtolower($content) !== 'no class' && $content !== '') {
                                            $schedule[$day] = array_values(
                                                array_filter(preg_split('/\s+/', $content))
                                            );
                                        }
                                    }
                                }
                            }

                            $formatTime = function($time) {
                                [$h,$m] = array_pad(explode(':', trim($time)), 2, '00');
                                $h = intval($h);
                                $amp = $h >= 12 ? 'PM' : 'AM';
                                $h12 = ($h % 12) ?: 12;
                                return $h12 . ':' . str_pad($m,2,'0') . ' ' . $amp;
                            };
                        @endphp

                        <table class="table table-bordered text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>MON</th><th>TUE</th><th>WED</th><th>THU</th><th>FRI</th><th>SAT</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    @foreach ($days as $day)
                                        <td>
                                            @forelse ($schedule[$day] as $slot)
                                                @php
                                                    [$start,$end] = explode('-', $slot);
                                                @endphp
                                                <div class="time-slot">
                                                    {{ $formatTime($start) }} - {{ $formatTime($end) }}
                                                </div>
                                            @empty
                                                <div class="text-muted small">No Class</div>
                                            @endforelse
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="right-col">
                <div class="event-wrapper">
                    <div class="events-section p-3 border rounded">
                        <h4 class="events-title mb-3">Event History</h4>

                        <table class="table table-bordered mb-0 event-table">
                            <tbody>

                                <tr class="event-item">
                                    <td class="event-name">
                                        <a href="#">
                                            Community Food Drive
                                            <span class="click-bubble"><i class="fa fa-eye"></i> View Event</span>
                                        </a>
                                    </td>
                                    <td class="event-datetime">
                                        October 8, 2023 — 8:00 AM to 12:00 NN
                                    </td>
                                </tr>

                                <tr class="event-item">
                                    <td class="event-name">
                                        <a href="#">
                                            Tree Planting Activity
                                            <span class="click-bubble"><i class="fa fa-eye"></i> View Event</span>
                                        </a>
                                    </td>
                                    <td class="event-datetime">
                                        October 10, 2023 — 9:00 AM to 1:00 PM
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Bootstrap -->
<script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>

{{-- COPY + PRINT SCRIPTS 
@include('volunteer_profile.scripts')--}}
<script>
async function copyVolunteerData(button) {
    const cards = document.querySelectorAll('.volunteer-details .detail-card');
    if (!cards.length) return;

    let lines = [];
    let n = 1;

    cards.forEach(card => {
        const title = card.querySelector("h6")?.innerText?.trim() || "";
        const value = card.querySelector("p")?.innerText?.trim() || "";
        if (title && value) lines.push(`${n++}. ${title}: ${value}`);
    });

    const text = lines.join("\n");

    try {
        await navigator.clipboard.writeText(text);

        const original = button.innerHTML;
        button.innerHTML = `Copied <i class="fas fa-check"></i>`;
        button.disabled = true;
        setTimeout(() => {
            button.innerHTML = original;
            button.disabled = false;
        }, 1800);

    } catch (err) {
        console.error("Clipboard failed:", err);
        window.prompt("Copy manually:", text);
    }
}
</script>
<script>
async function copySchedule(button) {
    const table = document.querySelector('.schedule-section table');
    if (!table) return;

    let output = [];
    let count = 1;

    const days = [...table.querySelectorAll("thead th")].map(th => th.innerText.trim());
    const tds  = [...table.querySelectorAll("tbody tr td")];

    days.forEach((day, i) => {
        const cell = tds[i];
        const slots = [...cell.querySelectorAll(".time-slot")].map(s => s.innerText.trim());

        const line = slots.length
            ? `${count}. ${day}: ${slots.join(", ")}`
            : `${count}. ${day}: No Class`;

        output.push(line);
        count++;
    });

    const finalText = output.join("\n");

    try {
        await navigator.clipboard.writeText(finalText);

        const original = button.innerHTML;
        button.innerHTML = `Copied <i class="fas fa-check"></i>`;
        button.disabled = true;

        setTimeout(() => {
            button.innerHTML = original;
            button.disabled = false;
        }, 1800);

    } catch (err) {
        console.error("Clipboard failed:", err);
        window.prompt("Copy manually:", finalText);
    }
}
</script>
<script>
function printLeftColumn() {
    const leftCol = document.querySelector('.left-col');
    if (!leftCol) return;

    const clone = leftCol.cloneNode(true);

    // Remove ONLY interactive elements
    clone.querySelectorAll(
        'button, .copy-volunteer-btn, .copy-schedule-btn, .action-tools, .info-card'
    ).forEach(el => el.remove());

    // Open print window
    const w = window.open('', '', 'width=900,height=700');
    w.document.write(`
        <html>
        <head>
            <title>Volunteer Profile</title>
    `);

    // Clone CSS <link> tags for correct styling
    document.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
        w.document.write(link.outerHTML);
    });

    // Inline styles
    w.document.write(`<style>
        body { background: #fff !important; padding: 20px; }
        .profile-photo:hover,
        .detail-card:hover,
        .time-slot:hover { transform:none!important; box-shadow:none!important; }
    </style>`);

    w.document.write(`</head><body>`);
    w.document.write(clone.outerHTML);
    w.document.write(`</body></html>`);

    w.document.close();

    // Ensures images load BEFORE printing
    const imgs = w.document.images;
    let loaded = 0;

    if (imgs.length === 0) return w.print();

    for (let i = 0; i < imgs.length; i++) {
        imgs[i].addEventListener("load", () => {
            loaded++;
            if (loaded === imgs.length) {
                w.focus();
                w.print();
                w.close();
            }
        });

        // fallback
        imgs[i].addEventListener("error", () => {
            loaded++;
            if (loaded === imgs.length) {
                w.focus();
                w.print();
                w.close();
            }
        });
    }
}
</script>

</body>
</html>
