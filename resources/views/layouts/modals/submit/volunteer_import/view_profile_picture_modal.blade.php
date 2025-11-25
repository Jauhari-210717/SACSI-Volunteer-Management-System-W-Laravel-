<style>
/* ===============================
   GENERIC OVERLAY (for both)
=============================== */
.picture-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    justify-content: center;
    align-items: center;
    z-index: 99999 !important;
}

/* Small message modal */
.picture-modal {
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    padding: 30px 35px;
    max-width: 420px;
    width: 90%;
    text-align: center;
    animation: fadeInUp 0.3s ease;
}

/* Fullscreen image modal container */
.picture-expanded-modal {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    padding: 15px 20px 20px;
    max-width: 95%;
    max-height: 90vh;
    width: 95%;
    animation: fadeInUp 0.25s ease;
}

/* Heading for both modals */
.picture-modal h3,
.picture-expanded-modal h3 {
    margin-bottom: 12px;
    font-size: 1.4rem;
    font-weight: 700;
    color: #222;
}

/* Message Text */
.picture-modal p {
    margin-bottom: 25px;
    font-size: 1rem;
    line-height: 1.5;
    color: #444;
}

/* Buttons row */
.picture-modal-buttons {
    display: flex;
    justify-content: center;
    gap: 16px;
}

/* OK Button */
.picture-ok-btn {
    background-color: #b2000c;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 22px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.25s ease, transform 0.15s ease;
}

.picture-ok-btn:hover {
    background-color: #8e0009;
    transform: translateY(-1px);
}

/* Anim */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<!-- PROFILE PICTURE MODAL (Bootstrap) -->
<div class="modal fade" id="profilePictureModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header custom-modal-header">
        <h5 class="modal-title">
          <i class="fa-solid fa-image me-2"></i> Profile Picture
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center" style="position:relative;">

        <!-- 🔍 Expand button in upper-right of image area -->
        <button type="button"
                onclick="expandPicture()"
                style="
                    position:absolute;
                    top:10px;
                    right:10px;
                    background:#ffffffee;
                    border:1px solid #ccc;
                    border-radius:8px;
                    padding:5px 9px;
                    cursor:pointer;
                    font-size:0.9rem;
                    z-index:10;">
            <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
        </button>

        <h5 id="ppVolunteerName" class="fw-bold mb-3"></h5>

        <img id="ppModalImage"
             src=""
             style="max-width:95%; max-height:60vh; border-radius:8px; display:none; border: 1px solid #ccc">

        <p id="ppNoImageText" class="text-muted" style="display:none;">
            <i class="fa-regular fa-image fa-lg"></i><br>No Image Available
        </p>

        <input type="file" id="ppFileInput" accept="image/*" class="d-none">

        <div class="mt-4 d-flex justify-content-center gap-2">
          <button class="btn btn-outline-primary" type="button" onclick="triggerPPFileInput()">
            <i class="fa-solid fa-upload"></i> Replace Photo
          </button>
          <button class="btn btn-outline-danger" type="button" onclick="previewDefaultPicture()">
            <i class="fa-solid fa-user-xmark"></i> Set Default
          </button>
        </div>

        <div class="mt-4 d-flex justify-content-center gap-2">
          <!-- Toggles Edit → Save -->
          <button id="ppEditSaveBtn" type="button" class="btn btn-secondary" onclick="toggleEditOrSave()">
            <i class="fa-solid fa-pen-to-square"></i> Edit
          </button>
          <!-- Only revert local changes -->
          <button class="btn btn-secondary" type="button" onclick="revertPictureChanges()">
            <i class="fa-solid fa-rotate-left"></i> Revert
          </button>
        </div>

      </div>
    </div>
  </div>
</div>


<!-- MESSAGE / INFO MODAL (no server success used) -->
<div id="pictureMessageModal" class="picture-modal-overlay">
    <div class="picture-modal">
        <h3><i class="fa-solid fa-circle-check" style="color:#28a745;"></i> Message</h3>
        <p id="pictureMessageText"></p>

        <div class="picture-modal-buttons">
            <button type="button" class="picture-ok-btn" onclick="closePictureMessageModal()">OK</button>
        </div>
    </div>
