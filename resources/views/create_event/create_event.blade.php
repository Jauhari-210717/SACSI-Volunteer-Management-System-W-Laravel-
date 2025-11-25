@php
  $pageTitle = "Event Scheduler";
  $savedForm = session('event_form_data', []);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Creation Page</title>

  <!-- Main CSS -->
  <link rel="stylesheet" href="{{ asset('assets/create_event/css/create_event.css') }}">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    .organizer-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .organizer-input { flex: 1; }
    .details-btn, .remove-organizer-btn, .add-organizer-btn {
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        padding: 6px 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    .details-btn { background: #B2000C; color: #fff; }
    .remove-organizer-btn { background: #7f1010; color: #fff; }
    .add-organizer-btn {
        margin-top: 6px;
        background: #fff;
        border: 1px dashed #B2000C;
        color: #B2000C;
        padding: 6px 14px;
    }
    .details-btn:hover, .remove-organizer-btn:hover, .add-organizer-btn:hover { opacity: 0.9; }

    #organizer-details-modal {
        display: none; position: fixed; inset: 0; z-index: 9999;
        justify-content: center; align-items: center;
    }
    #organizer-details-modal .modal-overlay {
        position: absolute; inset: 0; background: rgba(0,0,0,0.55);
    }
    #organizer-details-modal .modal-content {
        position: relative; background: #fff; border-radius: 14px;
        padding: 1.5rem 1.75rem; width: 90%; max-width: 450px;
        box-shadow: 0 14px 40px rgba(0,0,0,0.35);
    }
    #organizer-details-modal h2 { margin: 0 0 1rem; font-size: 1.35rem; text-align: center; color: #B2000C; }
    .organizer-detail-input {
        width: 100%; border-radius: 8px; border: 1px solid #ddd;
        padding: 0.55rem 0.75rem; margin-bottom: 0.75rem; font-size: 0.95rem;
    }
  </style>
</head>

<body>
@include('layouts.page_loader')
@include('layouts.navbar')

