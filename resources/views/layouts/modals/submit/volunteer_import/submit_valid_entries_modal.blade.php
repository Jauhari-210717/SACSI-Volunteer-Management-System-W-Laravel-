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

<!-- Error Modal -->
<div id="errorModal" class="custom-modal-overlay">
    <div class="custom-modal error-modal">
        <h3><i class="fa-solid fa-triangle-exclamation"></i> Error</h3>
        <p id="errorModalMessage">Something went wrong.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" id="closeErrorModal">
                <i class="fa-solid fa-xmark"></i> Close
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalSubmit');
    const openModalBtn = document.getElementById('openSubmitModalBtn');
    const confirmBtn = document.getElementById('confirmSubmitBtn');
    const cancelBtn = document.getElementById('cancelSubmitBtn');

    // visible form inside the valid section
    const validForm = document.querySelector('#import-Section-valid form');

    // error modal elements (if present)
    const errorModal = document.getElementById('errorModal');
    const errorMessageBox = document.getElementById('errorModalMessage');
    const closeErrorBtn = document.getElementById('closeErrorModal');

    if (!openModalBtn || !validForm) return;

    function getTableCheckboxes() {
        return document.querySelectorAll('#valid-entries-table tbody input[name="selected_valid[]"]');
    }
    function getCheckedTableCheckboxes() {
        return document.querySelectorAll('#valid-entries-table tbody input[name="selected_valid[]"]:checked');
    }

    openModalBtn.addEventListener('click', () => {
        const checkboxes = getTableCheckboxes();
        if (checkboxes.length === 0) {
            // show friendly error
            if (errorModal && errorMessageBox) {
                errorMessageBox.textContent = "No verified entries to submit.";
                errorModal.classList.add('active');
            } else {
                alert('No verified entries to submit.');
            }
            return;
        }

        // Auto-check actual table checkboxes (optional)
        checkboxes.forEach(cb => cb.checked = true);

        const count = checkboxes.length;
        modal.querySelector('#modalSubmitCount').textContent =
            `Are you sure you want to submit ${count} verified entr${count > 1 ? 'ies' : 'y'} to the database?`;

        modal.classList.add('active');
    });

    cancelBtn.addEventListener('click', () => {
        modal.classList.remove('active');
    });

    confirmBtn.addEventListener('click', () => {
        // BEFORE submitting: remove any hidden or non-checkbox inputs named selected_valid[] inside the form
        // (this eliminates duplicates if leftover hidden inputs are present)
        Array.from(validForm.querySelectorAll('input[name="selected_valid[]"]')).forEach(el => {
            if (el.type !== 'checkbox') el.remove();
        });

        const checked = getCheckedTableCheckboxes();
        if (checked.length === 0) {
            if (errorModal && errorMessageBox) {
                errorMessageBox.textContent = "No entries selected to submit.";
                errorModal.classList.add('active');
            } else {
                alert('No entries selected to submit.');
            }
            return;
        }

        // now submit the form — only actual checked checkboxes will be posted
        validForm.submit();
        modal.classList.remove('active');
    });

    if (closeErrorBtn) {
        closeErrorBtn.addEventListener('click', () => errorModal.classList.remove('active'));
    }
});
</script>