</div>

<style>
#pictureExpandOverlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    justify-content: center;
    align-items: center;
    z-index: 999999;
}

.picture-expanded-modal {
    background: #fff;
    border-radius: 16px;
    padding: 15px 20px 20px;
    max-width: 95%;
    max-height: 90vh;
    width: 95%;
    display: flex;
    flex-direction: column;
}

/* Center the image container */
.picture-expanded-modal img {
    margin: 0 auto; /* horizontal center */
    display: block;
}
</style>

<!-- FULLSCREEN IMAGE VIEWER -->
<div id="pictureExpandOverlay" class="picture-modal-overlay">
    <div class="picture-expanded-modal">
        <h3 style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <span><i class="fa-solid fa-image"></i> Fullscreen Preview</span>
            <button type="button"
                    onclick="closeExpandedPicture()"
                    style="border:none; background:none; font-size:1.4rem; cursor:pointer;">
                <i class="fa-solid fa-xmark" style="color:#b2000c;"></i>
            </button>
        </h3>

        <img id="expandedPicture"
             src=""
             style="max-width:100%; max-height:75vh; border-radius:10px; border:1px solid #ccc;">
    </div>
</div>


<!-- OPTIONAL: FLASH MESSAGE FROM SERVER (does NOT use 'success') -->
@if(session('picture_message'))
<script>
    window.__picture_flash_message = `{!! session('picture_message') !!}`;
</script>
@endif


<!-- HIDDEN FORM FOR UPLOAD / DEFAULT -->
<form id="pictureForm" method="POST" enctype="multipart/form-data" style="display:none;">
    @csrf
    <input type="hidden" name="index" id="ppFormIndex">
    <input type="hidden" name="type"  id="ppFormType">
    <input type="file"   name="file"  id="ppFormFile" style="display:none;">
</form>
<script>
let currentVolunteerIndex = null;
let currentVolunteerType  = null;
let originalPictureSrc    = null;
let pendingUploadFile     = null;
let pendingDefault        = false;
let isEditingPicture      = false;

const UPDATE_PICTURE_URL = @json(route('volunteer.import.updatePicture'));
const SET_DEFAULT_URL    = @json(route('volunteer.import.setDefaultPicture'));

/* -----------------------------------------------------------
   OPEN PROFILE MODAL
----------------------------------------------------------- */
function openImageModalFromButton(btn) {
    currentVolunteerIndex = btn.dataset.entryIndex;
    currentVolunteerType  = btn.dataset.entryType;

    const src  = btn.dataset.pictureSrc || "";
    const name = btn.dataset.volName || "Unknown";

    originalPictureSrc = src || null;

    document.getElementById("ppVolunteerName").textContent = name;

    const img = document.getElementById("ppModalImage");
    const noImg = document.getElementById("ppNoImageText");

    if (src) {
        img.src = src;
        img.style.display = "block";
        noImg.style.display = "none";
    } else {
        img.style.display = "none";
        noImg.style.display = "block";
    }

    pendingUploadFile = null;
    pendingDefault = false;
    isEditingPicture = false;

    resetEditSaveButton();

    new bootstrap.Modal(document.getElementById("profilePictureModal")).show();
}

/* -----------------------------------------------------------
   EXPAND PICTURE
----------------------------------------------------------- */
function expandPicture() {
    const img = document.getElementById("ppModalImage");
    if (!img || img.style.display === "none" || !img.src) return;

    const expanded = document.getElementById("expandedPicture");
    expanded.src = img.src;

    const overlay = document.getElementById("pictureExpandOverlay");
    overlay.style.display = "flex";
}

function closeExpandedPicture() {
    document.getElementById("pictureExpandOverlay").style.display = "none";
}

document.addEventListener("click", (e) => {
    const overlay = document.getElementById("pictureExpandOverlay");
    if (overlay.style.display === "flex" && e.target === overlay) {
        closeExpandedPicture();
    }
});