<div class="Wrapper">
    <section class="edit-section" role="region" aria-label="Create Event">
        <h2>Create Event</h2>

        <!-- TRUE FORM STARTS HERE -->
        <form id="create-event-form" action="{{ route('events.store') }}" method="POST">
            @csrf

            <div class="input-grid">

                <!-- TITLE -->
                <div class="volunteer-info full-width">
                    <span class="icon"><i class="fa-solid fa-pencil"></i></span>
                    <input type="text" placeholder="Event Title" name="title" required>
                </div>

                <!-- START DATE -->
                <div class="volunteer-info floating-wrapper">
                    <span class="icon"><i class="fa-solid fa-hourglass-start"></i></span>

                    <input type="datetime-local"
                        name="start_datetime"
                        id="start_datetime"
                        class="datetime-input floating-input"
                        required>

                    <label for="start_datetime" class="floating-label">
                        Start Date & Time
                    </label>
                </div>

                <!-- END DATE -->
                <div class="volunteer-info floating-wrapper">
                    <span class="icon"><i class="fa-solid fa-hourglass-end"></i></span>

                    <input type="datetime-local"
                        name="end_datetime"
                        id="end_datetime"
                        class="datetime-input floating-input">
                            
                    <label for="end_datetime" class="floating-label">
                        End Date & Time (Optional)
                    </label>
                </div>

                <style>
                /* Wrapper keeps your original style */
                .floating-wrapper {
                    position: relative;
                }

                /* Floating label */
                .floating-label {
                    position: absolute;
                    left: 260px;                /* keeps label after your icon */
                    top: 40%;
                    transform: translateY(-50%);
                    font-size: 0.9rem;
                    color: #888;
                    pointer-events: none;
                    transition: all 0.18s ease;
                }

                /* When typing OR has value */
                .floating-input:focus + .floating-label,
                .floating-input:not(:placeholder-shown) + .floating-label {
                    top: -10px;
                    left: 35%;
                    padding: 0 4px;
                    font-size: 0.73rem;
                    color: #B2000C;
                }

                /* Input padding so text doesn't overlap icon */
                .floating-input {
                    padding-left: 40px !important;
                }
                </style>
                <!-- VENUE -->
                <div class="volunteer-info">
                    <span class="icon"><i class="fa-solid fa-location-dot"></i></span>
                    <input type="text" placeholder="Location" name="venue">
                </div>

                <!-- MAX VOLUNTEERS -->
                <div class="volunteer-info">
                    <span class="icon"><i class="fa-solid fa-users"></i></span>
                    <input type="number" placeholder="Maximum Volunteers" min="0" name="max_volunteers">
                </div>

                <!-- BARANGAY (location_id) -->
                <div class="volunteer-info">
                    <span class="icon"><i class="fa-solid fa-house"></i></span>

                    <div class="custom-select searchable" id="barangay-select" data-field="location_id">
                        <div class="custom-select-trigger">Select Barangay</div>

                        <div class="custom-options">

                            <div class="search-box">
                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                <input type="text" id="barangaySearchInput" placeholder="Search barangay...">
                            </div>

                            <span class="custom-option" data-value="">
                                <i class="fa-solid fa-ban"></i> Remove Barangay Filter
                            </span>

                            @foreach ($locations as $loc)
                                <span class="custom-option"
                                    data-value="{{ $loc->location_id }}"
                                    data-district="{{ $loc->district_id }}">
                                    <i class="fa-solid fa-location-dot"></i> {{ $loc->barangay }}
                                </span>
                            @endforeach

                        </div>
                    </div>
                </div>

                <!-- DISTRICT -->
                <div class="volunteer-info">
                    <span class="icon"><i class="fa-solid fa-map-location-dot"></i></span>
                    <input type="text" id="districtDisplay" placeholder="District" readonly>
                    <input type="hidden" name="district_id" id="districtHidden">
                </div>

                <!-- EVENT TYPE -->
                <div class="volunteer-info">
                    <span class="icon"><i class="fa-solid fa-calendar-check"></i></span>

                    <div class="custom-select" id="event-type-select" data-field="event_type_id">
                        <div class="custom-select-trigger">Select Event Type</div>

                        <div class="custom-options">
                            <span class="custom-option" data-value="">
                                <i class="fa-solid fa-ban"></i> Remove Event Type
                            </span>

                            @foreach ($eventTypes as $type)
                                <span class="custom-option"
                                    data-value="{{ $type->event_type_id }}"
                                    data-icon="{{ $type->icon_class }}">
                                    <i class="{{ $type->icon_class }}"></i> {{ $type->label }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- ORGANIZERS -->
                <div class="volunteer-info full-width">
                    <span class="icon"><i class="fa-solid fa-user"></i></span>

                    <div id="organizers-wrapper">
                        <div class="organizer-row">
                            <input type="text" name="organizers[name][]" placeholder="Organizer Name" class="organizer-input">

                            <button type="button" class="details-btn" onclick="openOrganizerModal(this)">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <button type="button" class="remove-organizer-btn" onclick="removeOrganizer(this)">×</button>

                            <input type="hidden" name="organizers[email][]" value="">
                            <input type="hidden" name="organizers[contact][]" value="">
                        </div>
                    </div>

                    <button type="button" class="add-organizer-btn" onclick="addOrganizer()">+ Add</button>
                </div>

                <!-- CREATOR -->
                <div class="volunteer-info creator-row full-width">
                    <div class="creator-inner">
                        <span class="icon"><i class="fa-solid fa-user-pen"></i></span>
                        <input type="text" value="Uploading as {{ Auth::guard('admin')->user()->username ?? 'Guest' }}" readonly>
                    </div>
                </div>

            </div>

            <!-- DESCRIPTION -->
            <div class="description-box">
                <textarea name="description" placeholder="Description"></textarea>
            </div>

            <!-- BUTTONS -->
            <div class="submit-section">
                <!-- Open confirm modal -->
                <button class="open-modal-btn" type="button" id="open-create-modal-btn">Create Event</button>
                <button class="cancel-btn" type="button" onclick="window.history.back()">Cancel</button>
            </div>
        </form>
    </section>
</div>


<!-- ===========================
     MODALS
=========================== -->

<!-- VALIDATION ERROR MODAL -->
<div class="modal-wrapper" id="validation-error-modal" style="display:none;">
    <div class="modal-overlay">
        <div class="modal-content" style="max-width:450px;">
            <h2 style="color:#B2000C;">Form Incomplete</h2>
            <hr>

            <p>Please correct the following:</p>

            <ul style="text-align:left; margin-bottom:1rem; color:#B2000C;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <div class="modal-buttons">
                <button class="modal-btn confirm" type="button"
                        onclick="closeModal('validation-error-modal')">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Organizer Details Modal -->
<div class="modal-wrapper" id="organizer-details-modal">
    <div class="modal-overlay">
        <div class="modal-content organizer-modal-box">
            <h2>Organizer Details</h2>
            <input type="email" id="orgEmail" placeholder="Email (optional)" class="organizer-detail-input">
            <input type="text" id="orgContact" placeholder="Contact (optional)" class="organizer-detail-input">
            <div class="modal-buttons">
                <button class="modal-btn cancel" type="button" onclick="closeOrganizerModal()">Cancel</button>
                <button class="modal-btn confirm" type="button" onclick="saveOrganizerDetails()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Organizer Minimum Modal -->
<div class="modal-wrapper" id="organizer-minimum-modal">
    <div class="modal-overlay">
        <div class="modal-content">
            <h2>Organizer Required</h2>
            <hr>
            <p>At least one organizer is required.</p>
            <div class="modal-buttons">
                <button class="modal-btn confirm" type="button" onclick="closeMinimumModal()">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Main Confirmation Modal -->
<div class="modal-wrapper" id="modal1">
    <div class="modal-overlay">
        <div class="modal-content">
            <h2>Are you sure?</h2>
            <hr>
            <p>This will create a new event.</p>
            <div class="modal-buttons">
                <button class="modal-btn cancel" type="button">Cancel</button>
                <!-- IMPORTANT: dedicated ID -->
                <button class="modal-btn confirm" type="button" id="confirm-create-btn">Yes</button>
            </div>
        </div>
    </div>
</div>

<!-- Organizer Limit Modal -->
<div class="modal-wrapper" id="organizer-limit-modal">
    <div class="modal-overlay">
        <div class="modal-content">
            <h2>Limit Reached</h2>
            <hr>
            <p>Maximum 3 organizers allowed.</p>
            <div class="modal-buttons">
                <button class="modal-btn confirm" type="button" id="organizer-limit-ok">OK</button>
            </div>
        </div>
    </div>
</div>

<style>
  /* Make event creator its own full-width row */
  .volunteer-info.creator-row {
      display: flex;
    
  }
  /* Keep its inner content right-aligned */
  .creator-inner {
      display: flex;
      align-items: center;
      gap: 10px;
  }
  /* Prevent full-width stretching of the input */
  .creator-inner input {
      width: auto;
      min-width: 240px;
  }
</style>

<!-- Check for empty feilds-->
@if ($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", () => {
        openModal("validation-error-modal");
    });
