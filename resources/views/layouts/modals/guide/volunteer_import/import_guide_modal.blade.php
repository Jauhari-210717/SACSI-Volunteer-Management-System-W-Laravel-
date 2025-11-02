<style>
.see-guide-modal {
    position: fixed;
    inset: 0;
    display: none;
    z-index: 9999;
    font-family: 'Segoe UI', Roboto, sans-serif;
    }

    .see-guide-modal .modal-overlay {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.55);
    }

    .see-guide-modal .modal-content {
        background: #fff;
        border-radius: 16px;
        width: 95%;
        max-width: 1200px; /* wide modal */
        padding: 2rem;
        box-shadow: 0 12px 40px rgba(0,0,0,0.35);
        text-align: left;
        overflow-y: auto;
        max-height: 90vh;
        animation: slideIn 0.3s ease forwards;
    }

    .wide-modal .guide-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .guide-image {
        width: 100%;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* Modal Header */
    .modal-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .modal-header h2 {
        color: #B2000C;
        margin: 0;
    }
    .modal-icon {
        font-size: 2rem;
        color: #B2000C;
    }

    /* Footer Buttons */
    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        margin-top: 1.5rem;
    }
    .modal-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.5rem;
        font-size: 1rem;
        border-radius: 8px;
        cursor: pointer;
        border: none;
        transition: all 0.25s ease;
    }
    .modal-btn.cancel {
        background-color: #e5e5e5;
        color: #333;
    }
    .modal-btn.cancel:hover {
        background-color: #d0d0d0;
        transform: translateY(-2px);
    }

    /* Animation */
    @keyframes slideIn {
        from { opacity:0; transform: translateY(-20px) scale(0.96); }
        to { opacity:1; transform: translateY(0) scale(1); }
    }

    /* Responsive */
    @media(max-width: 768px) {
        .see-guide-modal .modal-content {
            width: 95%;
            padding: 1rem;
        }
    }

    /* Header */
    .modal-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .modal-header h2 {
        color: #B2000C;
        margin: 0;
    }
    .modal-icon {
        font-size: 2rem;
        color: #B2000C;
    }

    /* Tables */
    .import-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
    }
    .import-table th, .import-table td {
        border: 1px solid #ccc;
        padding: 0.5rem 1rem;
        text-align: left;
    }
    .import-table th {
        background-color: #f5f5f5;
    }
    .import-table td[contenteditable="true"] {
        background: #fff3e0;
        cursor: text;
    }

    /* Footer Buttons */
    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .modal-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.5rem;
        font-size: 1rem;
        border-radius: 8px;
        cursor: pointer;
        border: none;
        transition: all 0.25s ease;
    }
    .modal-btn.cancel {
        background-color: #e5e5e5;
        color: #333;
    }
    .modal-btn.cancel:hover {
        background-color: #d0d0d0;
    }

    /* Animation */
    @keyframes slideIn {
        from { opacity:0; transform: translateY(-20px) scale(0.96); }
        to { opacity:1; transform: translateY(0) scale(1); }
    }

