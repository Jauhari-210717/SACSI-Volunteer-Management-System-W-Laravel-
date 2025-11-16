<!-- Class Schedule Modal -->
<div class="modal fade" id="classScheduleModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content custom-schedule-modal">

      <!-- Modal Header -->
      <div class="modal-header custom-modal-header d-flex justify-content-between align-items-center">
        <h5 class="modal-title"><i class="fa-solid fa-calendar-days me-2"></i> Class Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body custom-modal-body">
        <div class="weekly-schedule">
          <table class="table schedule-table text-center">
            <thead>
              <tr>
                <th>Time</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Saturday</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="scheduleContent"></tbody>
          </table>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer custom-modal-footer d-flex justify-content-between">
        <div>
          <button type="button" class="btn btn-danger me-2" id="addRowBtnFooter">
            <i class="fa-solid fa-plus me-1"></i> Add Row
          </button>
        </div>
        <div>
          <button type="button" class="btn btn-secondary" id="editScheduleBtn">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
          </button>
          <button type="button" class="btn btn-success d-none" id="saveScheduleBtn">
            <i class="fa-solid fa-save me-1"></i> Save
          </button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            <i class="fa-solid fa-xmark me-1"></i> Close
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Class Schedule Message Modal -->
<div id="scheduleMessageModal" class="logout-modal-overlay" style="display:none;">
  <div class="logout-modal">
    <h3><i class="fa-solid fa-circle-exclamation" style="color:#d9534f;"></i> Notice</h3>
    <p id="scheduleMessageText">This is a message</p>
    <div class="modal-buttons">
      <button type="button" class="btn btn-secondary" onclick="closeScheduleMessageModal()">OK</button>
    </div>
  </div>
</div>



<!-- Hidden form for PUT submission -->
<form id="updateScheduleForm" method="POST" style="display:none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="schedule" id="scheduleInput">
</form>

<script>
const MAX_ROWS = 6;
const timeOptions = [
  "7:30-8:00","8:30-9:30","9:30-10:50","11:00-12:20","12:30-1:50",
  "2:00-3:30","3:30-5:00","5:00-6:30","6:30-7:30"
];

let currentType = null;
let currentIndex = null;
let currentVolunteerId = null; // 👈 NEW
const days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

// Open the modal
function openScheduleModal(scheduleString, type, index, volunteerId) {
  currentType = type;
  currentIndex = index;
  currentVolunteerId = volunteerId; // 👈 SAVE THE ID

  document.getElementById('editScheduleBtn').classList.remove('d-none');
  document.getElementById('saveScheduleBtn').classList.add('d-none');

  const container = document.getElementById('scheduleContent');
  container.innerHTML = '';

  const scheduleData = {};
  days.forEach(day => {
    const regex = new RegExp(day + ":\\s*([^]*?)(?=(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|$))", "i");
    const match = scheduleString.match(regex);
    let raw = (match && match[1]) ? match[1].trim() : "";

    raw = raw.replace(/No Class/gi, '').trim();
    scheduleData[day] = raw ? raw.split(/\s+/).filter(Boolean) : [];
  });

  let numRows = Math.max(...days.map(day => scheduleData[day].length));
  if (!Number.isFinite(numRows) || numRows <= 0) numRows = 1;
  numRows = Math.min(MAX_ROWS, numRows);

  for (let r = 0; r < numRows; r++) {
    const rowData = {};
    days.forEach(day => {
      rowData[day] = scheduleData[day][r] || "";
    });
    addScheduleRow(rowData);
  }

  document.getElementById('addRowBtnFooter').onclick = () => addScheduleRow();

  new bootstrap.Modal(document.getElementById('classScheduleModal')).show();
}

// Add a row
function addScheduleRow(rowData = null) {
  const container = document.getElementById('scheduleContent');
  if (container.children.length >= MAX_ROWS) {
    showMessageModal("You can only add up to " + MAX_ROWS + " rows.");
    return;
  }

  const tr = document.createElement('tr');
  tr.innerHTML = `<td class="schedule-time">${container.children.length + 1}</td>`;

  days.forEach(day => {
    const td = document.createElement('td');
    td.classList.add('schedule-entry');

    const value = (rowData && rowData.hasOwnProperty(day)) ? rowData[day] : "";
    td.textContent = value;
    td.style.backgroundColor = value ? "#d4edda" : "";

    tr.appendChild(td);
  });

  const delTd = document.createElement('td');
  const delBtn = document.createElement('button');
  delBtn.type = 'button';
  delBtn.className = "btn btn-sm btn-danger delete-row-btn";
  delBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';
  delBtn.addEventListener("click", () => {
    tr.remove();
    updateRowNumbers();
  });
  delTd.appendChild(delBtn);
  tr.appendChild(delTd);

  container.appendChild(tr);
}

function updateRowNumbers() {
  document.querySelectorAll("#scheduleContent tr").forEach((row, index) => {
    const timeCell = row.querySelector(".schedule-time");
    if (timeCell) timeCell.textContent = index + 1;
  });
}

