<?php 
  $pageTitle = "Event Details"; //Header Title

  include '../External_assets (shared)/PHP/auto-refresh.php'; 
  include '../External_assets (shared)/PHP/Universal-Header-Navbar.php';
  include '../External_assets (shared)/PHP/Universal-Back-Button.php';
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Event Details</title>
  <link href="styles.css" rel="stylesheet">
  <!-- Main CSS -->
  <link rel="stylesheet" href="{{ asset('assets/event_details/css/styles.css') }}">

  <!-- Boostrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <main class="container my-4">
      <div class="event-top card p-4 mb-3">
        <!-- Header row: title (left) + actions (right) -->
        <div class="d-flex justify-content-between align-items-start flex-wrap">
          <div class="event-title-container flex-grow-1 me-3">
            <h1 class="event-title mb-1">Birthday Event with Fr. Ernald</h1>
            <!-- smaller details below the title -->
            <div class="badges mt-2">
              <span class="badge bg-light text-dark me-2">AUGUST 31, 2025</span>
              <span class="badge bg-light text-dark me-2">08:00 AM - 12:00 NN</span>
              <span class="badge bg-light text-dark me-2">Barangay 1</span>
              <span class="badge bg-light text-dark me-2">District 1</span>
              <span class="badge bg-success text-white">Active</span>            
            </div>
          </div>

          <!-- Buttons aligned to the top-right of the header -->
          <div class="event-actions d-flex flex-column flex-sm-row gap-2 align-items-start">
            <a href="../SummaryReport&Dashboard_Balbin/index.php" class="btn btn-danger info-card"><i class="fas fa-file-lines"></i> View Summary Report</a>
            <a href="../Import_Attendance/Import_Attendance.php" class="btn btn-danger info-card"><i class="fas fa-upload"></i> Import Attendance</a>
          </div>
        </div>

        <!-- Event Description -->
        <div class="mt-3">
          <p>Join us for a community birthday celebration with Fr. Ernald. Food, games, and fellowship. Please arrive on time and bring a valid ID.</p>
        </div>
      </div>

      <section class="attendees">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h3>Attendees</h3>
          <div class="text-muted">Showing <span id="attendee-count">0</span> attendees</div>
        </div>
      </section>
      <section> 
      <div class="grid-container card p-3">
        <div id="cards-grid" class="cards-grid"></div>
      </div>
      </section>
      <section>
      <div class="navigation">
                <button id="arrow-up" class="btn arrow-btn" aria-pressed="false">
                  <svg viewBox="0 0 79 79" width="40" height="40" aria-hidden="true">
                    <path class="arrow-path"
                          d="M60.649 33.8088C64.1842 29.5165 61.1262 23.0417 55.5633 23.0417H23.4367C17.8737 23.0417 14.8191 29.5165 18.3543 33.8088L34.421 53.3185C35.0386 54.0686 35.8146 54.6727 36.6933 55.0875C37.572 55.5022 38.5316 55.7173 39.5033 55.7173C40.475 55.7173 41.4346 55.5022 42.3133 55.0875C43.192 54.6727 43.968 54.0686 44.5856 53.3185L60.649 33.8088Z"
                          fill="#888888" transform="rotate(180 39.5 39.5)"></path>
                  </svg>
                </button>

                <button id="arrow-down" class="btn arrow-btn mb-2" aria-pressed="false">
                  <svg viewBox="0 0 79 79" width="40" height="40" aria-hidden="true">
                    <path class="arrow-path"
                          d="M60.649 33.8088C64.1842 29.5165 61.1262 23.0417 55.5633 23.0417H23.4367C17.8737 23.0417 14.8191 29.5165 18.3543 33.8088L34.421 53.3185C35.0386 54.0686 35.8146 54.6727 36.6933 55.0875C37.572 55.5022 38.5316 55.7173 39.5033 55.7173C40.475 55.7173 41.4346 55.5022 42.3133 55.0875C43.192 54.6727 43.968 54.0686 44.5856 53.3185L60.649 33.8088Z"
                          fill="#888888"></path>
                  </svg>
                </button>
              </div>
      </section>
    </main>

  <!-- Load Bootstrap once (matching homepage version) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Reusable header scripts so the dropdowns/side-nav behave like on the homepage -->
  <script src="{{ asset('assets/event_details/js/script.css') }}"></script> 
  
  <!-- Add Student Modal -->
  <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addStudentModalLabel">Add Attendees</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="add-student-modal">
            <div class="split d-flex">
              <!-- Left: Available people -->
              <div class="left-list flex-fill">
                <h6>Available People</h6>
                <div class="list-scroll">
                  <!-- Example items: replace with dynamic items as needed -->
                  <!-- student cards styled like attendee cards but neutral for modal -->
                  <!-- Expanded list: 10 sample available people for testing -->
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="101">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="101">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Juan Dela Cruz</div>
                      <div class="course small text-muted">BSIT</div>
                    </div>
                  </div>
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="102">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="102">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Maria Santos</div>
                      <div class="course small text-muted">BSN</div>
                    </div>
                  </div>
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="103">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="103">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Carlos Reyes</div>
                      <div class="course small text-muted">BSBA</div>
                    </div>
                  </div>
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="104">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="104">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Ana Lopez</div>
                      <div class="course small text-muted">BSN</div>
                    </div>
                  </div>
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="105">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="105">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Mark Reyes</div>
                      <div class="course small text-muted">BSIT</div>
                    </div>
                  </div>
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="106">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="106">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Lucy Tan</div>
                      <div class="course small text-muted">BSBA</div>
                    </div>
                  </div>
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="107">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="107">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Peter Cruz</div>
                      <div class="course small text-muted">BSIT</div>
                    </div>
                  </div>
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="108">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="108">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Grace Lim</div>
                      <div class="course small text-muted">BSN</div>
                    </div>
                  </div>
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="109">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="109">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Daniel Ong</div>
                      <div class="course small text-muted">BSCOE</div>
                    </div>
                  </div>
                  <div class="student-card modal-student p-2 d-flex align-items-center" data-id="110">
                    <input type="checkbox" class="form-check-input me-2 available-check" data-id="110">
                    <img src="human.png" alt="avatar" class="avatar me-2" width="48" height="48">
                    <div class="meta">
                      <div class="name">Ivy Santos</div>
                      <div class="course small text-muted">BSIT</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right: Manually added in this modal -->
              <div class="right-list flex-fill ms-3">
                <h6>Added (in this modal)</h6>
                <div class="list-scroll selected-list d-flex flex-column">
                  <div class="empty small text-muted">No one added yet. Select people from the left and click <strong>Add selected</strong>.</div>
                </div>
              </div>
            </div>

            <!-- Controls -->
            <div class="mt-3 d-flex gap-2 justify-content-end controls">
              <button type="button" id="add-selected-btn" class="btn btn-outline-primary">Add selected</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" id="save-student-btn" class="btn btn-danger">Save</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Inline modal script to handle selection and transferring items -->
  <script>
    (function(){
      const addSelectedBtn = document.getElementById('add-selected-btn');
      const saveBtn = document.getElementById('save-student-btn');
      function query(selector, root=document){ return root.querySelector(selector); }
      function queryAll(selector, root=document){ return Array.from(root.querySelectorAll(selector)); }

      function addSelected(){
        const checks = queryAll('.available-check');
        const selectedList = query('.selected-list');
        const empty = query('.selected-list .empty');
        if(empty) empty.remove();

        checks.forEach(cb => {
          if(!cb.checked) return;
          const card = cb.closest('.student-card');
          if(!card) return;
          const id = cb.dataset.id;
          // avoid duplicates
          if(query('.selected-list .student-card[data-id="'+id+'"]')){
            cb.checked = false;
            return;
          }

          // Remove checkbox from the card (we'll re-create it if the user removes the card later)
          const cbInput = card.querySelector('input[type=checkbox]');
          if(cbInput) cbInput.remove();

          // add a Remove button to allow moving back
          const removeBtn = document.createElement('button');
          removeBtn.type = 'button';
          removeBtn.className = 'btn btn-sm btn-outline-secondary ms-auto remove-added';
          removeBtn.textContent = 'Remove';
          // make sure the remove button is aligned to the right
          card.appendChild(removeBtn);

          // move the actual card element from left to right
          card.classList.remove('modal-student');
          card.classList.add('mb-2');
          const parent = card.parentElement;
          if(parent) parent.removeChild(card);
          selectedList.appendChild(card);
        });
      }

      document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('remove-added')){
          const card = e.target.closest('.student-card');
          if(!card) return;
          const id = card.dataset.id;

          // remove the remove button
          const btn = card.querySelector('.remove-added');
          if(btn) btn.remove();

          // recreate the checkbox and insert at start of card
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.className = 'form-check-input me-2 available-check';
          checkbox.dataset.id = id;
          card.insertBefore(checkbox, card.firstChild);

          // move back to left list
          const leftScroll = query('.left-list .list-scroll');
          if(leftScroll) leftScroll.appendChild(card);

          // if selected list becomes empty, show placeholder
          const selList = query('.selected-list');
          if(selList && selList.children.length === 0){
            selList.innerHTML = '<div class="empty small text-muted">No one added yet. Select people from the left and click <strong>Add selected</strong>.</div>';
          }
        }
      });

      if(addSelectedBtn) addSelectedBtn.addEventListener('click', addSelected);

      if(saveBtn) saveBtn.addEventListener('click', function(){
        // Collect IDs of added people and create attendee cards in the grid
        const addedCards = queryAll('.selected-list .student-card');
        const grid = query('#cards-grid');
        let count = parseInt(document.getElementById('attendee-count').textContent || '0', 10);
        addedCards.forEach(card => {
          const id = card.dataset.id;
          // avoid duplicates in grid
          if(query('#cards-grid .student-card[data-id="'+id+'"]')) return;
          // build attendee card (reuse structure)
          const newCard = document.createElement('a');
          newCard.className = 'student-card';
          newCard.setAttribute('data-id', id);
          newCard.href = '#';
          const img = card.querySelector('img')?.cloneNode(true) || document.createElement('div');
          img.classList.add('avatar');
          const meta = document.createElement('div');
          meta.className = 'meta';
          const name = document.createElement('div');
          name.className = 'name';
          name.textContent = card.querySelector('.name')?.textContent || '';
          const course = document.createElement('div');
          course.className = 'course';
          course.textContent = card.querySelector('.course')?.textContent || '';
          meta.appendChild(name);
          meta.appendChild(course);
          newCard.appendChild(img);
          newCard.appendChild(meta);
          grid.appendChild(newCard);
          count += 1;
        });
        document.getElementById('attendee-count').textContent = count;
        // close modal
        const modalEl = document.getElementById('addStudentModal');
        const bsModal = bootstrap.Modal.getInstance(modalEl);
        if(bsModal) bsModal.hide();
        // clear selected list
        const selList = query('.selected-list');
        selList.innerHTML = '<div class="empty small text-muted">No one added yet. Select people from the left and click <strong>Add selected</strong>.</div>';
      });
    })();
  </script>
</body>
</html>