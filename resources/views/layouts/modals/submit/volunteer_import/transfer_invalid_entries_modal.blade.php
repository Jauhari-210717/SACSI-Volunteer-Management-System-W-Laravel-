<style>
  .custom-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
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

  /* Header styles forced red */
  .custom-modal.confirm-modal h3 {
    color: #dc3545 !important; /* Red */
    font-size: 1.4rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }

  .custom-modal.confirm-modal h3 i {
    color: #dc3545 !important; /* Red icon */
  }

  .custom-modal p {
    color: #555;
    font-size: 1rem;
    margin-bottom: 1.5rem;
  }

  .modal-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
  }

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

  /* Primary button forced red */
  .modal-actions .btn.btn-primary {
    background-color: #dc3545 !important;
    color: #fff !important;
  }

  .modal-actions .btn.btn-primary:hover {
    background-color: #b2000c !important;
    transform: translateY(-2px);
  }

  .modal-actions .btn.btn-secondary {
    background-color: #e5e5e5;
    color: #333;
  }

  .modal-actions .btn.btn-secondary:hover {
    background-color: #d0d0d0;
    transform: translateY(-2px);
  }

  /* Animations */
  @keyframes slideIn {
    from { opacity: 0; transform: translateY(-20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>

{{-- Move to Verified Modal --}}
<div id="modal2" class="custom-modal-overlay">
  <div class="custom-modal confirm-modal">
    <h3>
      <i class="fa-solid fa-triangle-exclamation"></i> Move to Verified
    </h3>
    <p id="modalRowCount">Do you want to move selected entries to the Verified list?</p>
    <div class="modal-actions">
      <button class="btn btn-primary" id="confirmMoveBtn">
        <i class="fa-solid fa-check"></i> Yes, Move
      </button>
      <button class="btn btn-secondary" id="cancelMoveBtn">
        <i class="fa-solid fa-xmark"></i> Cancel
      </button>
    </div>
  </div>
</div>


<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal2');
    const openModalBtn = document.getElementById('openMoveModalBtn');
    const confirmBtn = document.getElementById('confirmMoveBtn');
    const cancelBtn = document.getElementById('cancelMoveBtn');
    const hiddenForm = document.getElementById('moveToVerifiedForm');
    const modalRowCount = document.getElementById('modalRowCount');

    // Open modal and check all rows
    openModalBtn.addEventListener('click', () => {
        const allCheckboxes = document.querySelectorAll('#invalid-entries-table tbody input[type="checkbox"]');

        if (allCheckboxes.length === 0) {
            alert('No invalid entries to move.');
            return;
        }

        // Check all checkboxes
        allCheckboxes.forEach(cb => cb.checked = true);

        // Update modal text with row count
        modalRowCount.textContent = `You are about to move ${allCheckboxes.length} ${allCheckboxes.length > 1 ? 'entries' : 'entry'} to the Verified list.`;

        modal.classList.add('active');
    });

    // Close modal
    const closeModal = () => {
        modal.classList.remove('active');
    };
    cancelBtn.addEventListener('click', closeModal);

    // Confirm action
    confirmBtn.addEventListener('click', () => {
        // Clear previous hidden inputs
        hiddenForm.innerHTML = '@csrf';

        // Copy checked checkboxes into hidden form
        const selectedCheckboxes = document.querySelectorAll('#invalid-entries-table tbody input[type="checkbox"]:checked');
        selectedCheckboxes.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_invalid[]';
            input.value = cb.value;
            hiddenForm.appendChild(input);
        });

        hiddenForm.submit();
        closeModal();
    });
  });
</script>