/* -----------------------------------------------------------
   EDIT / SAVE
----------------------------------------------------------- */
function toggleEditOrSave() {
    const btn = document.getElementById("ppEditSaveBtn");

    if (!isEditingPicture) {
        isEditingPicture = true;
        btn.innerHTML = '<i class="fa-solid fa-save"></i> Save';
        btn.classList.remove("btn-secondary");
        btn.classList.add("btn-success");
    } else {
        savePictureChanges();
    }
}

function resetEditSaveButton() {
    const btn = document.getElementById("ppEditSaveBtn");
    btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Edit';
    btn.classList.remove("btn-success");
    btn.classList.add("btn-secondary");
}

/* -----------------------------------------------------------
   REPLACE PHOTO
----------------------------------------------------------- */
function triggerPPFileInput() {
    const input = document.getElementById("ppFileInput");
    input.value = "";
    input.click();

    input.onchange = () => {
        if (!input.files.length) return;

        pendingUploadFile = input.files[0];
        pendingDefault = false;

        if (!isEditingPicture) toggleEditOrSave();

        const reader = new FileReader();
        reader.onload = (e) => {
            const img = document.getElementById("ppModalImage");
            img.src = e.target.result;
            img.style.display = "block";
            document.getElementById("ppNoImageText").style.display = "none";
        };
        reader.readAsDataURL(pendingUploadFile);
    };
}

/* -----------------------------------------------------------
   SET DEFAULT PREVIEW
----------------------------------------------------------- */
function previewDefaultPicture() {
    pendingUploadFile = null;
    pendingDefault = true;

    if (!isEditingPicture) toggleEditOrSave();

    const img = document.getElementById("ppModalImage");
    img.src = "/storage/defaults/default_user.png";
    img.style.display = "block";
    document.getElementById("ppNoImageText").style.display = "none";
}

/* -----------------------------------------------------------
   REVERT
----------------------------------------------------------- */
function revertPictureChanges() {
    pendingUploadFile = null;
    pendingDefault = false;
    isEditingPicture = false;

    resetEditSaveButton();

    const img = document.getElementById("ppModalImage");
    const noImg = document.getElementById("ppNoImageText");

    if (originalPictureSrc) {
        img.src = originalPictureSrc;
        img.style.display = "block";
        noImg.style.display = "none";
    } else {
        img.style.display = "none";
        noImg.style.display = "block";
    }
}

/* -----------------------------------------------------------
   SAVE PICTURE
----------------------------------------------------------- */
function savePictureChanges() {

    const name = document.getElementById("ppVolunteerName").textContent || "this volunteer";

    if (!pendingUploadFile && !pendingDefault) {
        const message = `
            <strong>No changes made.</strong><br>
            The profile picture for <strong>${name}</strong> was not modified.
        `;
        showPictureMessageModal(message);
        return;
    }

    const form = document.getElementById("pictureForm");
    document.getElementById("ppFormIndex").value = currentVolunteerIndex;
    document.getElementById("ppFormType").value = currentVolunteerType;

    if (pendingUploadFile) {
        form.action = UPDATE_PICTURE_URL;
        document.getElementById("ppFormFile").files =
            document.getElementById("ppFileInput").files;
    } else {
        form.action = SET_DEFAULT_URL;
        document.getElementById("ppFormFile").value = "";
    }

    form.submit();
}

/* -----------------------------------------------------------
   PICTURE MESSAGE ONLY (never triggers global success)
----------------------------------------------------------- */
function showPictureMessageModal(message) {
    const o = document.getElementById("pictureMessageModal");
    document.getElementById("pictureMessageText").innerHTML = message;
    o.style.display = "flex";
}

function closePictureMessageModal() {
    document.getElementById("pictureMessageModal").style.display = "none";
}

/* -----------------------------------------------------------
   SHOW PICTURE MESSAGE ONLY WHEN controller sets picture_message
----------------------------------------------------------- */
document.addEventListener("DOMContentLoaded", () => {

    @if(session('picture_message'))
        showPictureMessageModal(`{!! session('picture_message') !!}`);
    @endif

});
</script>
