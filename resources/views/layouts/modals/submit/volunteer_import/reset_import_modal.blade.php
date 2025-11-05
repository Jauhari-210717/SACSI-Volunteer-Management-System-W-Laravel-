<style>
/* Modal hidden by default */
.reset-import-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    font-family: 'Segoe UI', Roboto, sans-serif;
}

/* Active modal shows */
.reset-import-modal.active {
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Overlay */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Modal box */
.modal-box {
    background: #fff;
    border-radius: 16px;
    width: 90%;
    max-width: 500px;
    padding: 2rem;
    text-align: center;
    animation: fadeInUp 0.3s ease forwards;
    box-shadow: 0 12px 40px rgba(0,0,0,0.35);
}

/* Header */
.modal-header {
    display: flex;
    align-items: center;
    justify-content: center;
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
}

/* Modal Footer */
.modal-buttons {
    display: flex;
    justify-content: center;
    gap: 16px;
    align-items: center; /* Ensures buttons are aligned in the center */
    width: 100%;
}

/* Fix for form button */
form button.modal-btn {
    margin: 0; /* Remove any form-specific margin */
}

/* Confirm Button */
.modal-btn.confirm {
    background-color: #b2000c;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 22px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.25s ease, transform 0.15s ease;
}

/* Cancel Button */
.modal-btn.cancel {
    background-color: #f1f1f1;
    color: #222;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 10px 22px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.25s ease, transform 0.15s ease;
}

.modal-btn.cancel:hover {
    background-color: #e2e2e2;
    transform: translateY(-1px);
}

.modal-btn.confirm:hover {
    background-color: #8e0009;
    transform: translateY(-1px);
}

/* Animation */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

{{-- Reset Import Modal --}}
<div class="reset-import-modal" id="resetImportModal">
    <div class="modal-overlay" id="resetModalOverlay">
        <div class="modal-box">

            <!-- Modal Header -->
            <div class="modal-header">
                <i class="fa-solid fa-rotate-left modal-icon"></i>
                <h2>Clear Import Preview?</h2>
            </div>

            <!-- Modal Body -->
            <p id="resetModalMessage">Are you sure you want to clear all imported entries from the preview? This action cannot be undone.</p>

            <!-- Modal Footer -->
            <div class="modal-buttons">
                <button type="button" class="modal-btn cancel" id="cancelResetModal">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>

                <form action="{{ route('volunteer.import.reset') }}" method="POST">
                    @csrf
                    <button type="submit" class="modal-btn confirm" id="confirmResetBtn">
                        <i class="fa-solid fa-check"></i> Confirm
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const openBtn = document.getElementById('openResetModal');
    const modal = document.getElementById('resetImportModal');
    const overlay = document.getElementById('resetModalOverlay');
    const cancelBtn = document.getElementById('cancelResetModal');
    const modalBody = document.getElementById('resetModalMessage');
    const confirmBtn = document.getElementById('confirmResetBtn');

    if (!openBtn || !modal || !overlay || !cancelBtn || !modalBody || !confirmBtn) return;

    // Open modal
    openBtn.addEventListener('click', () => {
        const validCount = {{ session()->has('validEntries') ? count(session('validEntries')) : 0 }};
        const invalidCount = {{ session()->has('invalidEntries') ? count(session('invalidEntries')) : 0 }};
        const total = validCount + invalidCount;

        modalBody.textContent = `Are you sure you want to clear all imported entries from the preview? This action cannot be undone. Total rows to clear: ${total}`;

        confirmBtn.disabled = total === 0;

        modal.classList.add('active');

        const invalidTable = document.getElementById('invalid-entries-table');
        const firstRow = invalidTable?.querySelector('tbody tr');
        window.lastUsedTable = { type: 'invalid', index: firstRow ? 0 : null };
        sessionStorage.setItem('lastUsedTable', JSON.stringify(window.lastUsedTable));
    });

    // Close modal
    cancelBtn.addEventListener('click', () => modal.classList.remove('active'));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) modal.classList.remove('active'); });
    document.addEventListener('keydown', (e) => { if (e.key === "Escape") modal.classList.remove('active'); });

    // Show success info after reset
    @if(session('resetInfo'))
        const info = @json(session('resetInfo'));
        alert(`Reset Complete!\nFile: ${info.file_name}\nRows Cleared: ${info.total_cleared}\nLog ID: ${info.log_id}`);
    @endif
});
</script>

