<!-- Styles -->
<style>
/* Modal Base */
.edit-volunteer-modal {
    position: fixed;
    inset: 0;
    display: none;
    z-index: 9999;
    font-family: 'Segoe UI', Roboto, sans-serif;
}
.edit-volunteer-modal.is-open {
    display: flex;
    justify-content: center;
    align-items: center;
}
.edit-volunteer-modal .modal-overlay {
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.55);
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Modal Content Scrollable */
.edit-volunteer-modal .modal-content {
    background: #fff;
    border-radius: 16px;
    width: 90%;
    max-width: 650px;
    max-height: 90vh; /* limits height to 90% of viewport */
    padding: 2rem;
    box-shadow: 0 12px 40px rgba(0,0,0,0.25);
    text-align: left;
    animation: slideIn 0.3s ease forwards;
    overflow-y: auto; /* enables vertical scrolling if content exceeds max-height */
}

/* Optional: smoother scroll on mobile */
.edit-volunteer-modal .modal-content {
    -webkit-overflow-scrolling: touch;
}

/* Media Query for very small screens */
@media(max-height:600px){
    .edit-volunteer-modal .modal-content {
        max-height: 95vh;
        padding: 1.5rem;
    }
}

/* Already existing responsive for input grid */
@media(max-width:500px){
    .input-grid { 
        grid-template-columns: 1fr; 
    }
}

/* Header */
.modal-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}
.modal-header h2 {
    font-size: 1.6rem;
    color: #B2000C;
    margin: 0;
}
.modal-icon {
    font-size: 2rem;
    color: #B2000C;
    transition: transform 0.25s ease, color 0.25s ease;
}
.modal-icon:hover {
    transform: rotate(-15deg);
}

/* Input Grid */
.input-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.volunteer-info {
    position: relative;
    display: flex;
    flex-direction: column;
}
.volunteer-info label {
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 0.25rem;
    font-weight: 500;
}
.volunteer-info input {
    width: 100%;
    padding: 0.6rem 0.75rem 0.6rem 2.5rem;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 1rem;
    transition: all 0.25s ease;
}
.volunteer-info input:focus {
    border-color: #B2000C;
    background: #fff0f0;
    outline: none;
}
/* Input Icons */
.input-icon {
    position: absolute;
    left: 0.75rem;
    top: 70%; /* center vertically */
    transform: translateY(-50%) rotate(0deg); /* default rotation 0 */
    color: #942a2a;
    font-size: 1.2rem;
    pointer-events: none;
    transition: transform 0.25s ease, color 0.25s ease;
}

/* Rotate icon on hover/focus */
.volunteer-info:hover .input-icon,
.volunteer-info input:focus + .input-icon {
    transform: translateY(-50%) rotate(-15deg);
    color: #B2000C;
}

/* Modal Footer */
.modal-footer {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}
/* Button Base */
.modal-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.65rem 1.8rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    transition: all 0.25s ease;
    height: 50px;
}
/* Cancel Button */
.modal-btn.cancel {
    background-color: #f1f1f1;
    color: #333;
    transition: all 0.25s ease;
}
.modal-btn.cancel:hover {
    background-color: #e0e0e0;
    transform: scale(1.05); /* subtle scale instead of tilt */
}

/* Save Button */
.modal-btn.save {
    background-color: #B2000C;
    color: #fff;
    transition: all 0.25s ease;
}
.modal-btn.save:hover {
    background-color: #7F0008;
    transform: scale(1.05); /* subtle scale on hover */
}


/* Animations */
@keyframes slideIn {
    from { opacity:0; transform: translateY(-20px) scale(0.96); }
    to { opacity:1; transform: translateY(0) scale(1); }
}

/* Responsive */
@media(max-width:500px){
    .input-grid { grid-template-columns: 1fr; }
}
</style>

<!-- Volunteers Data -->
<script>
window.volunteersData = {
    invalid: @json(session('invalidEntries', [])),
    valid: @json(session('validEntries', []))
};
</script>

