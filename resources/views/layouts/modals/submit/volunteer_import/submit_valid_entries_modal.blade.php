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
  color: #0d6efd; /* Bootstrap primary blue */
  font-size: 1.4rem;
  margin-bottom: 1rem;
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
}

.modal-actions .btn-primary {
  background-color: #0d6efd;
  color: #fff;
}

.modal-actions .btn-primary:hover {
  background-color: #0b5ed7;
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

{{-- Confirm Submit --}}
<div id="modal1" class="custom-modal-overlay">
  <div class="custom-modal confirm-modal">
    <h3><i class="fa-solid fa-database me-2 text-success"></i> Submit to Database</h3>
    <p>Are you sure you want to submit verified entries to the database?</p>
    <div class="modal-actions">
      <button class="btn btn-danger" onclick="confirmAction('submit')">
        <i class="fa-solid fa-check"></i> Yes, Submit
      </button>
      <button class="btn btn-secondary" onclick="closeModal('modal1')">
        Cancel
      </button>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('confirmSubmitModal');
  const confirmBtn = document.getElementById('confirmSubmitBtn');
  const cancelBtn = document.getElementById('cancelSubmitBtn');

  // Open modal
  window.openConfirmModal = () => {
    modal.classList.add('active');
  };

  // Close modal
  const closeModal = () => {
    modal.classList.remove('active');
  };

  cancelBtn.addEventListener('click', closeModal);

  // Confirm action
  confirmBtn.addEventListener('click', () => {
    console.log('Submitting entries to database...');
    closeModal();
  });
});

</script>