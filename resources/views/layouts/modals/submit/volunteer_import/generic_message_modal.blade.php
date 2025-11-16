<!-- Generic Message Modal -->
<div id="messageModal" class="logout-modal-overlay" style="display:none;">
  <div class="logout-modal">
    <h3><i class="fa-solid fa-circle-exclamation" style="color:#d9534f;"></i> Notice</h3>
    <p id="messageModalText">This is a message</p>
    <div class="modal-buttons" id="messageModalButtons">
      <button type="button" class="btn btn-secondary" onclick="closeMessageModal()">OK</button>
      <button type="button" class="btn btn-danger" onclick="closeMessageModal()">Close</button>
    </div>
  </div>
</div>

<script>
function showMessageModal(message) {
  document.getElementById('messageModalText').textContent = message;
  document.getElementById('messageModal').style.display = 'flex';
}

function closeMessageModal() {
  document.getElementById('messageModal').style.display = 'none';
}
</script>