<!-- Edit Volunteer Modal -->
<div class="edit-volunteer-modal" id="editVolunteerModal">
  <div class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <i class="fa-solid fa-user-edit modal-icon"></i>
        <h2>Edit Volunteer</h2>
      </div>
      <form id="editVolunteerForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body input-grid">
          <div class="volunteer-info">
            <label>Full Name</label>
            <input type="text" id="full_name" name="full_name" placeholder="Full Name" required>
            <i class="fa-solid fa-user input-icon"></i>
          </div>
          <div class="volunteer-info">
            <label>School ID</label>
            <input type="text" id="id_number" name="id_number" placeholder="School ID" required>
            <i class="fa-solid fa-id-card input-icon"></i>
          </div>
          <div class="volunteer-info">
            <label>Course</label>
            <input type="text" id="course" name="course" placeholder="Course" required>
            <i class="fa-solid fa-graduation-cap input-icon"></i>
          </div>
          <div class="volunteer-info">
            <label>Year Level</label>
            <input type="text" id="year_level" name="year_level" placeholder="Year Level" required>
            <i class="fa-solid fa-calendar input-icon"></i>
          </div>
          <div class="volunteer-info">
            <label>Contact Number</label>
            <input type="text" id="contact_number" name="contact_number" placeholder="Contact Number" required>
            <i class="fa-solid fa-phone input-icon"></i>
          </div>
          <div class="volunteer-info">
            <label>Emergency Contact</label>
            <input type="text" id="emergency_contact" name="emergency_contact" placeholder="Emergency Contact" required>
            <i class="fa-solid fa-phone-volume input-icon"></i>
          </div>
          <div class="volunteer-info">
            <label>Email</label>
            <input type="email" id="email" name="email" placeholder="Email" required>
            <i class="fa-solid fa-envelope input-icon"></i>
          </div>
          <div class="volunteer-info">
            <label>FB Messenger</label>
            <input type="text" id="fb_messenger" name="fb_messenger" placeholder="FB Messenger">
            <i class="fa-brands fa-facebook-messenger input-icon"></i>
          </div>
          <div class="volunteer-info">
            <label>Barangay</label>
            <input type="text" id="barangay" name="barangay" placeholder="Barangay">
            <i class="fa-solid fa-house input-icon"></i>
          </div>
          <div class="volunteer-info">
            <label>District</label>
            <input type="text" id="district" name="district" placeholder="District">
            <i class="fa-solid fa-map-location-dot input-icon"></i>
          </div>
        </div>
        <div class="modal-footer">
           <button type="button" class="modal-btn cancel" onclick="closeEditVolunteerModal()">
                <i class="fa-solid fa-xmark"></i> Cancel
            </button>

            <button type="submit" class="modal-btn save">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
(function() {
  const modal = document.getElementById('editVolunteerModal');
  const form = document.getElementById('editVolunteerForm');
  let escHandler;

  // Open modal and populate fields
  window.openEditVolunteerModal = function(type, index) {
    const volunteer = (window.volunteersData[type] || [])[index] || {};
    if (!volunteer) return;

    // Populate inputs
    ['full_name', 'id_number', 'course', 'year_level', 
     'contact_number', 'emergency_contact', 'email', 
     'fb_messenger', 'barangay', 'district'].forEach(key => {
        const input = document.getElementById(key);
        if (input) input.value = volunteer[key] || '';
    });

    // Set form action to your update route
    form.action = `/volunteer-import/volunteer/update-entry/${index}/${type}`;

    // Show modal
    modal.classList.add('is-open');
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';

    // ESC closes modal
    escHandler = e => { if (e.key === 'Escape') closeEditVolunteerModal(); };
    document.addEventListener('keydown', escHandler);
  };

  // Close modal
  window.closeEditVolunteerModal = function() {
    modal.classList.remove('is-open');
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
    if (escHandler) {
      document.removeEventListener('keydown', escHandler);
      escHandler = null;
    }
  };

  // Click outside modal closes it
  modal.querySelector('.modal-overlay').addEventListener('click', e => {
    if (e.target === modal.querySelector('.modal-overlay')) closeEditVolunteerModal();
  });

})();
</script>
