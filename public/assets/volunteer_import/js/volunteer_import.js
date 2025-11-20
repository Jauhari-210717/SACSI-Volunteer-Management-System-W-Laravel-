// File Import Handling
document.addEventListener('DOMContentLoaded', () => {

    // 🔥 Global district lookup injected from Blade:
    // window.locationDistricts is already provided by Blade
    function getDistrictName(barangay) {
        if (!barangay || !window.locationDistricts) return '';
        const found = window.locationDistricts.find(loc =>
            String(loc.barangay).toLowerCase() === String(barangay).toLowerCase()
        );
        return found ? found.district_id : '';
    }

    (function fileUploadSetup() {
        const fileInput = document.getElementById('file-upload');
        const filePath = document.getElementById('file-path');
        const uploadButton = document.getElementById('file-upload-button');
        const uploaderInput = document.querySelector('.uploader-info input');
        const importButton = document.querySelector('.uploader-info .import-btn');

        if (!fileInput || !filePath || !uploadButton) return;

        const messageModal = document.getElementById('messageModal');
        const messageModalText = document.getElementById('messageModalText');
        const messageModalButtons = document.getElementById('messageModalButtons');

        // Click to open file selector
        uploadButton.addEventListener('click', () => fileInput.click());

        // Update selected filename
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                filePath.textContent = fileInput.files[0].name;
                filePath.classList.add('active');
                uploaderInput?.classList.add('active');
                importButton?.classList.add('active');

                showNoticeModal(`Selected file: ${fileInput.files[0].name}`);
            } else if (!filePath.classList.contains('imported')) {
                filePath.textContent = 'No file chosen';
                filePath.classList.remove('active');
                uploaderInput?.classList.remove('active');
                importButton?.classList.remove('active');
            }
        });

        // Confirm import
        importButton?.form?.addEventListener('submit', (e) => {
            e.preventDefault();

            if (!fileInput.files.length) return;

            const fileName = fileInput.files[0].name;

            showConfirmModal(`Are you sure you want to upload the file: "${fileName}"?`, () => {

                // 🔥 Patch: Fix district values before saving
                if (window.sessionEntries) {
                    ['validEntries', 'invalidEntries'].forEach(type => {
                        if (window.sessionEntries[type]) {
                            window.sessionEntries[type].forEach(entry => {
                                entry['district_name'] = getDistrictName(entry['barangay']);
                            });
                        }
                    });
                }

                // Mark UI as imported
                filePath.classList.add('imported', 'active');
                uploaderInput?.classList.add('imported', 'active');
                if (importButton) {
                    importButton.classList.add('imported');
                    importButton.style.display = 'none';
                }

                // Track table state
                const invalidTable = document.getElementById('invalid-entries-table');
                if (invalidTable) {
                    const firstRow = invalidTable.querySelector('tbody tr');
                    window.lastUsedTable = { type: 'invalid', index: firstRow ? 0 : null };
                    sessionStorage.setItem('lastUsedTable', JSON.stringify(window.lastUsedTable));
                }

                importButton.form.submit();

                showNoticeModal(`File imported successfully: ${fileName}`);
            });
        });

        // ---------------------
        // Helper Modals
        // ---------------------

        function showNoticeModal(message) {
            messageModalText.innerText = message;
            messageModalButtons.innerHTML = '';

            const okBtn = document.createElement('button');
            okBtn.type = 'button';
            okBtn.className = 'confirm-btn';
            okBtn.innerText = 'OK';
            okBtn.addEventListener('click', () => messageModal.style.display = 'none');

            messageModalButtons.appendChild(okBtn);
            messageModal.style.display = 'flex';
        }

        function showConfirmModal(message, onConfirm) {
            messageModalText.innerText = message;
            messageModalButtons.innerHTML = '';

            const confirmBtn = document.createElement('button');
            confirmBtn.type = 'button';
            confirmBtn.className = 'confirm-btn';
            confirmBtn.innerText = 'Yes';
            confirmBtn.addEventListener('click', () => {
                messageModal.style.display = 'none';
                onConfirm?.();
            });

            const cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.className = 'cancel-btn';
            cancelBtn.innerText = 'No';
            cancelBtn.addEventListener('click', () => messageModal.style.display = 'none');

            messageModalButtons.appendChild(confirmBtn);
            messageModalButtons.appendChild(cancelBtn);
            messageModal.style.display = 'flex';
        }

    })();
});

// Move Invalid → Valid
function submitMoveToValid(button) {
    const row = button.closest('tr');
    const checkbox = row.querySelector('input[name="selected_invalid[]"]');
    if (!checkbox) return;
    checkbox.checked = true;
    const form = button.closest('form');
    if (form) form.submit();
}

// Move Valid → Invalid
function moveToInvalid(index) {
    window.location.href = `/volunteer-import/move-valid-to-invalid/${index}#invalid-entries-table`;
}