</script>
@endif

<!-- Remember Inputs -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    @if(session()->has('event_form_data'))
    const saved = @json(session('event_form_data'));

    // ===============================
    // SIMPLE TEXT INPUTS
    // ===============================
    document.querySelector("input[name='title']").value = saved.title ?? "";
    document.querySelector("textarea[name='description']").value = saved.description ?? "";
    document.querySelector("input[name='venue']").value = saved.venue ?? "";
    document.querySelector("input[name='max_volunteers']").value = saved.max_volunteers ?? "";

    // ===============================
    // DATE / TIME
    // ===============================
    if (saved.start_datetime) {
        document.querySelector("#start_datetime").value = saved.start_datetime;
    }
    if (saved.end_datetime) {
        document.querySelector("#end_datetime").value = saved.end_datetime;
    }

    // ===============================
    // CUSTOM SELECT: BARANGAY
    // ===============================
    if (saved.location_id) {
        const barangaySelect = document.querySelector("#barangay-select");
        const trigger = barangaySelect.querySelector(".custom-select-trigger");
        const hidden = barangaySelect.querySelector("input[type='hidden']");
        const option = barangaySelect.querySelector(`.custom-option[data-value='${saved.location_id}']`);

        if (option) {
            hidden.value = saved.location_id;
            trigger.textContent = option.textContent.trim();

            // Restore district too
            document.querySelector("#districtHidden").value = option.dataset.district ?? "";
            document.querySelector("#districtDisplay").value = option.dataset.district
                ? ("District " + option.dataset.district)
                : "";
        }
    }

    // ===============================
    // CUSTOM SELECT: EVENT TYPE
    // ===============================
    if (saved.event_type_id) {
        const typeSelect = document.querySelector("#event-type-select");
        const trigger = typeSelect.querySelector(".custom-select-trigger");
        const hidden = typeSelect.querySelector("input[type='hidden']");
        const option = typeSelect.querySelector(`.custom-option[data-value='${saved.event_type_id}']`);

        if (option) {
            hidden.value = saved.event_type_id;
            const icon = option.dataset.icon ?? "";
            const label = option.textContent.trim();

            trigger.innerHTML = icon ? `<i class="${icon}"></i> ${label}` : label;
        }
    }

    // ===============================
    // ORGANIZERS
    // ===============================
    if (saved.organizers && saved.organizers.name) {

        const wrapper = document.getElementById("organizers-wrapper");
        wrapper.innerHTML = ""; // Clear existing default row

        saved.organizers.name.forEach((name, i) => {
            // Skip empty rows but preserve structure
            const row = document.createElement("div");
            row.className = "organizer-row";
            row.innerHTML = `
                <input type="text" name="organizers[name][]" placeholder="Organizer Name" class="organizer-input" value="${name ?? ''}">
                <button type="button" class="details-btn" onclick="openOrganizerModal(this)">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button type="button" class="remove-organizer-btn" onclick="removeOrganizer(this)">×</button>
                <input type="hidden" name="organizers[email][]" value="${saved.organizers.email?.[i] ?? ''}">
                <input type="hidden" name="organizers[contact][]" value="${saved.organizers.contact?.[i] ?? ''}">
            `;
            wrapper.appendChild(row);
        });
    }

    @endif

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- ===============================
     CUSTOM SELECT LOGIC
