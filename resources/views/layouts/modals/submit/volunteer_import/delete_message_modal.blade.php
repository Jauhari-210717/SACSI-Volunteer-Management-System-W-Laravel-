<!-- DELETE CONFIRMATION MODAL -->
<div id="deleteModal" class="reset-import-modal">
    <div class="reset-modal-overlay" id="deleteOverlay">
        <div class="reset-modal-box">

            <div class="reset-modal-header">
                <i class="fa-solid fa-trash-can reset-modal-icon"></i>
                <h2 style="color:#B2000C">Delete Selected Entries?</h2>
            </div>

            <hr class="reset-modal-separator">

            <div id="deleteModalText" class="reset-text-block">
                Are you sure you want to delete the selected entries?<br>
                <strong>This action can be undone.</strong>
            </div>

            <div class="reset-modal-buttons">
                <button type="button" class="reset-btn-cancel" id="deleteCancelBtn">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>

                <button type="button" class="reset-btn-confirm" id="deleteConfirmBtn">
                    <i class="fa-solid fa-check"></i> Delete
                </button>
            </div>

        </div>
    </div>
</div>

<!-- DELETE SUCCESS MODAL -->
<div id="deleteSuccessModal" class="reset-import-modal">
    <div id="deleteSuccessOverlay" class="reset-modal-overlay">
        <div class="reset-modal-box">

            <div class="reset-modal-header">
                <i class="fa-solid fa-circle-check reset-success-icon"></i>
                <h2 class="reset-success-title">Success</h2>
            </div>

            <hr class="reset-modal-separator">

            <div id="deleteSuccessMessage" class="reset-text-block reset-success-text"></div>

            <div class="reset-modal-buttons">
                <button type="button" class="reset-btn-confirm" id="deleteSuccessOkBtn">
                    <i class="fa-solid fa-check"></i> OK
                </button>
            </div>

        </div>
    </div>
</div>

@if(session('delete_success'))
<script>
    window.serverDeleteSuccessMessage = `{!! session('delete_success') !!}`;
</script>
@endif

@if(session('undo_success'))
<script>
    window.serverUndoSuccessMessage = `{!! session('undo_success') !!}`;
</script>
@endif

<script>
document.addEventListener("DOMContentLoaded", () => {

    /* ------------------ ELEMENTS ------------------ */

    const deleteModal          = document.getElementById("deleteModal");
    const deleteOverlay        = document.getElementById("deleteOverlay");
    const deleteConfirmBtn     = document.getElementById("deleteConfirmBtn");
    const deleteCancelBtn      = document.getElementById("deleteCancelBtn");

    const deleteSuccessModal   = document.getElementById("deleteSuccessModal");
    const deleteSuccessOverlay = document.getElementById("deleteSuccessOverlay");
    const deleteSuccessMessage = document.getElementById("deleteSuccessMessage");
    const deleteSuccessOkBtn   = document.getElementById("deleteSuccessOkBtn");

    const globalDeleteForm     = document.getElementById("globalDeleteForm");

    let pendingDeleteAction = null;


    /* ----------------------------------------------------------
       1. OPEN DELETE MODAL 
    ---------------------------------------------------------- */
    window.openDeleteModal = function(action, tableType) {
        pendingDeleteAction = { action, tableType };
        deleteModal.classList.add("active");
    };


    /* ----------------------------------------------------------
       2. CANCEL DELETE
    ---------------------------------------------------------- */
    function closeDeleteModal() {
        deleteModal.classList.remove("active");
        pendingDeleteAction = null;
    }

    deleteCancelBtn?.addEventListener("click", closeDeleteModal);

    deleteOverlay?.addEventListener("click", e => {
        if (e.target === deleteOverlay) closeDeleteModal();
    });


    /* ----------------------------------------------------------
       3. CONFIRM DELETE
    ---------------------------------------------------------- */
    deleteConfirmBtn?.addEventListener("click", () => {

        if (!pendingDeleteAction) return;

        globalDeleteForm.innerHTML = "";

        const csrf = document.createElement("input");
        csrf.type = "hidden";
        csrf.name = "_token";
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        globalDeleteForm.appendChild(csrf);

        const tableType = document.createElement("input");
        tableType.type = "hidden";
        tableType.name = "table_type";
        tableType.value = pendingDeleteAction.tableType;
        globalDeleteForm.appendChild(tableType);

        const selected = document.querySelectorAll(
            `input[name="selected_${pendingDeleteAction.tableType}[]"]:checked`
        );

        selected.forEach(cb => {
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = "selected[]";
            hidden.value = cb.value;
            globalDeleteForm.appendChild(hidden);
        });

        globalDeleteForm.action = pendingDeleteAction.action;
        globalDeleteForm.submit();
    });


    /* ----------------------------------------------------------
       4. SHOW SUCCESS MODAL
    ---------------------------------------------------------- */
    function showDeleteSuccess(msg) {
        deleteSuccessMessage.innerHTML = msg;
        deleteSuccessModal.classList.add("active");
    }

    if (window.serverDeleteSuccessMessage) {
        showDeleteSuccess(window.serverDeleteSuccessMessage);
    }
    if (window.serverUndoSuccessMessage) {
        showDeleteSuccess(window.serverUndoSuccessMessage);
    }


    /* ----------------------------------------------------------
       5. CLOSE SUCCESS MODAL
    ---------------------------------------------------------- */
    function closeDeleteSuccess() {
        deleteSuccessModal.classList.remove("active");
    }

    deleteSuccessOkBtn?.addEventListener("click", closeDeleteSuccess);

    deleteSuccessOverlay?.addEventListener("click", e => {
        if (e.target === deleteSuccessOverlay) closeDeleteSuccess();
    });

});
</script>
