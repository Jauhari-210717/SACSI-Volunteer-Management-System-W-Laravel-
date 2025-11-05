<link rel="stylesheet" href="{{ asset('assets/volunteer_import/css/guide1&2.css') }}">

<!-- Modal 2: Valid Entries Guide -->
<div class="import-handling-modal" id="importHandlingModal2">
    <div class="modal-overlay">
        <div class="modal-content wide-modal">
        <div class="modal-inner">
            <!-- Header -->
            <div class="modal-header">
            <i class="fas fa-book modal-icon"></i>
            <h2>Valid Entries Guide</h2>
            </div>
            <!-- Content -->
            <div class="guide-content">
            <p>
                This section focuses on finalizing valid volunteer records before saving them to the database and logging the import activity.
            </p>
            <h3>1. Review Valid Entries</h3>
            <p class="inline-paragraph">
                The table shows all volunteer records that passed validation. Review these to ensure accuracy before submission.
            </p>
            <div class="video-placeholder outline-cut">Video: Reviewing valid entries</div>
            <h3>2. Edit if Needed</h3>
            <p class="inline-paragraph">
                You can still update information by clicking 
                <button class="btn btn-sm btn-outline-secondary inline-small-btn" aria-hidden="true">
                <i class="fa-solid fa-user-edit"></i> Edit
                </button> 
                next to any entry to make final adjustments.
            </p>
            <div class="video-placeholder outline-cut">Video: Editing valid entries</div>
            <h3>3. Submit to Database</h3>
            <p class="inline-paragraph">
            Once all records are reviewed, click 
            <span class="submit-section" style="display: inline-block; vertical-align: middle;">
                <button type="button" class="btn btn-danger submit-database">
                    Submit
                </button>
            </span>
            to finalize the import. The system will automatically log this action in the Import Logs section.
            </p>
            <div class="video-placeholder outline-cut">Video: Submitting valid entries</div>
            <h3>4. View Import Logs</h3>
            <p>
            After submission, go to the 
            <strong>
            <a href="#importlog-Section">
                Import Logs
            </a>
            </strong>
            section to confirm the import details, including uploader name, date, and number of records processed.
            </p>
            <div class="video-placeholder outline-cut">Video: Viewing import logs</div>
            </div>
            <!-- Footer -->
            <div class="modal-buttons">
            <button class="modal-btn cancel" onclick="closeModal('importHandlingModal2')">
                <i class="fa-solid fa-xmark"></i> Close
            </button>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
    // guide2.js — Handles Valid Entries Guide modal
document.addEventListener("DOMContentLoaded", () => {
    const modal2 = document.getElementById("importHandlingModal2");
    if (!modal2) return; // Exit if modal not on page

    const closeBtn2 = modal2.querySelector(".modal-btn.cancel");

    // Close overlay click
    modal2.querySelector(".modal-overlay").addEventListener("click", (e) => {
        if (e.target.classList.contains("modal-overlay")) {
            closeModal("importHandlingModal2");
        }
    });

    // ESC key closes modal
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal2.classList.contains("is-open")) {
            closeModal("importHandlingModal2");
        }
    });

    // Close button
    closeBtn2.addEventListener("click", () => closeModal("importHandlingModal2"));

    // Optional: Navigate back to guide 1
    const backToGuide1 = modal2.querySelector(".btn.back-guide1");
    if (backToGuide1) {
        backToGuide1.addEventListener("click", () => {
            closeModal("importHandlingModal2");
            openModal("importHandlingModal1");
        });
    }

    console.log("Guide 2 (Valid Entries) initialized ✅");
});

</script>