/* General modal styling */
    .import-handling-modal {
        position: fixed;
        inset: 0;
        display: none;
        z-index: 9999;
    }

    .import-handling-modal.is-open { display: block; }

    .import-handling-modal .modal-overlay {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.55);
        overflow-y: auto;
        padding: 1rem;
    }

    .import-handling-modal .modal-content {
        background: #fff;
        border-radius: 16px; /* round all corners */
        width: 100%;
        max-width: 1600px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.35);
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        overflow: hidden; /* keeps corners rounded */
    }

    /* Scrollable inner content */
    .import-handling-modal .modal-inner {
        padding: 2rem;
        overflow-y: auto; /* content scrolls if too tall */
    }


    .import-handling-modal .modal-content .modal-body {
        overflow-y: auto; /* scrollable inner content */
        padding-right: 1rem; /* optional, avoid content hiding behind scrollbar */
    }


    .import-handling-modal .modal-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .import-handling-modal .modal-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }
    .import-handling-modal .modal-header .modal-icon {
        font-size: 1.75rem;
        color: #c41e3a;
    }

    /* Modal buttons */
    .import-handling-modal .modal-buttons {
        margin-top: 1.5rem;
        text-align: right;
    }
    .import-handling-modal .modal-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background-color: transparent;
        color: #333;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .import-handling-modal .modal-btn:hover {
        background-color: #f8f9fa;
        border-color: #b2000c;
        color: #b2000c;
    }
    .import-handling-modal .modal-btn i {
        font-size: 0.9rem;
        transition: transform 0.2s ease, color 0.2s ease;
    }
    .import-handling-modal .modal-btn:hover i {
        transform: rotate(10deg);
        color: #b2000c;
    }

    /* Inline button group for guide */
    .inline-file-group {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .inline-file-group .btn {
        height: 36px;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px 0 0 6px;
    }
    .inline-file-group .file-path {
        display: inline-block;
        height: 36px;
        line-height: 36px;
        min-width: 120px;
        max-width: 220px;
        padding: 0 0.5rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border: 1px solid #ced4da;
        border-radius: 0 6px 6px 0;
        background: #fff;
        font-size: 0.9rem;
        color: #495057;
    }

    /* Video placeholder */
    .video-placeholder {
        background: #e0e0e0;
        height: 150px;
        width: 100%;
        text-align: center;
        line-height: 150px;
        border-radius: 8px;
        margin-top: 0.5rem;
        color: #555;
        font-weight: 500;
    }

    /* Table inside modal */
    .import-handling-modal .import-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0.5rem;
        margin-bottom: 1rem;
    }
    .import-handling-modal .import-table th,
    .import-handling-modal .import-table td {
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.875rem;
    }
    .import-handling-modal .import-table th {
        background: #f8f9fa;
        font-weight: 600;
    }

    .modal-btn.cancel {
        padding: 12px 24px;      /* Bigger button area */
        font-size: 1.1rem;       /* Larger text */
    }

    .modal-btn.cancel i {
        font-size: 1.2rem;       /* Larger icon */
        margin-right: 6px;       /* Space between icon and text */
    }

    /* Keep original .file-upload and .uploader-info rules intact. */

    .inline-control {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: 0.5rem;
    vertical-align: middle;
    }

    /* Use your file-upload markup but ensure the button & path sit flush */
    .file-upload .input-group {
    display: inline-flex;
    align-items: center;
    gap: 0;
    vertical-align: middle;
    }

    /* Choose File: left button, file-path right (use your .btn styles) */
    #file-upload-button-demo {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: 0;
    height: 36px;
    padding: 0.45rem 0.8rem;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    }

    #file-path-demo,
    .file-path {
    display: inline-block;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border: 1px solid #ced4da;
    height: 36px;
    line-height: 36px;
    padding: 0 0.6rem;
    min-width: 120px;
    max-width: 260px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    background: #fff;
    font-size: 0.9rem;
    color: #495057;
    }

    /* connect the two visually (prevent double border seam) */
    #file-upload-button-demo + #file-path-demo {
    margin-left: -1px;
    }

    /* uploader controls inline */
    .uploader-info-demo,
    .submit-demo {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    vertical-align: middle;
    margin-left: 0.4rem;
    }

    /* demo uploader input style consistent with .uploader-info input */
    .demo-uploader-input {
    min-width: 180px;
    padding: 0.375rem 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.95rem;
    }

    /* small adaptations for the video placeholders */
    .video-placeholder.outline-cut {
    background: #f7f7f7;
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    border-radius: 8px;
    margin: 0.5rem 0 1rem;
    border: 2px dashed rgba(0,0,0,0.08);
    }

    /* Responsive */
    @media (max-width: 640px) {
    .inline-control { display:flex; flex-direction:row; gap:0.5rem; }
    .file-upload .input-group { flex-wrap:wrap; gap:0.5rem; }
    #file-path-demo, .file-path { max-width: 100%; }
    .demo-uploader-input { width: 100%; }
    }

    /* Keep controls inline, matching Step 2 alignment */
    .inline-paragraph {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    }

    .inline-control {
    display: inline-flex;
    align-items: center;
    gap: 0;
    margin-left: 0.25rem;
    vertical-align: middle;
    }

    /* Choose File button (visual only) */
    #file-upload-button-demo {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: 0;
    height: 36px;
    padding: 0.45rem 0.8rem;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    }

    /* File path stays directly to the right of the button, same height */
    .inline-filepath {
    border: 1px solid #ced4da;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    height: 36px;
    line-height: 36px;
    padding: 0 0.6rem;
    min-width: 120px;
    max-width: 220px;
    background: #fff;
    color: #495057;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-left: -1px; /* hides seam */
    }

    .inline-move-btn {
    padding: 0.4rem 0.9rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    background-color: #c73c46; /* main red */
    color: #fff;
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                background 0.3s ease,
                box-shadow 0.25s ease;
    vertical-align: middle;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    }

    .inline-control .import-btn {
    margin-left: 0.5rem; /* small space between input and button */
    }

    /* Hover effect */
    .inline-move-btn:hover {
    background-color: #8f000a;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 6px 12px rgba(0,0,0,0.3);
    }

    /* Active (pressed) */
    .inline-move-btn:active {
    background-color: #6b0007;
    transform: translateY(0) scale(0.98);
    box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    }

    /* Focus (accessibility) */
    .inline-move-btn:focus {
    outline: 3px solid #ffd43b;
    outline-offset: 3px;
    }

    /* Responsive fallback for smaller screens */
    @media (max-width:640px) {
    .inline-paragraph {
        flex-direction: column;
        align-items: flex-start;
    }

    /* small inline button used in sentences (Edit, Submit) */
    .inline-small-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.45rem;
    font-size: 0.85rem;
    vertical-align: baseline;
    margin-left: 0.4rem;
    }

    /* Ensure import/demo submit buttons match original visual style */
    .inline-btn,
    .uploader-info-demo .inline-btn,
    .submit-database.inline-btn,
    .import-btn.inline-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    vertical-align: middle;
    }

    /* demo uploader input (visual-only) */
    .demo-uploader-input {
        min-width: 180px;
        padding: 0.375rem 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 0.95rem;
    }

    /* video placeholder small style */
    .video-placeholder.outline-cut {
        background: #f7f7f7;
        border: 2px dashed rgba(0,0,0,0.08);
        height: 140px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#6c757d;
        border-radius:8px;
        margin:0.5rem 0 1rem;
    }

    /* responsive fallback - keep inline but wrap on narrow screens */
    @media (max-width:640px) {
        .inline-paragraph { flex-direction: column; align-items:flex-start; gap:0.5rem; }
        .inline-control { margin-left: 0; }
        .demo-uploader-input { width: 100%; }
        .inline-filepath { max-width: 100%; }
        }
    }
</style>

<!-- Modal 1: Import CSV Guide -->
<div class="import-handling-modal" id="importHandlingModal1">
    <div class="modal-overlay">
        <div class="modal-content wide-modal">
        <div class="modal-inner">
            <!-- Header -->
            <div class="modal-header">
            <i class="fas fa-book modal-icon"></i>
            <h2>Import File Guide</h2>
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
                    Move to Verified Entries
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