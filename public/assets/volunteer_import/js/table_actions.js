// Hidden table Button Functions
const deleteModal = document.getElementById('deleteModal');
const deleteConfirmBtn = document.getElementById('deleteConfirmBtn');
const deleteCancelBtn = document.getElementById('deleteCancelBtn');
let pendingDelete = null;

/* -----------------------------
   Show message for a specific section
----------------------------- */
function showMessage(sectionId, message, type = 'info', autoHide = true) {
    const section = document.getElementById(sectionId);
    if (!section) return;

    const msgDiv = section.querySelector('.action-message');
    if (!msgDiv) return;

    const textSpan = msgDiv.querySelector('.message-text');
    textSpan.innerHTML = message;

    msgDiv.className = 'action-message';

    if (type === 'success') msgDiv.classList.add('text-success');
    else if (type === 'error') msgDiv.classList.add('text-error');
    else msgDiv.classList.add('text-info');

    msgDiv.classList.remove('d-none');

    if (autoHide) {
        setTimeout(() => {
            msgDiv.classList.add('d-none');
        }, 6000);
    }
}

/* -----------------------------
   Close message manually
----------------------------- */
document.querySelectorAll('.close-message-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const msgDiv = btn.closest('.action-message');
        if (msgDiv) msgDiv.classList.add('d-none');
    });
});

/* -----------------------------
   Toggle edit mode
----------------------------- */
document.querySelectorAll('.toggle-edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const container = btn.closest('.data-table-container');
        container.classList.toggle('edit-mode');
        const hiddenActions = btn.closest('.table-actions').querySelector('.hidden-actions');
        hiddenActions?.classList.toggle('visible');
        btn.classList.toggle('active');
    });
});

/* -----------------------------
   Select All Checkbox Headers
----------------------------- */
['invalid', 'valid'].forEach(type => {
    const headerCb = document.querySelector(`.select-all-${type}`);
    if (!headerCb) return;

    const table = document.getElementById(`${type}-entries-table`);
    if (!table) return;

    headerCb.addEventListener('change', () => {
        table.querySelectorAll('tbody input[type="checkbox"]')
            .forEach(cb => cb.checked = headerCb.checked);
    });

    table.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => {
            const allChecked = Array.from(table.querySelectorAll('tbody input[type="checkbox"]'))
                .every(c => c.checked);
            headerCb.checked = allChecked;
        });
    });
});

/* -----------------------------
   Select All Toggle Button
----------------------------- */
document.querySelectorAll('.select-all-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const container = btn.closest('.data-table-container');
        const checkboxes = container.querySelectorAll('tbody input[type="checkbox"]');

        if (!checkboxes.length) {
            showMessage(container.closest('section').id, 'No rows available', 'error');
            return;
        }

        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    });
});

/* -----------------------------
   Copy Selected Rows
----------------------------- */
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const container = btn.closest('.data-table-container');
        const sectionId = container.closest('section').id;
        const selected = Array.from(container.querySelectorAll('tbody input[type="checkbox"]:checked'));

        if (!selected.length) {
            showMessage(sectionId, 'No rows selected', 'error');
            return;
        }

        let text = '';
        selected.forEach(cb => {
            const row = cb.closest('tr');
            const cells = Array.from(row.querySelectorAll('td'));
            text += cells.slice(1, -1).map(c => c.innerText.trim()).join('\t') + '\n';
        });

        navigator.clipboard.writeText(text).then(() => {
            showMessage(sectionId, `✔ Copied ${selected.length} row(s)`, 'success');
            const originalText = btn.innerHTML;
            btn.innerHTML = '✔ Copied';
            setTimeout(() => btn.innerHTML = originalText, 1500);
        });
    });
});

/* -----------------------------
   Delete Selected Rows
----------------------------- */
function showNoRowsSelected(sectionId) { showMessage(sectionId, 'No rows selected', 'error'); }

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const container = btn.closest('.data-table-container');
        const sectionId = container.closest('section').id;

        const selected = Array.from(container.querySelectorAll('tbody input[type="checkbox"]:checked'))
            .map(cb => cb.value);

        if (!selected.length) {
            showNoRowsSelected(sectionId);
            return;
        }

        pendingDelete = {
            action: btn.dataset.action,
            tableType: btn.dataset.tableType,
            selected,
            container
        };

        if (deleteModal) deleteModal.style.display = 'flex';
    });
});

/* -----------------------------
   Delete Confirm
----------------------------- */
if (deleteConfirmBtn) {
    deleteConfirmBtn.addEventListener('click', () => {
        if (!pendingDelete) return;

        const { action, tableType, selected } = pendingDelete;

        const form = document.getElementById('globalDeleteForm');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        form.action = action;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrf}">
            <input type="hidden" name="table_type" value="${tableType}">
        `;

        selected.forEach(val => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected[]';
            input.value = val;
            form.appendChild(input);
        });

        form.submit();
        if (deleteModal) deleteModal.style.display = 'none';
        pendingDelete = null;
    });
}

/* -----------------------------
   Delete Cancel
----------------------------- */
if (deleteCancelBtn) {
    deleteCancelBtn.addEventListener('click', () => {
        if (deleteModal) deleteModal.style.display = 'none';
        pendingDelete = null;
    });
}
