<Style>
    
/* Edit Import Log Modal */
.edit-import-log-modal {
  position: fixed;
  inset: 0;
  display: none;
  z-index: 9999;
  font-family: 'Segoe UI', Roboto, sans-serif;
}

.edit-import-log-modal .modal-overlay {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.55);
}

.edit-import-log-modal .modal-content {
    background: #fff;
    border-radius: 16px;
    width: 90%;
    max-width: 650px;
    padding: 2rem;
    box-shadow: 0 12px 40px rgba(0,0,0,0.35);
    text-align: center;
    animation: slideIn 0.3s ease forwards;
}

/* Small outline buttons inside Import Log Modal */
.edit-import-log-modal .btn-sm {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;           /* space between icon and text */
    padding: 0.35rem 0.75rem;
    font-size: 0.875rem;   /* slightly smaller text */
    font-weight: 500;
    border: 1px solid #ced4da;
    border-radius: 6px;
    background-color: transparent;
    color: #333;
    cursor: pointer;
    transition: all 0.25s ease;
}

/* Hover effect */
.edit-import-log-modal .btn-sm:hover {
    background-color: #f8f9fa;
    border-color: #b2000c;
    color: #b2000c;
}

/* Icon inside button */
.edit-import-log-modal .btn-sm i {
    font-size: 0.9rem;
    transition: transform 0.2s ease, color 0.2s ease;
}

/* Rotate icon slightly on hover */
.edit-import-log-modal .btn-sm:hover i {
    transform: rotate(10deg);
    color: #b2000c;
}

</Style>