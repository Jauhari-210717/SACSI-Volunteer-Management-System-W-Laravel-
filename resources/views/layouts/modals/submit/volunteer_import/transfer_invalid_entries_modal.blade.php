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

  .custom-modal.confirm-modal h3 {
    color: #dc3545 !important;
    font-size: 1.4rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }

  .custom-modal.confirm-modal h3 i {
    color: #dc3545 !important;
  }

  .custom-modal.success-modal h3 {
      color: #28a745 !important;
      font-size: 1.6rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
  }

  .custom-modal.success-modal h3 i {
      color: #28a745 !important;
      font-size: 2rem;
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

  .modal-actions .btn.btn-primary {
    background-color: #b2000c  !important;
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

  #successOkBtn {
      background-color: #b2000c !important;
      color: #fff !important;
      border: none !important;
  }

  #successOkBtn:hover {
      background-color: #8a0009 !important;
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

<!-- Confirm Move Modal -->
<div id="modal2" class="custom-modal-overlay">
  <div class="custom-modal confirm-modal">
    <h3>
      <i class="fa-solid fa-exclamation-triangle"></i> Confirm Move
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

<!-- Success Modal -->
<div id="successModal" class="custom-modal-overlay">
  <div class="custom-modal success-modal">
    <h3>
      <i class="fa-solid fa-circle-check"></i> Success!
    </h3>
    <p id="successModalMessage">{{ session('success') }}</p>

    <div class="modal-actions">
      <button class="btn btn-primary" id="successOkBtn">
          OK
      </button>
    </div>
  </div>
</div>

<!-- No Entries Modal -->
<div id="noEntryModal" class="custom-modal-overlay">
  <div class="custom-modal confirm-modal">
    <h3>
      <i class="fa-solid fa-circle-xmark"></i> Nothing to Move
    </h3>
    <p>No invalid entries are available to move.</p>

    <div class="modal-actions">
      <button class="btn btn-secondary" id="noEntryOkBtn">
        <i class="fa-solid fa-check"></i> OK
      </button>
    </div>
  </div>
</div>

@if(session('show_success_modal') && session('success'))
<script>
    window.serverSuccessMessage = `{!! session('success') !!}`;
</script>
@endif

<script>
document.addEventListener("DOMContentLoaded", () => {

    const moveModal      = document.getElementById("modal2");
    const successModal   = document.getElementById("successModal");
    const noEntryModal   = document.getElementById("noEntryModal");

    const openMoveBtn    = document.getElementById("openMoveModalBtn");
    const confirmMoveBtn = document.getElementById("confirmMoveBtn");
    const cancelMoveBtn  = document.getElementById("cancelMoveBtn");

    const successOkBtn   = document.getElementById("successOkBtn");
    const noEntryOkBtn   = document.getElementById("noEntryOkBtn");

    const modalRowCount  = document.getElementById("modalRowCount");
    const successMessage = document.getElementById("successModalMessage");

    const hiddenForm     = document.getElementById("moveToVerifiedForm");

    if (!hiddenForm) return;

    /* Helpers */
    const getInvalidCheckboxes = () =>
        document.querySelectorAll('#invalid-entries-table tbody input[name="selected_invalid[]"]');

    const resetHiddenForm = () => {
        const token = hiddenForm.querySelector('input[name="_token"]');
        hiddenForm.innerHTML = "";
        if (token) hiddenForm.appendChild(token);
    };


    /* 1. Move ALL invalid → open modal */
    if (openMoveBtn) {
        openMoveBtn.addEventListener("click", () => {
            const boxes = getInvalidCheckboxes();

            if (boxes.length === 0) {
                noEntryModal.classList.add("active");
                return;
            }

            boxes.forEach(cb => cb.checked = true);

            modalRowCount.innerHTML = 
                `Move <strong style="color:#bc3000">${boxes.length} entr${boxes.length>1?"ies":"y"}</strong> to the Verified list?`;

            moveModal.classList.add("active");
        });
    }


    /* 2. Confirm move */
    confirmMoveBtn?.addEventListener("click", () => {

        const boxes = getInvalidCheckboxes();
        resetHiddenForm();

        boxes.forEach(cb => {
            if (cb.checked) {
                const i = document.createElement("input");
                i.type = "hidden";
                i.name = "selected_invalid[]";
                i.value = cb.value;
                hiddenForm.appendChild(i);
            }
        });

        hiddenForm.submit();
    });


    /* 3. Cancel modal */
    cancelMoveBtn?.addEventListener("click", () => moveModal.classList.remove("active"));

    /* 4. Close empty modal */
    noEntryOkBtn?.addEventListener("click", () => noEntryModal.classList.remove("active"));


    /* 5. SUCCESS MODAL — highlight only important parts */
    if (window.serverSuccessMessage && successMessage) {

        let msg = window.serverSuccessMessage;

        // CONDITION: If message contains comma-separated entries → treat as "Move ALL"
        if (msg.includes("Moved Volunteer Entry") && msg.includes(",")) {
            const count = msg.split("Moved Volunteer Entry").length - 1;
            msg = `${count} entries successfully moved to Verified.`;
        }

        // highlight names & entry numbers like "#3 John"
        msg = msg.replace(/(Entry\s?#?\d+[^,]*)/gi,
            `<span style="color:#b2000c; font-weight:600;">$1</span>`
        );

        // Set formatted HTML
        successMessage.innerHTML = msg;

        successModal.classList.add("active");
    }

    successOkBtn?.addEventListener("click", () => successModal.classList.remove("active"));
});


/* 6. Instant move single row */
function submitMoveToValid(button) {
    const row = button.closest("tr");
    if (!row) return;

    const checkbox = row.querySelector('input[name="selected_invalid[]"]');
    if (!checkbox) return;

    const form = document.getElementById("moveToVerifiedForm");
    if (!form) return;

    const token = form.querySelector('input[name="_token"]');
    form.innerHTML = "";
    if (token) form.appendChild(token);

    const hidden = document.createElement("input");
    hidden.type = "hidden";
    hidden.name = "selected_invalid[]";
    hidden.value = checkbox.value;

    form.appendChild(hidden);
    form.submit();
}


/* 7. Move VALID → INVALID */
function moveToInvalid(index) {
    window.location.href =
        `/volunteer-import/move-valid-to-invalid/${index}#invalid-entries-table`;
}
</script>
