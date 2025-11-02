<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management - Import CSV, Invalid Entries & Import Logs</title>

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('assets/volunteer_import/css/volunteer_import.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Reusable-Searchbar+Filter.css') }}">

    {{-- Bootstrap & Font Awesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    {{-- Loader & Navbar --}}
    @include('layouts.page_loader')
    @include('layouts.navbar')

    <div class="scroll-container">

        {{-- =====================
            1. IMPORT & VALIDATION
        ====================== --}}
        <section id="handling-Section">
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

                        {{-- File Upload --}}
                        <form action="" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="import-controls">
                                <div class="file-upload">
                                    <div class="input-group">
                                        <input type="file" name="csv_file" class="form-control d-none" id="file-upload" accept=".csv" required>
                                        <button class="btn btn-outline-secondary rounded-1" type="button" id="file-upload-button">
                                            <i class="fa-solid fa-file-csv me-2"></i> Choose File
                                        </button>
                                        <span class="file-path" id="file-path">No file chosen</span>
                                    </div>
                                </div>

                                <div class="uploader-info">
                                    <input type="text" class="form-control" value="Uploading as {{ Auth::user()->name }}" readonly>
                                    <button type="submit" class="btn btn-outline-secondary import-btn">
                                        <i class="fa-solid fa-upload"></i> Import
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="action-message d-none">Action Message Here</div>
                        <hr class="red-hr">

                        {{-- Invalid Entries Table --}}
                        <div class="data-table-container">
                            <div class="table-controls mb-0">
                                <div class="table-actions d-flex align-items-center justify-content-center gap-2">
                                    <h3>Invalid Entries</h3>
                                </div>
                               {{-- @include('import_volunteers.partials.searchbar_filter1')--}}
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover volunteer-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>File Name</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Valid</th>
                                            <th>Invalid</th>
                                            <th>Uploaded By</th>
                                            <th>Uploaded At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($imports as $import)
                                            <tr>
                                                <td>{{ $import->import_id }}</td>
                                                <td>{{ $import->filename }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $import->status === 'Validated' ? 'success' : 'warning' }}">
                                                        {{ $import->status }}
                                                    </span>
                                                </td>
                                                <td>{{ $import->total_records ?? '-' }}</td>
                                                <td>{{ $import->valid_records ?? '-' }}</td>
                                                <td>{{ $import->invalid_records ?? '-' }}</td>
                                                <td>{{ $import->admin->name ?? 'Unknown' }}</td>
                                                <td>{{ $import->created_at->format('M d, Y h:i A') }}</td>
                                                <td>
                                                    @if ($import->status === 'Pending')
                                                        <a href="{{ route('volunteer-import.validate', $import->import_id) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fa-solid fa-check"></i> Validate
                                                        </a>
                                                    @elseif ($import->status === 'Validated')
                                                        <a href="{{ route('volunteer-import.submit', $import->import_id) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fa-solid fa-database"></i> Submit
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">No import files yet.</td>
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

        {{-- =====================
            2. IMPORT LOGS
        ====================== --}}
        <section id="importlog-Section">
            <div class="database-container">
                <main class="database-main">
                    <div class="import-section">
                        <h2 class="section-title"><i class="fas fa-history"></i> Import Logs</h2>
                        <hr class="red-hr">

                        <div class="data-table-container">
                            <div class="table-controls mb-0">
                                <div class="table-actions d-flex align-items-center justify-content-center gap-2">
                                    <h3>Import History</h3>
                                </div>
                              {{--  @include('import_volunteers.partials.searchbar_filter2') --}}
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover volunteer-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>File Name</th>
                                            <th>Uploaded By</th>
                                            <th>Uploaded At</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($importLogs as $log)
                                            <tr>
                                                <td>{{ $log->id }}</td>
                                                <td>{{ $log->filename }}</td>
                                                <td>{{ $log->admin->name ?? 'Unknown' }}</td>
                                                <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                                <td>{{ $log->total_records }}</td>
                                                <td>{{ $log->status }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No import logs found.</td>
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
    </div>

    {{-- Modals 
    @include('layouts.modals.submit.import_guide_modal')
    @include('layouts.modals.submit.valid_entries_modal')
    @include('layouts.modals.guide.import_guide_modal')
    @include('layouts.modals.guide.valid_entries_modal')
--}}
    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/import_volunteer.js') }}"></script>
</body>
</html>
