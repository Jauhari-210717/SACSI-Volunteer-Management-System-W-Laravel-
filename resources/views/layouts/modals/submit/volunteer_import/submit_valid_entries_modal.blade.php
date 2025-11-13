<style>
.custom-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    font-family: 'Segoe UI', Roboto, sans-serif;
}
.custom-modal-overlay.active {
    display: flex;
    animation: fadeIn 0.25s ease;
}
.custom-modal {
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    width: 90%;
    max-width: 450px;
    text-align: center;
    box-shadow: 0 12px 30px rgba(0,0,0,0.25);
    animation: slideIn 0.3s ease forwards;
}
.custom-modal h3 {
    color: #dc3545 !important;
    font-size: 1.4rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.custom-modal h3 i { color: #dc3545 !important; }
.custom-modal p { color: #555; font-size: 1rem; margin-bottom: 1.5rem; }
.modal-actions { display: flex; justify-content: center; gap: 1rem; }
.modal-actions .btn {
    padding: 0.6rem 1.4rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.modal-actions .btn-primary {
    background-color: #dc3545 !important;
    color: #fff !important;
}
.modal-actions .btn-primary:hover {
    background-color: #b2000c !important;
    transform: translateY(-2px);
}
.modal-actions .btn-secondary {
    background-color: #e5e5e5;
    color: #333;
}
.modal-actions .btn-secondary:hover {
    background-color: #d0d0d0;
    transform: translateY(-2px);
}
@keyframes slideIn {
    from { opacity: 0; transform: translateY(-20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes fadeIn {
    from { opacity: 0; } 
    to { opacity: 1; }
}
</style>

{{-- Submit Verified Entries Modal --}}
<div id="modalSubmit" class="custom-modal-overlay">
    <div class="custom-modal confirm-modal">
        <h3>
            <i class="fa-solid fa-database"></i> Submit to Database
        </h3>
        <p id="modalSubmitCount">Are you sure you want to submit verified entries to the database?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-primary" id="confirmSubmitBtn">
                <i class="fa-solid fa-check"></i> Yes, Submit
            </button>
            <button type="button" class="btn btn-secondary" id="cancelSubmitBtn">
                <i class="fa-solid fa-xmark"></i> Cancel
            </button>
        </div>
    </div>
</div>

{{-- Hidden form for submission --}}
<form id="submitVerifiedForm" action="{{ route('volunteer.import.validateSave') }}" method="POST" style="display:none;">
    @csrf
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalSubmit');
    const openModalBtn = document.getElementById('openSubmitModalBtn');
    const confirmBtn = document.getElementById('confirmSubmitBtn');
    const cancelBtn = document.getElementById('cancelSubmitBtn');
    const hiddenForm = document.getElementById('submitVerifiedForm');

    if (!openModalBtn) return;

    // Open modal
    openModalBtn.addEventListener('click', () => {
        const checkboxes = document.querySelectorAll('#valid-entries-table tbody input[type="checkbox"]');
        if (checkboxes.length === 0) {
            alert('No verified entries to submit.');
            return;
        }
        // Optionally check all checkboxes automatically
        checkboxes.forEach(cb => cb.checked = true);

        // Update modal message with count
        const count = checkboxes.length;
        modal.querySelector('#modalSubmitCount').textContent = `Are you sure you want to submit ${count} verified entr${count > 1 ? 'ies' : 'y'} to the database?`;

        modal.classList.add('active');
    });

    // Cancel submission
    cancelBtn.addEventListener('click', () => {
        modal.classList.remove('active');
    });

    // Confirm submission
    confirmBtn.addEventListener('click', () => {
        // Remove previous hidden inputs
        hiddenForm.querySelectorAll('input[name="selected_valid[]"]').forEach(i => i.remove());

        const selected = document.querySelectorAll('#valid-entries-table tbody input[type="checkbox"]:checked');
        if (selected.length === 0) {
            alert('No entries selected to submit.');
            return;
        }

        selected.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_valid[]';
            input.value = cb.value;
            hiddenForm.appendChild(input);
        });

        hiddenForm.submit();
        modal.classList.remove('active');
    });
});
</script>
