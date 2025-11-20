<!-- Submit Verified Entries Modal -->
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
    <div class="custom-modal error-modal" style="border-top: 5px solid #d9534f;">
        <h3><i class="fa-solid fa-triangle-exclamation" style="color:#d9534f;"></i> Error</h3>
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

    // ⭐ FIXED FORM SELECTOR (your old selector was invalid)
    const validForm = document.getElementById('import-Section-valid')?.querySelector('form');

    const errorModal = document.getElementById('errorModal');
    const errorMessageBox = document.getElementById('errorModalMessage');
    const closeErrorBtn = document.getElementById('closeErrorModal');

    // Stop JS if form or button missing
    if (!openModalBtn || !validForm) return;

    const getTableCheckboxes = () =>
        document.querySelectorAll('#valid-entries-table tbody input[name="selected_valid[]"]');

    const getCheckedTableCheckboxes = () =>
        document.querySelectorAll('#valid-entries-table tbody input[name="selected_valid[]"]:checked');

    // Backend DB duplicate check
    const checkDuplicatesBackend = async (ids) => {
        try {
            const response = await fetch("/check-duplicates", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ ids })
            });
            return await response.json();
        } catch (err) {
            console.error('Error checking duplicates:', err);
            return { duplicates: [] };
        }
    };

    const handleOpenModal = async () => {
        const checkboxes = getTableCheckboxes();

        if (checkboxes.length === 0) {
            errorMessageBox.textContent = "No verified entries to submit.";
            errorModal.classList.add('active');
            return;
        }

        // Auto-check all if none selected
        const checked = getCheckedTableCheckboxes();
        if (checked.length === 0) checkboxes.forEach(cb => cb.checked = true);

        // STEP 1 — BLOCK IF INVALID ENTRIES EXIST
        const invalidCheckboxes = document.querySelectorAll('#invalid-entries-table tbody input[type="checkbox"]');
        if (invalidCheckboxes.length > 0) {
            confirmBtn.disabled = true;
            modal.querySelector('#modalSubmitCount').innerHTML =
                `⚠️ <span style="color:#dc3545; font-weight:600;">${invalidCheckboxes.length} invalid entr${invalidCheckboxes.length > 1 ? 'ies' : 'y'}</span> still exist. Fix them first.`;
            modal.classList.add('active');
            return;
        }

        // STEP 2 — COLLECT ID NUMBERS
        const selectedCbs = Array.from(getCheckedTableCheckboxes());
        const ids = selectedCbs.map(cb => cb.dataset.idNumber?.trim() || "");

        // A1 — Missing ID_NUMBER
        for (let cb of selectedCbs) {
            const id = cb.dataset.idNumber?.trim();
            const row = cb.closest("tr");
            const rowNumber = row ? row.children[1].textContent : "Unknown";

            if (!id) {
                errorMessageBox.innerHTML =
                    `❌ Missing School ID in row <strong>${rowNumber}</strong>.`;
                errorModal.classList.add('active');
                return;
            }
        }

        // B1 — Detect local duplicates
        const duplicatesLocal = ids.filter((id, idx) => ids.indexOf(id) !== idx);

        if (duplicatesLocal.length > 0) {
            errorMessageBox.innerHTML =
                `❌ Duplicate School ID(s) found in selection: <strong>${[...new Set(duplicatesLocal)].join(', ')}</strong>.`;
            errorModal.classList.add('active');
            return;
        }

        // STEP 3 — BACKEND DB DUPLICATE CHECK
        confirmBtn.disabled = true;
        modal.querySelector('#modalSubmitCount').innerHTML = "Checking for duplicates...";

        const result = await checkDuplicatesBackend(ids);
        const duplicatesDB = result.duplicates.map(id => id.toString().trim());

        // Uncheck duplicate rows if any
        duplicatesDB.forEach(id => {
            const cb = document.querySelector(`#valid-entries-table tbody input[data-id-number="${id}"]`);
            if (cb) cb.checked = false;
        });

        if (duplicatesDB.length > 0) {
            errorMessageBox.innerHTML =
                `❌ These School ID(s) already exist in the database:<br><strong>${duplicatesDB.join(', ')}</strong>`;
            errorModal.classList.add('active');
            confirmBtn.disabled = true;
            return;
        }

        // STEP 4 — READY TO SUBMIT
        const finalSelected = Array.from(getCheckedTableCheckboxes());

        const totalSelected = finalSelected.length;

        if (totalSelected === 0) {
            errorMessageBox.innerHTML = "❌ No entries left to submit after duplicate filtering.";
            errorModal.classList.add('active');
            return;
        }

        // Success message
        modal.querySelector('#modalSubmitCount').innerHTML =
            `Submit <span style="color:#28a745; font-weight:600;">${totalSelected}</span> verified entr${totalSelected > 1 ? 'ies' : 'y'} to the database?`;

        confirmBtn.disabled = false;
        modal.classList.add('active');
    };

    // Event Listeners
    openModalBtn.addEventListener('click', handleOpenModal);

    cancelBtn.addEventListener('click', () => modal.classList.remove('active'));

    confirmBtn.addEventListener('click', () => {
        if (confirmBtn.disabled) return;

        const checked = getCheckedTableCheckboxes();

        if (checked.length === 0) {
            errorMessageBox.textContent = "No entries selected to submit.";
            errorModal.classList.add('active');
            return;
        }

        validForm.submit();
        modal.classList.remove('active');
    });

    if (closeErrorBtn) {
        closeErrorBtn.addEventListener('click', () => errorModal.classList.remove('active'));
    }
});
</script>
