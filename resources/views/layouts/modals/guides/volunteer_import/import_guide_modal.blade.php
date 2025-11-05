
<link rel="stylesheet" href="{{ asset('assets/volunteer_import/css/guide1&2.css') }}">

<!-- Modal 1: Import CSV Guide -->
<div class="import-handling-modal" id="importHandlingModal1">
    <div class="modal-overlay">
        <div class="modal-content wide-modal">
            <div class="modal-inner">
                <!-- Header -->
                <div class="modal-header">
                    <h2><i class="fas fa-book modal-icon"></i>Import File Guide</h2>
                </div>
                <!-- Content -->
                <div class="guide-content">
                <p>
                    This guide explains how to upload a CSV file containing volunteer records and prepare them for validation.
                </p>
                <h3>1. Select CSV File</h3>
                <p class="inline-paragraph">
                    Click 
                    <span class="inline-control">
                    <button type="button" class="btn btn-outline-secondary rounded-1 inline-btn">
                        <i class="fa-solid fa-file-csv" style="margin-right: 6px;"></i> Choose File
                    </button>
                    <span class="file-path inline-filepath">No file chosen</span>
                    </span>
                    to select the CSV file from your device.
                </p>
                <div class="video-placeholder outline-cut">Video: Selecting a CSV file</div>
                <h3>2. Enter Uploader Name & Import</h3>
                <p class="inline-paragraph">
                    Your name will automatically added as the uploader
                    <span class="uploader-info">
                        <input type="text" id="uploader-name" placeholder="Uploading as [Your Name]" readonly>
                    </span>
                    , then click 
                    <button class="btn btn-outline-secondary import-btn inline-btn">
                        <i class="fa-solid fa-upload"></i> Import
                    </button>
                    to upload and process the data.
                </p>
                <div class="video-placeholder outline-cut">Video: Uploading the CSV file</div>
                <h3>3. Review Imported Records</h3>
                    <p class="inline-paragraph">
                        Once uploaded, the system will display all records. Any missing or invalid data will appear highlighted in 
                        <span style="color:#c41e3a; font-weight:600;">red</span>. You can use the 
                        <button type="button" class="btn btn-sm btn-outline-secondary inline-small-btn" aria-hidden="true">
                            <i class="fa-solid fa-user-edit"></i> Edit
                        </button> 
                        button to correct individual entries.
                    </p>
                    <p class="inline-paragraph">
                        Additionally, you can use the 
                        <button type="button" class="btn btn-sm btn-outline-secondary inline-small-btn" aria-hidden="true">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Table
                        </button> 
                        button to manage the table as a whole. The following actions are available:
                    </p>
                    <ul class="inline-list" style="list-style-type: disc; padding-left: 1.5rem; margin-top: 0.25rem;">
                        <li style="margin-bottom: 0.5rem;">
                            <button class="btn btn-outline-primary btn-sm select-all-btn" aria-hidden="true">
                                <i class="fa-solid fa-check-double"></i> Select All
                            </button> — Quickly select all records in the table.
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <button class="btn btn-outline-success btn-sm copy-btn" aria-hidden="true">
                                <i class="fa-solid fa-copy"></i> Copy
                            </button> — Copy selected records for use elsewhere.
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <button class="btn btn-outline-danger btn-sm delete-btn" aria-hidden="true">
                                <i class="fa-solid fa-trash-can"></i> Delete
                            </button> — Remove unwanted or incorrect records.
                        </li>
                    </ul>
                    <div class="video-placeholder outline-cut">Video: Reviewing imported entries</div>
                <h3>4. Validate Corrected Entries</h3>
                <ul class="inline-list" style="list-style-type: disc; padding-left: 1.5rem;">
                <li>
                    Click 
                    <span class="inline-control" style="display: inline-block; vertical-align: middle;">
                    <button type="button" class="btn btn-sm btn-outline-secondary inline-small-btn move-btn">
                        <i class="fa-solid fa-arrow-right"></i> Validate
                    </button>
                    </span>
                    to move records individually after each correction.
                </li>
                <li>
                    Use 
                    <span class="submit-section" style="display: inline-block; vertical-align: middle;">
                    <button type="button" class="btn btn-danger submit-database inline-btn">
                        Move to All Invalid Entries
                    </button>
                    </span>
                    once <strong>all problems or missing fields have been resolved</strong> to validate all remaining records at once.
                </li>
                </ul>
                <div class="video-placeholder outline-cut">Video: Validating corrected entries</div>
                <h3>5. Proceed to Valid Entries Guide</h3>
                <p class="inline-paragraph">
                    Once validation is complete, continue to the 
                    <span class="inline-control">
                    <button class="btn btn-outline-secondary import-btn" onclick="closeModal('importHandlingModal1'); openModal('importHandlingModal2');">
                        <i class="fas fa-book"></i> Valid Entries Guide
                    </button>
                    </span> 
                    to finalize and submit verified records.
                </p>
                </div>
                <!-- Footer -->
                <div class="modal-buttons">
                    <button class="modal-btn cancel" onclick="closeModal('importHandlingModal1')">
                        <i class="fa-solid fa-xmark"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // guide1.js — Handles Import CSV Guide modal
document.addEventListener("DOMContentLoaded", () => {
    const modal1 = document.getElementById("importHandlingModal1");
    const closeBtn1 = modal1.querySelector(".modal-btn.cancel");

    // Open modal function (can be reused globally)
    window.openModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add("is-open");
    };

    // Close modal function
    window.closeModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove("is-open");
    };

    // Close when clicking overlay
    modal1.querySelector(".modal-overlay").addEventListener("click", (e) => {
        if (e.target.classList.contains("modal-overlay")) {
            closeModal("importHandlingModal1");
        }
    });

    // ESC key closes modal
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal1.classList.contains("is-open")) {
            closeModal("importHandlingModal1");
        }
    });

    // Close button event
    closeBtn1.addEventListener("click", () => closeModal("importHandlingModal1"));

    // Optional: Demo file picker for the guide (non-functional placeholder)
    const fileBtn = modal1.querySelector(".btn.btn-outline-secondary.inline-btn");
    const filePath = modal1.querySelector(".file-path.inline-filepath");

    if (fileBtn && filePath) {
        fileBtn.addEventListener("click", () => {
            filePath.textContent = "example_volunteers.csv";
        });
    }

    console.log("Guide 1 (Import CSV) initialized ✅");
});

</script>