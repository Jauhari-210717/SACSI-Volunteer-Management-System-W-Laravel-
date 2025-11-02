<style>
    /* Modal */
    .edit-volunteer-modal {
        position: fixed;
        inset: 0;
        display: none;
        z-index: 9999;
        font-family: 'Segoe UI', Roboto, sans-serif;
    }

    .edit-volunteer-modal .modal-overlay {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.55);
    }

    .edit-volunteer-modal .modal-content {
        background: #fff;
        border-radius: 16px;
        width: 90%;
        max-width: 650px;
        padding: 2rem;
        box-shadow: 0 12px 40px rgba(0,0,0,0.35);
        text-align: center;
        animation: slideIn 0.3s ease forwards;
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
        transition: transform 0.25s ease, color 0.25s ease;
    }
    .modal-icon:hover {
        transform: rotate(10deg);
        color: #B2000C;
    }

    /* Input Grid */
    .input-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Volunteer Info Tiles */
    .volunteer-info {
        position: relative;
        cursor: pointer; /* mimic custom-option hover feel */
    }
    .volunteer-info input {
        width: 100%;
        padding: 0.6rem 2.5rem 0.6rem 2.5rem;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 1rem;
        transition: all 0.25s ease;
    }
    .volunteer-info input:focus {
        outline: none !important;
        box-shadow: none !important;
        border-color: #B2000C;
        background: #f7f7f7;
    }

    /* Input Icon */
    .input-icon {
        position: absolute;
        top: 50%;
        left: 0.75rem;
        transform: translateY(-50%);
        color: #888;
        font-size: 1.2rem;
        pointer-events: none;
        transition: transform 0.25s ease, color 0.25s ease;
    }

    /* Hover effect like custom-option */
    .volunteer-info:hover input {
        background: #ffffffff;
        border-color: #B2000C;
    }
    .volunteer-info:hover .input-icon {
        color: #B2000C;
        transform: translateY(-50%) rotate(10deg);
    }

    /* Custom Select for Import Log Modal */
    .import-custom-select-wrapper {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1.5rem;  
    }
    
    .import-custom-options :hover input {
        background: #ffffffff;
        border-color: #B2000C;
    }

    .import-custom-select {
        position: relative;
        cursor: pointer;
        width: 100%;        /* take full width of parent container */
        max-width: 285px;   /* limit maximum width */
        min-width: 180px;   /* optional: prevent it from being too small */
        text-align: left;
        box-sizing: border-box; /* ensures padding doesn't break layout */
    }

    /* Optional: make the trigger flex nicely */
    .import-custom-select-trigger {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.25s;
        color: #888;
        box-sizing: border-box;
    }

    /* For very small screens, shrink font and padding */
    @media (max-width: 480px) {
        .import-custom-select {
            max-width: 100%;   /* fill available width */
        }

        .import-custom-select-trigger {
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
        }
    }


    /* Icon inside trigger */
    .import-custom-select-trigger i {
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .import-custom-select-trigger:hover i {
        color: #B2000C !important;
        transform: rotate(10deg);
    }

    /* Rotate and color on open */
    .import-custom-select.open .import-custom-select-trigger i,
    .import-custom-select-trigger:hover i {
        transform: rotate(10deg);
        color: #B2000C;
    }

    /* Options */
    .import-custom-options {
        position: absolute;
        top: calc(100% + 0.25rem);
        left: 0;
        width: 100%;
        border: 1px solid #ccc;
        border-radius: 8px;
        background: #fff;
        display: none;
        flex-direction: column;
        z-index: 10;
    }

    .import-custom-select.open .import-custom-options {
        display: flex;
    }

    .import-custom-option {
        padding: 0.5rem 1rem;
        transition: all 0.25s;
    }

    /* Hover now matches red theme */
    .import-custom-option:hover {
        background: #B2000C; /* red background */
        color: #fff;          /* white text */
    }

    .import-custom-option:hover i {
        color: #fff;          /* white icon */
    }

    /* Checkbox */
    .checkbox-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .custom-checkbox {
        width: 18px;
        height: 18px;
        accent-color: #B2000C;
    }

    /* Footer Buttons */
    .modal-buttons {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }
    .modal-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.7rem 1.8rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        border: none;
        transition: all 0.25s ease;
    }
    .modal-btn.confirm {
        background-color: #B2000C;
        color: #fff;
    }
    .modal-btn.confirm:hover {
        background-color: #8f000a;
        transform: translateY(-2px);
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
    @media(max-width: 500px) {
        .input-grid { grid-template-columns: 1fr; }
        .custom-select-wrapper { flex-direction: column; gap: 0.75rem; }
    }
</style>
<!-- Edit Volunteer Modal -->
<div class="edit-volunteer-modal" id="editVolunteerModal">
    <div class="modal-overlay">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <i class="fa-solid fa-user-edit modal-icon"></i>
                <h2>Edit Volunteer</h2>
            </div>
            <!-- Input Grid -->
            <div class="input-grid">
                <div class="volunteer-info">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" placeholder="Full Name">
                </div>
                <div class="volunteer-info">
                    <i class="fa-solid fa-id-badge input-icon"></i>
                    <input type="text" placeholder="ID Number">
                </div>
                <div class="volunteer-info">
                    <i class="fa-solid fa-graduation-cap input-icon"></i>
                    <input type="text" placeholder="Course">
                </div>
                <div class="volunteer-info">
                    <i class="fa-solid fa-calendar input-icon"></i>
                    <input type="text" placeholder="Year">
                </div>
                <div class="volunteer-info">
                    <i class="fa-solid fa-phone input-icon"></i>
                    <input type="tel" placeholder="Contact Number">
                </div>
                <div class="volunteer-info">
                    <i class="fa-solid fa-phone-flip input-icon"></i>
                    <input type="tel" placeholder="Emergency Number">
                </div>
                <div class="volunteer-info">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" placeholder="Email">
                </div>
                <div class="volunteer-info">
                    <i class="fa-brands fa-facebook input-icon"></i>
                    <input type="url" placeholder="Facebook Profile">
                </div>
            </div>
            <!-- Custom Select Wrapper -->
            <div class="import-custom-select-wrapper">
                <!-- Barangay -->
                <div class="import-custom-select" data-field="barangay">
                    <div class="import-custom-select-trigger">
                        <i class="fa-solid fa-house" style="color:#666;"></i> Filter by Barangay
                    </div>
                    <div class="import-custom-options">
                        <span class="import-custom-option" data-value="remove"><i class="fa-solid fa-ban"></i> Remove Barangay Filter</span>
                        <span class="import-custom-option" data-value="Sta.Maria"><i class="fa-solid fa-location-dot"></i> Sta.Maria</span>
                        <span class="import-custom-option" data-value="Cabatangan"><i class="fa-solid fa-location-dot"></i> Cabatangan</span>
                        <span class="import-custom-option" data-value="Upper Calarian"><i class="fa-solid fa-location-dot"></i> Upper Calarian</span>
                        <span class="import-custom-option" data-value="Tumaga"><i class="fa-solid fa-location-dot"></i> Tumaga</span>
                        <span class="import-custom-option" data-value="Pasonanca"><i class="fa-solid fa-location-dot"></i> Pasonanca</span>
                    </div>
                </div>
                <!-- District -->
                <div class="import-custom-select" data-field="district">
                    <div class="import-custom-select-trigger">
                        <i class="fa-solid fa-map-location-dot" style="color:#666;"></i> Filter by District
                    </div>
                    <div class="import-custom-options">
                        <span class="import-custom-option" data-value="remove"><i class="fa-solid fa-ban"></i> Remove District Filter</span>
                        <span class="import-custom-option" data-value="District 1"><i class="fa-solid fa-location-dot"></i> District 1</span>
                        <span class="import-custom-option" data-value="District 2"><i class="fa-solid fa-location-dot"></i> District 2</span>
                        <span class="import-custom-option" data-value="District 3"><i class="fa-solid fa-location-dot"></i> District 3</span>
                    </div>
                </div>
            </div>
            <!-- Checkbox -->
            <div class="checkbox-wrapper">
                <label for="Volunteer-Status">Active Volunteer</label>
                <input type="checkbox" class="custom-checkbox">
            </div>
            <!-- Footer Buttons -->
            <div class="modal-buttons">
                <button class="modal-btn cancel">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button class="modal-btn confirm">
                    <i class="fa-solid fa-check"></i> Apply
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // public/js/modals/editVolunteerModal.js
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("editVolunteerModal");
    if (!modal) return;

    const overlay = modal.querySelector(".modal-overlay");
    const cancelBtn = modal.querySelector(".modal-btn.cancel");
    const confirmBtn = modal.querySelector(".modal-btn.confirm");
    const selects = modal.querySelectorAll(".import-custom-select");

    /** =============================
     * MODAL OPEN / CLOSE FUNCTIONS
     ============================= */
    window.openEditModal = () => {
        modal.style.display = "block";
        document.body.style.overflow = "hidden"; // Prevent background scroll
    };

    window.closeEditModal = () => {
        modal.style.display = "none";
        document.body.style.overflow = ""; // Restore scroll
        // Close any open dropdowns
        selects.forEach(s => s.classList.remove("open"));
    };

    // Close on overlay click
    overlay.addEventListener("click", (e) => {
        if (e.target.classList.contains("modal-overlay")) {
            closeEditModal();
        }
    });

    // Close on ESC
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal.style.display === "block") {
            closeEditModal();
        }
    });

    // Cancel button
    cancelBtn.addEventListener("click", closeEditModal);

    // Confirm button (placeholder action)
    confirmBtn.addEventListener("click", () => {
        alert("Changes applied successfully!");
        closeEditModal();
    });

    /** =============================
     * CUSTOM SELECT DROPDOWN LOGIC
     ============================= */
    selects.forEach(select => {
        const trigger = select.querySelector(".import-custom-select-trigger");
        const options = select.querySelectorAll(".import-custom-option");

        // Toggle open/close
        trigger.addEventListener("click", (e) => {
            e.stopPropagation();
            closeAllSelects(select);
            select.classList.toggle("open");
        });

        // Option click
        options.forEach(option => {
            option.addEventListener("click", (e) => {
                e.stopPropagation();
                const value = option.getAttribute("data-value");
                const icon = option.querySelector("i").outerHTML;
                const text = option.textContent.trim();

                // Update the trigger display
                if (value === "remove") {
                    trigger.innerHTML = `<i class="fa-solid fa-house" style="color:#666;"></i> Filter by ${capitalize(select.dataset.field)}`;
                } else {
                    trigger.innerHTML = `${icon} ${text}`;
                }

                select.classList.remove("open");
            });
        });
    });

    // Close selects when clicking outside
    document.addEventListener("click", closeAllSelects);

    function closeAllSelects(except = null) {
        selects.forEach(sel => {
            if (sel !== except) sel.classList.remove("open");
        });
    }

    function capitalize(word) {
        return word.charAt(0).toUpperCase() + word.slice(1);
    }

    console.log("✅ Edit Volunteer Modal initialized");
});

</script>