document.getElementById('editScheduleBtn').addEventListener('click', () => {
  document.querySelectorAll("#scheduleContent tr").forEach(row => {
    row.querySelectorAll("td.schedule-entry").forEach(td => {
      const currentValue = td.textContent.trim();
      td.textContent = '';

      const select = document.createElement("select");
      select.classList.add("form-select", "form-select-sm");

      const placeholder = document.createElement("option");
      placeholder.value = "";
      placeholder.text = "No Class";
      select.appendChild(placeholder);

      timeOptions.forEach(opt => {
        const option = document.createElement("option");
        option.value = opt;
        option.text = opt;
        select.appendChild(option);
      });

      if (currentValue && !timeOptions.includes(currentValue)) {
        const opt = document.createElement("option");
        opt.value = currentValue;
        opt.text = currentValue;
        select.appendChild(opt);
      }

      select.value = currentValue || "";
      td.appendChild(select);
    });
  });

  document.getElementById('editScheduleBtn').classList.add('d-none');
  document.getElementById('saveScheduleBtn').classList.remove('d-none');
});

// ⭐⭐⭐ REAL SAVE FUNCTION WITH PUT REQUEST ⭐⭐⭐
document.getElementById('saveScheduleBtn').addEventListener('click', () => {
  const updatedSchedule = {};
  days.forEach(day => updatedSchedule[day] = []);

  document.querySelectorAll("#scheduleContent tr").forEach(row => {
    row.querySelectorAll("td.schedule-entry").forEach((td, colIndex) => {
      const sel = td.querySelector("select");
      let val = sel ? sel.value.trim() : td.textContent.trim();
      val = val === "" ? "" : val;
      updatedSchedule[days[colIndex]].push(val);

      td.textContent = val;
      td.style.backgroundColor = val ? "#d4edda" : "";
    });
  });

  const displaySchedule = {};
  days.forEach(day => {
    displaySchedule[day] = updatedSchedule[day].map(t => t || "No Class");
  });

  const scheduleStr = formatScheduleString(displaySchedule);

  // Update session via hidden PUT form
  const form = document.getElementById('updateScheduleForm');
  const scheduleInput = document.getElementById('scheduleInput');

  scheduleInput.value = scheduleStr;
  form.action = `/volunteer-import/volunteers/${currentIndex}/update-schedule`; // index = session id

  form.submit(); // 🚀 PUT request
});


// Format object into string
function formatScheduleString(scheduleObj) {
  return Object.entries(scheduleObj).map(([day, times]) => {
    return day + ': ' + (times.length ? times.join(' ') : 'No Class');
  }).join(' ');
}

function showMessageModal(msg) {
  const el = document.getElementById('messageModalText');
  if (el) el.textContent = msg;
  const overlay = document.getElementById('messageModal');
  if (overlay) overlay.style.display = 'flex';
}
function closeMessageModal() {
  const overlay = document.getElementById('messageModal');
  if (overlay) overlay.style.display = 'none';
}

function showScheduleSuccessModal(message) {
  const overlay = document.getElementById('scheduleSuccessModal');
  const textEl = document.getElementById('scheduleSuccessText');

  textEl.innerHTML = message;
  overlay.style.display = 'flex';
}

function showScheduleMessageModal(message) {
  const overlay = document.getElementById('scheduleMessageModal');
  const textEl = document.getElementById('scheduleMessageText');
  textEl.innerHTML = message;
  overlay.style.display = 'flex';
}

function closeScheduleMessageModal() {
  const overlay = document.getElementById('scheduleMessageModal');
  overlay.style.display = 'none';
}

// Show modal automatically if Laravel session has success/info
document.addEventListener("DOMContentLoaded", function() {
    @if(session('success') || session('info'))
        const msg = {!! json_encode(session('success') ?? session('info')) !!};
        showScheduleMessageModal(msg);
    @endif
});


</script>




<!-- Custom CSS -->
<style>
.custom-schedule-modal {
  border-radius: 15px;
  font-family: 'Segoe UI', Roboto, sans-serif;
  overflow: hidden;
}

.custom-modal-header {
  background-color: #c82333;
  color: white;
  font-weight: 600;
  border-bottom: none;
}

.custom-modal-body {
  background-color: #fff5f5;
  padding: 1rem 1.5rem;
}

.schedule-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.schedule-table th, .schedule-table td {
  border: 1px solid #f1c0c3;
  padding: 0.5rem;
}

.schedule-table th {
  background-color: #e4606d;
  color: white;
  font-weight: 600;
}

.schedule-table tbody tr:nth-child(even) {
  background-color: #ffe5e8;
}

.schedule-table tbody tr:hover {
  background-color: #f9b2bc;
}

.schedule-time {
  font-weight: 600;
  color: #b71c1c;
}

.schedule-entry {
  font-weight: 500;
  color: #4d0000;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  background-color: #f8d0d5;
  margin: 2px 0;
}
</style>