================================ -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const selects = document.querySelectorAll(".custom-select");

    selects.forEach(select => {
        const trigger = select.querySelector(".custom-select-trigger");
        const options = select.querySelectorAll(".custom-option");
        const field = select.dataset.field;

        // Hidden input for actual form value
        let hidden = select.querySelector("input[type='hidden']");
        if (!hidden) {
            hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = field;
            select.appendChild(hidden);
        }

        // Open/close
        trigger.addEventListener("click", e => {
            e.stopPropagation();
            document.querySelectorAll(".custom-select.open").forEach(s => {
                if (s !== select) s.classList.remove("open");
            });
            select.classList.toggle("open");
        });

        // Option click
        options.forEach(option => {
            option.addEventListener("click", () => {
                const value = option.dataset.value || "";
                const label = option.textContent.trim();
                const icon  = option.dataset.icon || "";

                hidden.value = value;

                if (field === "event_type_id") {
                    trigger.innerHTML = icon ? `<i class="${icon}"></i> ${label}` : label;
                } else {
                    trigger.textContent = label;
                }

                select.classList.remove("open");

                // Auto-assign district when barangay chosen
                if (field === "location_id") {
                    const districtVal = option.dataset.district || "";
                    document.getElementById("districtHidden").value  = districtVal;
                    document.getElementById("districtDisplay").value = districtVal
                        ? `District ${districtVal}` : "";
                }
            });
        });

        // Searchable (barangay)
        if (select.classList.contains("searchable")) {
            const searchBox  = select.querySelector(".search-box");
            const searchInput = searchBox.querySelector("input");
            const searchIcon  = select.querySelector(".search-icon");

            // Prevent dropdown from closing
            searchBox.addEventListener("click", e => e.stopPropagation());
            searchInput.addEventListener("click", e => e.stopPropagation());

            searchInput.addEventListener("keyup", () => {
                const q = searchInput.value.toLowerCase();
                const keywords = q.split(" ").filter(w => w.length > 0);

                if (q.length > 0) {
                    searchInput.classList.add("search-active");
                    searchIcon.classList.add("tilt");
                } else {
                    searchInput.classList.remove("search-active");
                    searchIcon.classList.remove("tilt");
                }

                options.forEach(option => {
                    const text  = option.textContent.toLowerCase();
                    const match = keywords.every(k => text.includes(k));
                    option.style.display = match ? "block" : "none";
                });
            });
        }

    });

    // Close all dropdowns when clicking outside
    document.addEventListener("click", () => {
        document.querySelectorAll(".custom-select.open").forEach(s => s.classList.remove("open"));
    });
});
</script>

