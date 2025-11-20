@php
  $pageTitle = "Event Scheduler"; //Header Title
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Creation Page</title>
  <!--Main CSS-->
  <link rel="stylesheet" href="{{ asset('assets/create_event/css/create_event.css') }}">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    @include('layouts.page_loader')
    @include('layouts.navbar')

    <div class="Wrapper">
        <section class="edit-section" role="region" aria-label="Create Event">
        <h2>Create Event</h2>

        <!-- Input & Dropdown Grid -->
        <div class="input-grid">
            <div class="volunteer-info full-width">
            <span class="icon"><i class="fa-solid fa-pencil"></i></span>
            <input type="text" placeholder="Event Title">
            </div>

            <div class="volunteer-info">
            <span class="icon"><i class="fa-regular fa-clock"></i></span>
            <input type="datetime-local" class="datetime-input">
            </div>

            <div class="volunteer-info">
            <span class="icon"><i class="fa-solid fa-location-dot"></i></span>
            <input type="text" placeholder="Location">
            </div>

            <!-- Volunteer Tally -->
            <div class="volunteer-info">
            <span class="icon"><i class="fa-solid fa-users"></i></span>
            <input type="number" placeholder="Expected Volunteers" min="0">
            </div>

            <!-- Barangay Dropdown -->
            <div class="volunteer-info">
            <span class="icon"><i class="fa-solid fa-house"></i></span>
            <div class="custom-select" data-field="barangay">
                <div class="custom-select-trigger">Sort by Barangay</div>
                <div class="custom-options">
                <span class="custom-option" data-value="remove"><i class="fa-solid fa-ban"></i> Remove Barangay filter</span>
                <span class="custom-option" data-value="Cabatangan"><i class="fa-solid fa-location-dot"></i> Cabatangan</span>
                <span class="custom-option" data-value="Sta.Maria"><i class="fa-solid fa-location-dot"></i> Sta.Maria</span>
                <span class="custom-option" data-value="Pasonanca"><i class="fa-solid fa-location-dot"></i> Pasonanca</span>
                <span class="custom-option" data-value="Tumaga"><i class="fa-solid fa-location-dot"></i> Tumaga</span>
                <span class="custom-option" data-value="Tetuan"><i class="fa-solid fa-location-dot"></i> Tetuan</span>
                <span class="custom-option" data-value="Mercedes"><i class="fa-solid fa-location-dot"></i> Mercedes</span>
                <span class="custom-option" data-value="Canelar"><i class="fa-solid fa-location-dot"></i> Canelar</span>
            </div>
            </div>
            </div>
            
            <!-- District Dropdown -->
            <div class="volunteer-info">
            <span class="icon"><i class="fa-solid fa-map-location-dot"></i></span>
            <div class="custom-select" data-field="district">
                <div class="custom-select-trigger">Sort by District</div>
                <div class="custom-options">
                <span class="custom-option" data-value="remove"><i class="fa-solid fa-ban"></i> Remove District filter</span>
                <span class="custom-option" data-value="District 1"><i class="fa-solid fa-location-dot"></i> District 1</span>
                <span class="custom-option" data-value="District 2"><i class="fa-solid fa-location-dot"></i> District 2</span>
                <span class="custom-option" data-value="District 3"><i class="fa-solid fa-location-dot"></i> District 3</span>
                </div>
            </div>
            </div>
            
            <!-- Event Type -->
            <div class="volunteer-info">
            <span class="icon"><i class="fa-solid fa-calendar-check"></i></span> <!-- main field icon -->
            <div class="custom-select" data-field="event-type">
                <div class="custom-select-trigger">Select Event Type</div>
                <div class="custom-options">
                <span class="custom-option" data-value="remove"><i class="fa-solid fa-ban"></i> Remove Event Type</span>
                <span class="custom-option" data-value="cleanup"><i class="fa-solid fa-broom"></i> Cleanup Drive</span>
                <span class="custom-option" data-value="seminar"><i class="fa-solid fa-chalkboard-teacher"></i> Seminar</span>
                <span class="custom-option" data-value="fundraise"><i class="fa-solid fa-hand-holding-dollar"></i> Fundraise</span>
                </div>
            </div>
            </div>

            <!-- Event Organizers -->
            <div class="volunteer-info">
            <span class="icon"><i class="fa-solid fa-user"></i></span>
            <div id="organizers-wrapper">
                <div class="organizer-row">
                <input type="text" placeholder="Organizer Name" class="organizer-input">
                </div>
            </div>
            <button type="button" class="add-organizer-btn" onclick="addOrganizer()">+ Add</button>
            </div>

            <!-- Event Creator -->
            <div class="volunteer-info">
            <span class="icon"><i class="fa-solid fa-user-pen"></i></span>
            <input type="text" placeholder="Creating Event as Ms.Joy" readonly>
            </div>
            
        </div>

        
        <!-- Separate Row: Description -->
        <div class="description-box">
            <textarea placeholder="Description"></textarea>
        </div>

        <!-- Separate Row: Buttons -->
        <div class="submit-section">
            <button class="open-modal-btn" onclick="openModal('modal1')">Create Event</button>
            <button class="cancel-btn" onclick="window.history.back()">Cancel</button>
        </div>
        </section>

    
        <!-- Modal Wrapper -->
        <div class="modal-wrapper" id="modal1">
            <div class="modal-overlay">
            <div class="modal-content">
                <h2>Are you sure?</h2>
                <p>This will create a new attendance</p>
                <div class="modal-buttons">
                <button class="modal-btn cancel">Cancel</button>
                <button class="modal-btn confirm" data-action="modal1">Yes</button>
                </div>
            </div>
            </div>
        </div>

        <!-- Organizer Limit Modal -->
        <div class="modal-wrapper" id="organizer-limit-modal">
            <div class="modal-overlay">
            <div class="modal-content">
                <h2>Limit Reached</h2>
                <p>Maximum 3 organizers allowed.</p>
                <div class="modal-buttons">
                <button class="modal-btn confirm" id="organizer-limit-ok">OK</button>
                </div>
            </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
        // Get all dropdowns
        const customSelects = document.querySelectorAll('.custom-select');

        customSelects.forEach(select => {
            const trigger = select.querySelector('.custom-select-trigger');
            const options = select.querySelectorAll('.custom-option');

            // Create a hidden input for form submission if not already there
            if (!select.querySelector('input[type="hidden"]')) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = select.dataset.field || 'custom-select';
            select.appendChild(hidden);
            }
            const hiddenInput = select.querySelector('input[type="hidden"]');

            // Toggle dropdown open/close
            trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            closeAllSelects(select); // close others first
            select.classList.toggle('open');
            });

            // Option click handler
            options.forEach(option => {
            option.addEventListener('click', () => {
                trigger.textContent = option.textContent; // show selected text
                hiddenInput.value = option.dataset.value; // store value
                select.classList.remove('open'); // close dropdown
            });
            });
        });

        // Close all when clicking outside
        document.addEventListener('click', () => closeAllSelects());

        // Helper to close all dropdowns except the current
        function closeAllSelects(except) {
            customSelects.forEach(sel => {
            if (sel !== except) sel.classList.remove('open');
            });
        }
        });
    </script>

    <script>
        // Open modal
        function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.style.display = 'flex';
        }

        // Close modal
        function closeModal(modal) {
        modal.style.display = 'none';
        }

        // Cancel buttons
        document.querySelectorAll('.modal-btn.cancel').forEach(btn => {
        btn.addEventListener('click', e => closeModal(e.target.closest('.modal-wrapper')));
        });

        // Confirm buttons
        document.querySelectorAll('.modal-btn.confirm').forEach(btn => {
        btn.addEventListener('click', e => {
            const modal = e.target.closest('.modal-wrapper');
            const action = btn.dataset.action;

            if (action === 'modal1') {
            window.location.href = '<?php echo $base; ?>/Event_Details/Event_Details.php';
            }

            closeModal(modal);
        });
        });

        // Click outside content to close
        document.querySelectorAll('.modal-wrapper').forEach(wrapper => {
        wrapper.addEventListener('click', e => {
            if (e.target === wrapper) closeModal(wrapper); // wrapper itself
        });
        });
    </script>

  <script>
        function addOrganizer() {
        const wrapper = document.getElementById('organizers-wrapper');
        const rows = wrapper.querySelectorAll('.organizer-row');

        if (rows.length >= 3) {
            // Show organizer limit modal
            const modal = document.getElementById('organizer-limit-modal');
            modal.style.display = 'flex';

            const okBtn = document.getElementById('organizer-limit-ok');
            okBtn.onclick = () => {
            modal.style.display = 'none';
            };

            // Optional: close modal when clicking overlay
            modal.querySelector('.modal-overlay').onclick = (e) => {
            if (e.target === modal.querySelector('.modal-overlay')) {
                modal.style.display = 'none';
            }
            };

            return;
        }

        // Add new organizer row
        const row = document.createElement('div');
        row.className = 'organizer-row';

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Organizer Name';
        input.className = 'organizer-input';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = '×';
        removeBtn.className = 'remove-organizer-btn';
        removeBtn.onclick = () => row.remove();

        row.appendChild(input);
        row.appendChild(removeBtn);
        wrapper.appendChild(row);
        }
    </script>
</body>
</html>

