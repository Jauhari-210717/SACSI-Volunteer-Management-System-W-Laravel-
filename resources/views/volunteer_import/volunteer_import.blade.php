{{-- Declar Page Title --}}
@php
    $pageTitle = 'Volunteer Imports';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management - Import CSV, Invalid Entries & Import Logs</title>

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('assets/volunteer_import/css/volunteer_import.css') }}">

    {{-- Bootstrap & Font Awesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta id="scrollToInvalid" content="{{ session('scrollToInvalid') ? '1' : '0' }}">
    <meta id="lastUpdatedTable" content="{{ session('last_updated_table') ?? '' }}">
    <meta id="lastUpdatedIndices" content='@json(session("last_updated_indices") ?? [])'>

</head>

<body>
    {{-- Loader & Navbar --}}
    @include('layouts.page_loader')
    @include('layouts.navbar')

    <div class="scroll-container">
        {{-- 1. IMPORT & VALIDATION --}}
        <section id="import-Section-invalid">
            <div class="database-container">
                <main class="database-main">
                    <div class="import-section">

                        {{-- Header --}}
                        <div class="import-controls">
                            <h2 class="section-title"><i class="fas fa-tasks"></i> Import & Validation</h2>
                            <div class="action-buttons">
                                <button class="btn btn-outline-secondary import-btn" onclick="openModal('importHandlingModal1')">
                                    <i class="fas fa-book fa-xl"></i> Import & Validation Guide
                                </button>
                            </div>
                        </div>

                        {{-- File Upload + Reset --}}
                        <div class="import-controls d-flex align-items-center gap-2">
                            <form action="{{ route('volunteer.import.preview') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="import-controls">
                                    {{-- Choose File Button + File Path Span --}}
                                    <div class="file-upload">
                                        <div class="input-group">
                                            <input type="file" name="csv_file" class="form-control d-none" id="file-upload" accept=".csv" required>
                                            <button class="btn btn-outline-secondary rounded-1" type="button" id="file-upload-button">
                                                <i class="fa-solid fa-file-csv me-2"></i> Choose File
                                            </button>
                                            <span class="file-path" id="file-path">
                                                {{ session('uploaded_file_name', 'No file chosen') }}
                                            </span>
                                        </div>
                                    </div>
                                    {{-- Import Button --}}
                                    <div class="uploader-info">
                                        <input type="text" class="form-control" value="Uploading as {{ Auth::guard('admin')->user()->username ?? 'Guest' }}" readonly>
                                        @if(!session('csv_imported'))
                                            <button type="submit" class="btn btn-outline-secondary import-btn">
                                                <i class="fa-solid fa-upload"></i> Import
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                            
                            {{-- Reset Button --}}
                            @if(session()->exists('validEntries') || session()->exists('invalidEntries'))
                                <button type="button" 
                                        class="btn btn-outline-warning import-btn" 
                                        id="openResetModal"
                                        title="Clear all imported entries from preview">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Clear Imports
                                </button>
                            @endif
                        </div>

                        {{-- Divider/Line --}}
                        <hr class="red-hr">

                        {{-- Invalid Data Table --}}
                        <div class="data-table-container">
                            {{-- Action Message --}}
                            <div class="action-message {{ session('success') ? 'text-success' : 'd-none' }}">
                                <span class="message-text">{!! session('success') !!}</span>
                                <button type="button" class="close-message-btn">&times;</button>
                            </div>

                            {{-- Table Actions --}}
                            <div class="table-controls mb-0">
                                <div class="table-actions d-flex align-items-center justify-content-center gap-2">
                                    <h3>Invalid Entries</h3>
                                    {{-- Toggle Edit Side Tool --}}
                                    <button type="button" class="toggle-edit-btn btn btn-outline-secondary btn-sm">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit Table
                                    </button>

                                    {{-- Edit Table Buttons --}}
                                    <div class="hidden-actions">
                                        {{-- Select All Button --}}
                                        <button type="button" class="btn btn-outline-primary btn-sm select-all-btn">
                                            <i class="fa-solid fa-check-double"></i> Select All
                                        </button>
                                        {{-- Delete Button --}}
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-btn" data-action="{{ route('volunteer.deleteEntries') }}" data-table-type="invalid">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </button>
                                        {{-- Copy Button --}}
                                        <button type="button" class="btn btn-outline-success btn-sm copy-btn">
                                            <i class="fa-solid fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>

                                {{-- Invalid Section Searchbar --}}
                                @include('layouts.search_bar.universal_search_bar', [
                                    'tableId'   => 'invalid-entries-table',
                                    'type'      => 'invalid',
                                    'placeholder' => 'Search invalid entries...'
                                ])
                            </div>
                            
                            {{-- Invalid Section Table --}}
                            <div class="table-responsive mt-3">
                                <table id="invalid-entries-table" class="table table-hover table-striped volunteer-table">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" class="select-all-invalid"></th>
                                            <th>#</th>
                                            <th>Full Name</th>
                                            <th>School ID</th>
                                            <th>Course</th>
                                            <th>Year</th>
                                            <th>Contact #</th>
                                            <th>Email</th>
                                            <th>Emergency #</th>
                                            <th>FB/Messenger</th>
                                            <th>Barangay</th>
                                            <th>District</th>
                                            <th>Class Schedule</th>
                                            <th>Photo</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Check if Data is Valid --}}
                                        @php
                                            $allInvalidEntriesValid = true;

                                            if (!empty($invalidEntries)) {
                                                foreach ($invalidEntries as $entry) {
                                                    // Same checks your table uses
                                                    $hasErrors = isset($entry['errors']) && count($entry['errors']) > 0;

                                                    $missingRequired = false;
                                                    foreach (['full_name','id_number','course','year_level','contact_number','email','barangay','district'] as $requiredField) {
                                                        if (empty($entry[$requiredField])) $missingRequired = true;
                                                    }

                                                    $scheduleValid = !empty(trim($entry['class_schedule'] ?? ''));
                                                    $hasPic = !empty($entry['profile_picture_local']) || !empty($entry['profile_picture']);

                                                    $isFullyValid = !$hasErrors && !$missingRequired && $scheduleValid && $hasPic;

                                                    if (!$isFullyValid) {
                                                        $allInvalidEntriesValid = false;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp

                                        {{-- Display All Invalid Entries --}}
                                        @if(!empty($invalidEntries) && count($invalidEntries) > 0)
                                            @foreach ($invalidEntries as $index => $entry)
                                                @php
                                                    // Check Each Entries for Invalid Field and Display Error Tool Tip
                                                    $hasErrors = isset($entry['errors']) && count($entry['errors']) > 0;
                                                    $missingRequired = false;
                                                    foreach (['full_name','id_number','course','year_level','contact_number','email','barangay','district'] as $requiredField) {
                                                        if (empty($entry[$requiredField])) $missingRequired = true;
                                                    }

                                                    // Check if Schedule is Filled or Empty
                                                    $rowClass = $hasErrors ? 'invalid-row'
                                                            : ($missingRequired ? 'invalid-row-light' : '');
                                                    $scheduleValue = trim($entry['class_schedule'] ?? '');
                                                    $scheduleValid = !empty($scheduleValue);

                                                    // Check if Profile Photo Exist in Storage/Volunteers/
                                                    $hasPic = !empty($entry['profile_picture_local']) || !empty($entry['profile_picture']);
                                                    $btnClass = $hasPic ? 'btn-success' : 'btn-danger';
                                                    $btnText  = $hasPic ? 'Profile Picture' : 'No Photo';
                                                    $picSrc = $entry['profile_picture_local']
                                                        ? asset('storage/' . $entry['profile_picture_local'])
                                                        : ($entry['profile_picture'] ?? null);
                                                @endphp
                                                <tr class="{{ $rowClass }}">
                                                    {{-- Check Box --}}
                                                    <td><input type="checkbox" name="selected_invalid[]" value="{{ $index }}"></td>
                                                    {{-- Index Number --}}  
                                                    <td>{{ $index + 1 }}</td>
                                                    {{-- MAIN COLUMNS --}}
                                                    @php
                                                        $columns = [
                                                            'full_name' => 'Name',
                                                            'id_number' => 'School ID',
                                                            'course' => 'Course',
                                                            'year_level' => 'Year',
                                                            'contact_number' => 'Contact #',
                                                            'email' => 'Email',
                                                            'emergency_contact' => 'Emergency #',
                                                            'fb_messenger' => 'FB/Messenger',
                                                            'barangay' => 'Barangay',
                                                            'district' => 'District',
                                                        ];
                                                        $truncatedFields = ['full_name','course','email','fb_messenger','barangay','district'];
                                                    @endphp
                                                    {{-- Display No Data --}}
                                                    @foreach ($columns as $key => $label)
                                                        @php
                                                            $value = trim($entry[$key] ?? '');
                                                            $isTruncated = in_array($key, $truncatedFields);
                                                            $displayVal = strlen($value) > 20 && $isTruncated
                                                                ? substr($value, 0, 20).'...' : $value;
                                                            $errors = $entry['errors'][$key] ?? [];
                                                            $errors = is_array($errors) ? $errors : [$errors];
                                                            $tooltip = '';
                                                            if (!empty($errors)) {
                                                                $tooltip = implode('<br>', array_map(fn($e)=>e($e), $errors));
                                                                if (empty($value)) $tooltip = "Missing $label<br>".$tooltip;
                                                            }
                                                            $tooltipText = $tooltip ?: ($value ?: "No $label");
                                                        @endphp

                                                        {{-- DISTRICT --}}
                                                        @if($key === 'district')
                                                            @php
                                                                $districtId = trim($entry['district'] ?? '');
                                                                $districtName = stripos($districtId, 'district') !== false
                                                                    ? $districtId
                                                                    : "District " . $districtId;
                                                            @endphp
                                                            <td>{{ $districtName }}</td>
                                                        @else
                                                            <td data-value="{{ $value }}"
                                                                @if($tooltipText)
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-html="true"
                                                                    title="{!! $tooltipText !!}"
                                                                @endif
                                                                @if($isTruncated)
                                                                    class="text-truncate"
                                                                    style="max-width:150px;"
                                                                @endif
                                                            >
                                                                {{ $displayVal ?: "No $label" }}
                                                            </td>
                                                        @endif
                                                    @endforeach

                                                    {{-- CLASS SCHEDULE --}}
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm {{ $scheduleValid ? 'btn-success' : 'btn-danger' }}"
                                                            onclick="openScheduleModal(`{!! nl2br(e($scheduleValue)) !!}`, 'invalid', '{{ $index }}')">
                                                            {{ $scheduleValid ? 'Schedule' : 'No Class Schedule' }}
                                                        </button>
                                                    </td>

                                                    {{-- PROFILE PHOTO --}}
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm {{ $btnClass }}"
                                                            data-entry-index="{{ $index }}"
                                                            data-entry-type="valid"
                                                            data-vol-name="{{ addslashes($entry['full_name']) }}"
                                                            data-picture-src="{{ $picSrc ? addslashes($picSrc) : '' }}"
                                                            onclick="openImageModalFromButton(this)">
                                                            {{ $btnText }}
                                                        </button>
                                                    </td>

                                                    {{-- Table Actions --}}
                                                    <td>
                                                        {{-- Edit Entry --}}
                                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            onclick="setLastUsedTable('invalid', '{{ $index }}'); openEditVolunteerModal('invalid', '{{ $index }}')">
                                                            <i class="fa-solid fa-user-edit"></i> Edit
                                                        </button>
                                                        {{-- Validate/Move Inavlid to Valid Button --}}
                                                        <button type="button" class="btn btn-sm btn-outline-secondary move-btn"
                                                            onclick="submitMoveToValid(this)">
                                                            <i class="fa-solid fa-arrow-right"></i> Validate
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            {{-- Display for Empty Tables --}}
                                            <tr>
                                                <td colspan="15" class="text-center text-muted py-4">
                                                    <i class="fa-solid fa-file-import fa-lg me-2"></i>No invalid entries yet.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- Move All Invalid/Correct Entries to Valid Section --}}                                    
                            <div class="submit-section">
                                <button type="button"
                                        class="btn btn-danger submit-database"
                                        id="openMoveModalBtn"
                                        data-bs-toggle="tooltip"
                                        title="Move all invalid entries to verified entries">
                                    Move to All Invalid Entries
                                </button>

                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </section>

        {{-- 2. Submit Valid Entries to DB --}}
        <section id="import-Section-valid">
            <div class="database-container">
                <main class="database-main">
                    <div class="import-section">

                        {{-- Header --}}
                        <div class="import-header d-flex align-items-center justify-content-between mb-2">
                            <div class="import-controls">
                                <h2 class="section-title">
                                    <i class="fas fa-user-check"></i> Verified Entries
                                </h2>
                                <div class="action-buttons">
                                    <button class="btn btn-outline-secondary import-btn"
                                            onclick="closeModal('importHandlingModal1'); openModal('importHandlingModal2');">
                                        <i class="fas fa-book fa-xl"></i> Valid Entries Guide
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Divider/Line --}}
                        <hr class="red-hr">

                        {{-- Invalid Data Table --}}
                        <form action="{{ route('volunteer.import.validateSave') }}" method="POST">
                            @csrf

                            <div class="data-table-container">

                                {{-- Action Message --}}
                                <div class="action-message {{ session('success') ? 'text-success' : 'd-none' }}">
                                    <span class="message-text">{!! session('success') !!}</span>
                                    <button type="button" class="close-message-btn">&times;</button>
                                </div>
                                
                                {{-- Table Actions --}}
                                <div class="table-controls mb-0">
                                    <div class="table-actions d-flex align-items-center justify-content-center gap-2">
                                        <h3>Valid Entries</h3>

                                        {{-- Toggle Edit Side Tool --}}
                                        <button type="button" class="toggle-edit-btn btn btn-outline-secondary btn-sm">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit Table
                                        </button>

                                        <div class="hidden-actions">
                                            {{-- Select All Button --}}
                                            <button type="button" class="btn btn-outline-primary btn-sm select-all-btn">
                                                <i class="fa-solid fa-check-double"></i> Select All
                                            </button>
                                            {{-- Delete Button --}}
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-btn"
                                                    data-action="{{ route('volunteer.deleteEntries') }}"
                                                    data-table-type="valid">
                                                <i class="fa-solid fa-trash-can"></i> Delete
                                            </button>
                                            {{-- Copy Button --}}
                                            <button type="button" class="btn btn-outline-success btn-sm copy-btn">
                                                <i class="fa-solid fa-copy"></i> Copy
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {{-- Valid Section Searchbar --}}
                                    @include('layouts.search_bar.universal_search_bar', [
                                        'tableId'   => 'valid-entries-table',
                                        'type'      => 'valid',
                                        'placeholder' => 'Search valid entries...'
                                    ])
                                </div>

                                <div class="table-responsive mt-3">
                                    <table id="valid-entries-table" class="table table-hover table-striped volunteer-table">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" class="select-all-valid"></th>
                                                <th>#</th>
                                                <th>Full Name</th>
                                                <th>School ID</th>
                                                <th>Course</th>
                                                <th>Year</th>
                                                <th>Contact #</th>
                                                <th>Email</th>
                                                <th>Emergency #</th>
                                                <th>FB/Messenger</th>
                                                <th>Barangay</th>
                                                <th>District</th>
                                                <th>Class Schedule</th>
                                                <th>Photo</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                        {{-- Display All Valid Entries --}}
                                        @if(!empty($validEntries) && count($validEntries) > 0)
                                            @foreach ($validEntries as $index => $entry)

                                                @php
                                                    $hasPic = !empty($entry['profile_picture_local']) || !empty($entry['profile_picture']);
                                                    $btnClass = $hasPic ? 'btn-success' : 'btn-danger';
                                                    $btnText  = $hasPic ? 'Profile Picture' : 'No Photo';

                                                    $picSrc = $entry['profile_picture_local']
                                                        ? asset('storage/' . $entry['profile_picture_local'])
                                                        : ($entry['profile_picture'] ?? null);

                                                    $scheduleValue = trim($entry['class_schedule'] ?? '');
                                                    $scheduleValid = !empty($scheduleValue);
                                                @endphp

                                                <tr class="valid-entry">
                                                    {{-- Check Box --}}
                                                    <td>
                                                        <input type="checkbox" name="selected_valid[]" value="{{ $index }}"data-id-number="{{ $entry['id_number'] ?? '' }}">
                                                    </td>

                                                    {{-- Index Number --}}  
                                                    <td>{{ $index + 1 }}</td>

                                                    {{-- MAIN COLUMNS --}}
                                                    @php
                                                        $columns = [
                                                            'full_name' => 'Name',
                                                            'id_number' => 'School ID',
                                                            'course' => 'Course',
                                                            'year_level' => 'Year',
                                                            'contact_number' => 'Contact #',
                                                            'email' => 'Email',
                                                            'emergency_contact' => 'Emergency #',
                                                            'fb_messenger' => 'FB/Messenger',
                                                            'barangay' => 'Barangay',
                                                            'district' => 'District',
                                                            'class_schedule' => 'Class Schedule',
                                                        ];

                                                        $truncatedFields = [
                                                            'full_name','course','email','fb_messenger','barangay','district'
                                                        ];
                                                    @endphp

                                                    @foreach ($columns as $key => $label)
                                                        @php
                                                            $value = trim($entry[$key] ?? '');
                                                            $isTruncated = in_array($key, $truncatedFields);
                                                            $displayVal = strlen($value) > 20 && $isTruncated
                                                                ? substr($value, 0, 20).'...' : $value;
                                                        @endphp

                                                        {{-- DISTRICT --}}
                                                        @if($key === 'district')
                                                            @php
                                                                $districtId = trim($entry['district'] ?? '');
                                                                $districtName = stripos($districtId, 'district') !== false
                                                                    ? $districtId
                                                                    : "District " . $districtId;
                                                            @endphp
                                                            <td>{{ $districtName }}</td>

                                                        {{-- CLASS SCHEDULE --}}
                                                        @elseif($key === 'class_schedule')
                                                            <td data-value="{{ $value }}">
                                                                <button type="button"
                                                                    class="btn btn-sm {{ $scheduleValid ? 'btn-success' : 'btn-danger' }}"
                                                                    onclick="openScheduleModal(
                                                                        `{!! nl2br(e($scheduleValue)) !!}`,
                                                                        'valid',
                                                                        '{{ $index }}'
                                                                    )">
                                                                    {{ $scheduleValid ? 'Schedule' : 'No Class Schedule' }}
                                                                </button>
                                                            </td>

                                                        {{-- TRUNCATED --}}
                                                        @elseif($isTruncated)
                                                            <td class="text-truncate" style="max-width:150px;"
                                                                data-value="{{ $value }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $value ?: 'No '.$label }}">
                                                                {{ $displayVal ?: "No $label" }}
                                                            </td>

                                                        {{-- DEFAULT --}}
                                                        @else
                                                            <td data-value="{{ $value }}">
                                                                {{ $displayVal ?: "No $label" }}
                                                            </td>

                                                        @endif
                                                    @endforeach

                                                    {{-- PHOTO BUTTON --}}
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm {{ $btnClass }}"
                                                            data-entry-index="{{ $index }}"
                                                            data-entry-type="valid"
                                                            data-vol-name="{{ addslashes($entry['full_name']) }}"
                                                            data-picture-src="{{ $picSrc ? addslashes($picSrc) : '' }}"
                                                            onclick="openImageModalFromButton(this)">
                                                            {{ $btnText }}
                                                        </button>
                                                    </td>

                                                    {{-- Table ACTIONS --}}
                                                    <td>
                                                        {{-- Edit Entry --}}
                                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            onclick="setLastUsedTable('valid','{{ $index }}'); openEditVolunteerModal('valid','{{ $index }}')">
                                                            <i class="fa-solid fa-user-edit"></i> Edit
                                                        </button>
                                                        
                                                        {{-- Invalidate/Move Valid to Invalid Button --}}
                                                        <button type="button" class="btn btn-sm btn-outline-secondary move-invalid-btn"
                                                            onclick="moveToInvalid('{{ $index }}')">
                                                            <i class="fa-solid fa-arrow-left"></i> Move to Invalid
                                                        </button>
                                                    </td>

                                                </tr>

                                            @endforeach
                                        @else
                                            {{-- Display for Empty Tables --}}
                                            <tr>
                                                <td colspan="16" class="text-center text-muted py-4">
                                                    <i class="fa-solid fa-check-circle fa-lg me-2"></i>No verified entries yet.
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                
                                {{-- Submit Valid Entries to Databasee --}}
                                <div class="submit-section">
                                    @php
                                        $validEntries = session('validEntries', []);
                                        $hasValidEntries = count($validEntries) > 0;
                                    @endphp

                                    @if($hasValidEntries)
                                        <button type="button" class="btn btn-danger submit-database" id="openSubmitModalBtn"
                                                data-bs-toggle="tooltip"
                                                title="Submit all verified entries to the database">
                                            <i class="fa-solid fa-database"></i> Submit
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </section>


        {{-- Bootstrap tooltip initialization --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });
        </script>

        <style>/* Highlight valid entries */
        .valid-entry {
            background-color: #e0f7e0;  /* Light green */
        }

        /* Shorten FB/Messenger links */
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Adjust button color for Schedule */
        .btn-success {
            background-color: #28a745;  /* Green */
            border-color: #28a745;
        }

        .btn-danger {
            background-color: #dc3545;  /* Red */
            border-color: #dc3545;
        }

        .btn-sm {
            font-size: 0.875rem;  /* Smaller button size */
        }

        /* Optional: Tooltip styling (if needed) */
        [data-bs-toggle="tooltip"] {
            cursor: help;
            text-decoration: underline dotted;
        }

        /* Remove underline from table buttons / links */
        .volunteer-table button,
        .volunteer-table a {
            text-decoration: none !important;
        }
        /* Make long text truncate with ellipsis */
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        [data-bs-toggle="tooltip"] {
            cursor: help;
            text-decoration: none;
        }
        </style>

       {{-- 3.IMPORT LOGS --}}
        <section id="importlog-Section">
            <div class="database-container">
                <main class="database-main"> 
                    <div class="import-section">

                        {{-- Header --}}
                        <div class="import-controls mb-3">
                            <h2 class="section-title"><i class="fas fa-history"></i> Import Logs</h2>
                        </div>

                        {{-- Divider/Line --}}
                        <hr class="red-hr">

                        {{-- Import Logs Table --}}
                        <div class="data-table-container">
                            {{-- Action Message --}}
                            <div class="action-message {{ session('success') ? 'text-success' : 'd-none' }}">
                                <span class="message-text">{!! session('success') !!}</span>
                                <button type="button" class="close-message-btn">&times;</button>
                            </div>
                            
                            {{-- Table Actions --}}
                            <div class="table-controls mb-0">
                                <div class="table-actions d-flex align-items-center justify-content-center gap-2">
                                    <h3>Import History</h3>

                                    {{-- Toggle Edit Side Tool --}}
                                    <button type="button" class="toggle-edit-btn btn btn-outline-secondary btn-sm">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit Table
                                    </button>

                                    {{-- Edit Table Buttons --}}
                                    <div class="hidden-actions">
                                        {{-- Select All Button --}}
                                        <button type="button" class="btn btn-outline-primary btn-sm select-all-btn">
                                            <i class="fa-solid fa-check-double"></i> Select All
                                        </button>
                                        {{-- Delete Button --}}
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm delete-btn"
                                                data-action="{{ route('volunteer.deleteEntries') }}"
                                                data-table-type="logs">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </button>
                                        {{-- Copy Button --}}
                                        <button type="button" class="btn btn-outline-success btn-sm copy-btn">
                                            <i class="fa-solid fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>

                                {{-- Valid Section Searchbar --}}
                                @include('layouts.search_bar.universal_search_bar', [
                                    'tableId'   => 'import-logs-table',
                                    'type'      => 'import_logs',
                                    'placeholder' => 'Search import logs...'
                                ])

                            </div>
                            
                            {{-- Import Log Data Table --}}
                            <div class="table-responsive mt-3">
                                <table id="import-logs-table" class="table table-hover table-striped volunteer-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th><input type="checkbox" class="select-all-checkbox"></th>
                                            <th>#</th>
                                            <th>File Name</th>
                                            <th>Uploaded By</th>
                                            <th>Uploaded At</th>
                                            <th>Total Records</th>
                                            <th>Valid</th>
                                            <th>Invalid</th>
                                            <th>Duplicate</th>
                                            <th>Status</th>
                                            <th style="min-width: 300px;">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($importLogs as $log)
                                        <tr class="align-middle">
                                            <td><input type="checkbox" name="selected_logs[]" value="{{ $log->import_id }}"></td>
                                            <td data-value="{{ $log->import_id }}">{{ $log->import_id }}</td>

                                            {{-- File Name --}}
                                            <td class="text-truncate" style="max-width: 220px;" 
                                                title="{{ $log->file_name }}"
                                                data-value="{{ $log->file_name }}">
                                                {{ $log->file_name }}
                                            </td>

                                            {{-- Uploaded By --}}
                                            <td data-value="{{ $log->admin->name ?? $log->admin->username ?? 'Unknown' }}">
                                                {{ $log->admin->name ?? $log->admin->username ?? 'Unknown' }}
                                            </td>

                                            {{-- Uploaded At --}}
                                            <td data-value="{{ optional($log->import_date ?? $log->created_at)->format('Y-m-d H:i:s') }}">
                                                {{ optional($log->import_date ?? $log->created_at)->format('M d, Y h:i A') ?? '-' }}
                                            </td>

                                            {{-- Counts --}}
                                            <td data-value="{{ $log->total_records }}">{{ $log->total_records }}</td>
                                            <td data-value="{{ $log->valid_count }}"><span class="badge bg-success">{{ $log->valid_count }}</span></td>
                                            <td data-value="{{ $log->invalid_count }}"><span class="badge bg-danger">{{ $log->invalid_count }}</span></td>
                                            <td data-value="{{ $log->duplicate_count }}"><span class="badge bg-warning text-dark">{{ $log->duplicate_count }}</span></td>

                                            {{-- Status Badge --}}
                                            @php
                                                $status = strtolower($log->status);
                                                $statusClass = match($status) {
                                                    'pending'   => 'bg-primary',
                                                    'completed' => 'bg-success',
                                                    'failed'    => 'bg-danger',
                                                    'partial'   => 'bg-warning text-dark',
                                                    'cancelled' => 'bg-secondary',
                                                    'reset'     => 'bg-purple text-white',
                                                    'abandoned' => 'bg-dark text-warning',
                                                    default     => 'bg-dark'
                                                };
                                            @endphp

                                            <td data-value="{{ $status }}">
                                                <span class="badge {{ $statusClass }}">
                                                    {{ ucfirst($status) }}
                                                </span>
                                            </td>

                                            {{-- Remarks --}}
                                            <td style="white-space: pre-line; padding: 0.75rem; min-width: 300px;">
                                                {{ $log->remarks ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- Display for Empty Tables --}}
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-4">
                                                <i class="fa-solid fa-folder-open fa-lg me-2"></i>
                                                No import logs found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </section>

        {{-- ⭐ Custom Purple Badge Style --}}
        <style>
            .bg-purple {
                background-color: #6f42c1 !important;
                color: #fff !important;
            }
        </style>

        {{-- Hidden Global Delete Form (for all tables) --}}
        <form id="globalDeleteForm" method="POST" style="display:none;">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </form>
        {{-- Hidden form for Moving Invalid Entries Table to Valid Entries Table --}}
        <form id="moveToVerifiedForm" action="{{ route('volunteer.import.moveInvalidToValid') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
    
    {{-- Modals --}}
    @include('layouts.modals.guides.volunteer_import.import_guide_modal')
    @include('layouts.modals.guides.volunteer_import.valid_entries_modal')

    @include('layouts.modals.submit.volunteer_import.reset_import_modal') {{-- Reset --}}
    @include('layouts.modals.submit.volunteer_import.edit_volunteer_modal')
    @include('layouts.modals.submit.volunteer_import.file_upload_modal')
    @include('layouts.modals.submit.volunteer_import.delete_message_modal')
    @include('layouts.modals.submit.volunteer_import.transfer_invalid_entries_modal')
    @include('layouts.modals.submit.volunteer_import.submit_valid_entries_modal')

    @include('layouts.modals.submit.volunteer_import.view_schedule_modal')
    @include('layouts.modals.submit.volunteer_import.view_profile_picture_modal')

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Scroll to Last Used Section JS --}}
    <script src="{{ asset('assets/volunteer_import/js/scroll_to_last_table_used.js') }}"></script>

   
    <link rel="stylesheet" href="{{ asset('assets/modals/css/modal.css') }}">
    <script src="{{ asset('assets/modals/js/modal.js') }}"></script>
    <script src="{{ asset('assets/volunteer_import/js/table_actions.js') }}"></script>


</body>
</html>