<!-- ===============================
     MODAL + SUBMIT LOGIC
================================ -->
<script>
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = "flex";
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = "none";
}

document.addEventListener("DOMContentLoaded", () => {
    const form            = document.getElementById("create-event-form");
    const openCreateBtn   = document.getElementById("open-create-modal-btn");
    const confirmCreateBtn= document.getElementById("confirm-create-btn");

    // Open confirmation modal
    if (openCreateBtn) {
        openCreateBtn.addEventListener("click", () => {
            openModal("modal1");
        });
    }

    // Confirm "Yes" -> submit form
    if (confirmCreateBtn) {
        confirmCreateBtn.addEventListener("click", () => {
            if (form) form.submit();
        });
    }

    // Cancel buttons in modals (all)
    document.querySelectorAll(".modal-btn.cancel").forEach(btn => {
        btn.addEventListener("click", e => {
            const modal = e.target.closest(".modal-wrapper");
            if (modal) modal.style.display = "none";
        });
    });

    // Click outside to close (all modals)
    document.querySelectorAll(".modal-wrapper").forEach(modal => {
        modal.addEventListener("click", e => {
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });
    });
});
</script>

<!-- ===============================
     ORGANIZER LOGIC
================================ -->
<script>
let activeOrganizerRow = null;

function addOrganizer() {
    const wrapper = document.getElementById("organizers-wrapper");
    if (wrapper.children.length >= 3) {
        document.getElementById("organizer-limit-modal").style.display = "flex";
        document.getElementById("organizer-limit-ok").onclick = () =>
            document.getElementById("organizer-limit-modal").style.display = "none";
        return;
    }

    const row = document.createElement("div");
    row.className = "organizer-row";
    row.innerHTML = `
        <input type="text" name="organizers[name][]" placeholder="Organizer Name" class="organizer-input">
        <button type="button" class="details-btn" onclick="openOrganizerModal(this)">
            <i class="fa-solid fa-pen-to-square"></i>
        </button>
        <button type="button" class="remove-organizer-btn" onclick="removeOrganizer(this)">×</button>
        <input type="hidden" name="organizers[email][]" value="">
        <input type="hidden" name="organizers[contact][]" value="">
    `;
    wrapper.appendChild(row);
}

function removeOrganizer(btn) {
    const wrapper = document.getElementById("organizers-wrapper");
    if (wrapper.children.length <= 1) {
        document.getElementById("organizer-minimum-modal").style.display = "flex";
        return;
    }
    btn.closest(".organizer-row").remove();
}

function openOrganizerModal(btn) {
    activeOrganizerRow = btn.closest(".organizer-row");
    document.getElementById("orgEmail").value =
        activeOrganizerRow.querySelector("input[name='organizers[email][]']").value;
    document.getElementById("orgContact").value =
        activeOrganizerRow.querySelector("input[name='organizers[contact][]']").value;
    document.getElementById("organizer-details-modal").style.display = "flex";
}

function saveOrganizerDetails() {
    if (!activeOrganizerRow) return;
    activeOrganizerRow.querySelector("input[name='organizers[email][]']").value =
        document.getElementById("orgEmail").value.trim();
    activeOrganizerRow.querySelector("input[name='organizers[contact][]']").value =
        document.getElementById("orgContact").value.trim();
    closeOrganizerModal();
}

function closeOrganizerModal() {
    document.getElementById("organizer-details-modal").style.display = "none";
}

function closeMinimumModal() {
    document.getElementById("organizer-minimum-modal").style.display = "none";
}
</script>

</body>
</html>
