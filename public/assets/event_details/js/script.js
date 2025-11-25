// Render static ATTENDEES list and paginate, using the same card template as Volunteer_List
const cardsGrid = document.getElementById('cards-grid');
const attendeeCount = document.getElementById('attendee-count');
const arrowUp = document.getElementById('arrow-up');
const arrowDown = document.getElementById('arrow-down');

const ATTENDEES = [
  {id:1, name:'David #230046', course:'BSIT', profile_pic:'Profile_Images/human.png'},
  {id:2, name:'Maria My', course:'BSCOE', profile_pic:'Profile_Images/fish.png'},
  {id:3, name:'Juan Dela Cruz', course:'BSIT', profile_pic:'Profile_Images/human.png'},
  {id:4, name:'Juan Dela Cruz', course:'BSCS', profile_pic:'Profile_Images/human.png'},
  {id:5, name:'Juan Dela Cruz', course:'BSCS', profile_pic:'Profile_Images/human.png'},
  {id:6, name:'Juan Dela Cruz', course:'BSCS', profile_pic:'Profile_Images/human.png'},
  {id:7, name:'Juan Dela Cruz', course:'BSCS', profile_pic:'Profile_Images/human.png'},
  {id:8, name:'David #230046', course:'BSIT', profile_pic:'Profile_Images/human.png'},
  {id:9, name:'Maria Santos', course:'BSCS', profile_pic:'Profile_Images/human.png'},
  {id:10, name:'Juan Dela Cruz', course:'BSIT', profile_pic:'Profile_Images/human.png'},
  {id:11, name:'Juan Dela Cruz', course:'BSCS', profile_pic:'Profile_Images/human.png'},
  {id:12, name:'Juan Dela Cruz', course:'BSCS', profile_pic:'Profile_Images/human.png'},
  {id:13, name:'Juan Dela Cruz', course:'BSCS', profile_pic:'Profile_Images/human.png'},
  {id:14, name:'Juan Dela Cruz', course:'BSCS', profile_pic:'Profile_Images/human.png'},
  {id:15, name:'David #230046', course:'BSIT', profile_pic:'Profile_Images/human.png'}
];

let currentPage = 1;
const itemsPerPage = 12;

function escapeHtml(text){ if(!text) return ''; return text.replace(/[&<>\"',]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;', '"':'&quot;', "'":'&#39;', ',':'&#44;'})[c]); }

function getPageData(page=1){
  const total = ATTENDEES.length;
  const pageSize = itemsPerPage;
  const totalPages = Math.max(1, Math.ceil(total/pageSize));
  const p = Math.min(Math.max(1,page), totalPages);
  const start = (p-1)*pageSize;
  const rows = ATTENDEES.slice(start, start+pageSize);
  return {page: p, pageSize, total, totalPages, rows};
}

function render(page = 1) {
  const data = getPageData(page);
  cardsGrid.innerHTML = '';

  // Render all valid student cards
  data.rows.forEach(s => {
    const profile = s.profile_pic && s.profile_pic.length ? s.profile_pic : 'Profile_Images/human.png';
    const card = document.createElement('a');
    card.className = 'student-card';
    card.href = `../VolunteerProfile_Ramirez/volunteer_Profile.php?id=${encodeURIComponent(s.id)}`;
    card.setAttribute('aria-label', `View profile of ${s.name}`);
    card.innerHTML = `
      <img src="${profile}" alt="${escapeHtml(s.name)}" class="avatar" 
           onerror="this.onerror=null;this.src='../Volunteer_List/Profile_Images/human.png'">
      <div class="meta">
        <div class="name">${escapeHtml(s.name)}</div>
        <div class="course">${escapeHtml(s.course)}</div>
      </div>
    `;
    cardsGrid.appendChild(card);
  });

  // ✅ Add card only if this is the *final* page of the entire list
  const isFinalPage = page === Math.ceil(ATTENDEES.length / itemsPerPage);
  if (isFinalPage) {
    const addCard = document.createElement('button');
    addCard.type = 'button';
    addCard.className = 'student-card add-student-card';
    addCard.setAttribute('aria-label', 'Add new student');
    addCard.innerHTML = `
      <div class="avatar add-avatar">+</div>
      <div class="meta">
        <div class="name">Add Student</div>
        <div class="course">Click to add attendee</div>
      </div>
    `;
    addCard.addEventListener('click', () => {
      const modalEl = document.getElementById('addStudentModal');
      const bsModal = new bootstrap.Modal(modalEl);
        const formEl = document.getElementById('add-student-form');
        const errEl = document.getElementById('add-student-error');
        // Some pages don't include the legacy "add-student-form" — guard before calling reset()
        if (formEl) {
          if (typeof formEl.reset === 'function') {
            formEl.reset();
          }
        }
        if (errEl) errEl.style.display = 'none';
      bsModal.show();
    });
    cardsGrid.appendChild(addCard);
  }

  attendeeCount.textContent = data.total;
  currentPage = data.page;
  arrowUp.disabled = currentPage <= 1;
  arrowDown.disabled = currentPage >= data.totalPages;
}

arrowUp.addEventListener('click', ()=>{ if(currentPage>1) render(currentPage-1); });
arrowDown.addEventListener('click', ()=>{ render(currentPage+1); });



// navigation (page up/down)
arrowUp.addEventListener('click', () => {
    // transient animation
    arrowUp.classList.remove('arrow-animate');
    void arrowUp.offsetWidth;
    arrowUp.classList.add('arrow-animate');

    if (currentPage > 1) {
        loadStudents(currentPage - 1);
    }
});

arrowDown.addEventListener('click', () => {
    arrowDown.classList.remove('arrow-animate');
    void arrowDown.offsetWidth;
    arrowDown.classList.add('arrow-animate');

    loadStudents(currentPage + 1);
});

// initial render
render(currentPage);

// handle old-style save from modal only if the legacy form inputs exist
const saveBtn = document.getElementById('save-student-btn');
if(saveBtn){
  const legacyName = document.getElementById('add-student-name');
  // attach legacy handler only when legacy inputs exist (we now use a different modal flow)
  if(legacyName){
    saveBtn.addEventListener('click', ()=>{
      const name = document.getElementById('add-student-name').value.trim();
      const course = document.getElementById('add-student-course').value.trim();
      const sid = document.getElementById('add-student-id').value.trim();
      const errEl = document.getElementById('add-student-error');
      if(!name){ errEl.textContent = 'Name is required.'; errEl.style.display = 'block'; return; }
      const nextId = ATTENDEES.length ? Math.max(...ATTENDEES.map(a=>a.id)) + 1 : 1;
      const newStudent = { id: nextId, name: name + (sid ? (' #' + sid) : ''), course: course || '—', profile_pic: 'Profile_Images/human.png' };
      ATTENDEES.push(newStudent);
      // close modal
      const modalEl = document.getElementById('addStudentModal');
      const bsModal = bootstrap.Modal.getInstance(modalEl);
      if(bsModal) bsModal.hide();
      // go to last page where the new student will appear
      const totalPages = Math.max(1, Math.ceil(ATTENDEES.length / itemsPerPage));
      render(totalPages);
    });
  }
}
