<style>
/* === Base Container === */
.search-container {
  position: relative;
  background: #fff;
  border-radius: 12px;
  overflow: visible;
  padding: 8px 12px;
  width: 100%;
  max-width: 600px;
  margin: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}


/* === Row Layout (Search + Results + Sort) === */
.search-row {
  display: flex;
  align-items: center;
  justify-content: flex-start; /* changed from space-between */
  gap: 18px; /* tighter consistent spacing */
  flex-wrap: nowrap; /* Prevent stacking */
  width: 100%;
}

/* === Search Box === */
.search-box {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1 1 45%;
  min-width: 240px;
  max-width: 400px;
}

.search-box input {
  width: 100%;
  font-size: clamp(0.85rem, 1vw + 0.5rem, 1rem);
  padding: 10px 38px 10px 14px;
  border: 2px solid #ccc;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.search-box input:focus {
  border-color: #c00;
  outline: none;
  box-shadow: 0 0 5px rgba(204, 0, 0, 0.3);
}

/* Search Icon */
.search-box .icon {
  position: absolute;
  right: 12px;
  font-size: 1rem;
  color: #777;
  pointer-events: none;
  transition: color 0.3s ease;
}

.search-box input:focus + .icon {
  color: #c00;
}

.search-box .icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-size: clamp(0.9rem, 1vw, 1.2rem);
  color: #888;
  transition: transform 0.4s ease, color 0.3s ease;
}

.search-box .icon:hover {
  color: #c00;
}

.search-box:focus-within .icon {
  transform: translateY(-50%) rotate(20deg);
  color: #c00;
}

/* === Results Count === */
.results-count {
  font-size: clamp(0.8rem, 0.9vw + 0.3rem, 0.95rem);
  color: #555;
  font-weight: 500;
  white-space: nowrap;
  text-align: center;
  flex: 0 1 auto;
  min-width: 90px;
}

/* === Sort By Button === */
.sort-by {
  font-weight: bold;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: #333;
  padding: 8px 14px;
  border: 2px solid #ccc;
  border-radius: 8px;
  background: #fff;
  transition: all 0.25s ease;
  flex-shrink: 0;
  white-space: nowrap;
  justify-content: center;
  align-items: center;
}

.sort-by:hover {
  background: #f2f2f2;
  transform: scale(1.05);
}

.sort-by .filter-icon {
  font-size: 16px;
  transition: color 0.25s ease, transform 0.25s ease;
}

.sort-by .icon {
  font-size: 18px;
  transition: transform 0.3s ease, color 0.25s ease;
}

.sort-by.active {
  background: #c00;
  color: #fff;
  transform: scale(1.05);
}

.sort-by.active .icon {
  transform: rotate(180deg);
  color: #fff;
}

.sort-by.active .filter-icon {
  color: #fff;
}

/* === Sort Options Dropdown === */
.sort-options {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  width: 100%;
  z-index: 99;
  max-height: 0;
  opacity: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 0 10px;
  transition: all 0.25s ease;
  pointer-events: none;
  outline: 2px solid #c00;
}

.sort-options.open {
  background: #f5f7fa; /* <- change to any color you want */
  max-height: 800px;
  opacity: 1;
  pointer-events: auto;
  padding-top: 10px;
  padding-bottom: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.sort-options i {
  margin-right: 6px;
  transition: transform 0.2s ease, color 0.2s ease;
}

.sort-options i:hover {
  transform: rotate(-10deg) scale(1.1);
  color: #b71c1c;
}

.custom-select {
  background: #f5f7fa; /* <- change to any color you want */
  position: relative;
  width: 100%;   /* full width inside the dropdown */
  min-width: 160px; /* optional */
  cursor: pointer;
  margin-bottom: 10px; /* spacing between selects */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: border-color 0.2s, box-shadow 0.2s;
}


/* Trigger button */
.custom-select-trigger {
  display: block;
  width: 100%;
  background: white;
  border: 2px solid #ccc;
  padding: 8px;
  border-radius: 6px;
  transition: border-color 0.3s, background 0.3s;
}

/* When hovering over the trigger, rotate the icon inside it */
.custom-select-trigger:hover i {
  transform: rotate(-10deg);
  color: #e60000;
  transition: transform 0.2s ease, color 0.2s ease;
}

/* Smooth transition back */
.custom-select-trigger i {
  transition: transform 0.2s ease, color 0.2s ease;
  color: #c62828; /* default red */
}

.custom-select-trigger:hover {
  border-color: #dc3545;
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);
}

.custom-select.open .custom-select-trigger {
  border-color: #c00;
}

.custom-select-trigger,
.custom-option {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
  transition: color 0.2s ease, transform 0.2s ease;
}

/* Make hover states pop a little */
.custom-select-trigger:hover {
  color: #e53935;
  transform: translateX(2px);
}

/* Dropdown options */
.custom-options {
  display: none;
  position: absolute;    /* critical */
  top: calc(100% + 2px);
  left: 0;
  width: 100%;
  max-height: 180px;     /* max height for scrolling */
  overflow-y: auto;      /* enable vertical scroll */
  overflow-x: hidden;
  border: 2px solid #c00;
  background: #fff;
  border-radius: 6px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  z-index: 999;
}

.custom-select.open .custom-options {
  display: block;
}

/* Scrollbar styling */
.custom-options::-webkit-scrollbar {
  width: 6px;
}
.custom-options::-webkit-scrollbar-thumb {
  background-color: #c00;
  border-radius: 3px;
}

/* Individual option */
.custom-option {
  display: block;
  padding: 8px;
  transition: background 0.2s, color 0.2s;
}

.custom-option:hover {
  background: #c00;
  color: #fff;
}
.custom-option i {
  color: #666;
  min-width: 18px;
  text-align: center;
}

.custom-option:hover i {
  color: #fff;
}

.actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
}

.right-actions {
  display: flex;
  align-items: center;
  gap: 10px; /* space between Reset & Apply */
}


.right-actions button {
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}

/* === Buttons for Apply / Reset === */
.actions {
  display: flex;
  justify-content: space-between;
  width: 100%;
  margin-top: 10px;
  align-items: center;
}


/* --- Reset Button --- */
.reset-btn {
  background: #b8bcc0ff;
  color: #333;
  border: 1px solid #ccc;
  border-radius: 10px;
  padding: 8px 14px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.25s ease;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.reset-btn:hover {
  background: #d8dbdfff;
  border-color: #bbb;
  transform: translateY(-2px);
}

.reset-btn:active {
  transform: scale(0.97);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* --- Apply Button --- */
.apply-btn {
  background: #b2000c;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 8px 14px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.25s ease;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
}

.apply-btn:hover {
  background: #8f000a;
  transform: translateY(-2px);
}

.apply-btn:active {
  background: #6b0007;
  transform: scale(0.97);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
}

/* ===== Remove shadows + set background for the search container ===== */
.search-container {
  box-shadow: none !important; /* remove container shadow */
}

/* Remove focus glow on the input if you want no shadow on focus */
.search-box input:focus {
  border-color: #c00;    /* keep your red border if desired */
  outline: none;
  box-shadow: none;      /* remove the focus shadow */
}

/* Remove dropdown and select shadows if you want fully flat UI */
.sort-options,
.custom-options {
  box-shadow: none !important;
  background: inherit; /* inherit container bg or set a specific bg */
}

/* === Two-column layout for large screens (1920px and up) === */


/* === Two-column layout for large screens (1920px and up) === */
@media (min-width: 1920px) {
  .sort-options {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr); /* two equal columns */
    column-gap: 16px;
    row-gap: 10px;
    align-items: start;
    padding: 12px 16px;
  }

  .custom-select {
    width: 100%;
    margin-bottom: 0;
  }

  /* Actions container spans both columns */
  .sort-options .actions {
    grid-column: 1 / -1; /* span both columns */
    display: flex;
    justify-content: flex-end; /* push buttons to right */
    align-items: center;
    margin-top: 12px;
    gap: 10px; /* space between Reset & Apply */
  }

  /* Individual buttons */
  .sort-options .reset-btn,
  .sort-options .apply-btn {
    min-width: 100px;
    padding: 8px 14px;
    font-size: 0.9rem;
    border-radius: 8px;
  }

  /* Apply button style */
  .sort-options .apply-btn {
    background-color: #B2000C;
    color: #fff !important;
    border: none;
  }

  /* Reset button style */
  .sort-options .reset-btn {
    background: #b8bcc0ff;
    color: #333;
    border: 1px solid #ccc;
  }
}


/* --- Responsive scaling for smaller screens --- */
@media (max-width: 768px) {
  .reset-btn,
  .apply-btn {
    padding: 6px 10px;
    font-size: 0.8rem;
    border-radius: 8px;
  }

  .right-actions {
    gap: 6px;
  }
}
/* If you want the inner elements (like the search input) to remain white,
   keep them as-is, otherwise make them match the container:
.search-box input {
  background: transparent;  / * or set same as container * /
}

/* Optional small polish: make the top controls sit flush on flat background */
.search-row {
  align-items: center;
}

/* === Sort by Status Enhancements === */
.custom-options[data-field="status"] .custom-option i {
  min-width: 18px;
  text-align: center;
  transition: transform 0.2s ease, color 0.2s ease;
}

.custom-options[data-field="status"] .custom-option:hover i {
  transform: rotate(-10deg) scale(1.1);
}


/* === Responsive Behavior === */

/* Medium screens */
@media (max-width: 992px) {
  .search-row {
    gap: 12px;
  }

  .search-box {
    flex: 1 1 40%;
  }

  .results-count {
    font-size: 0.9rem;
  }

  .sort-by {
    padding: 6px 10px;
  }

  /* Table actions */
  .table-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
  }

  .table-actions button {
    flex: 1 1 auto;
    font-size: 0.85rem;
  }
}

/* Small screens */
@media (max-width: 768px) {
  .search-row {
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
  }

  .search-box,
  .results-count,
  .sort-by {
    flex: 1 1 100%;
    text-align: center;
  }

  .sort-by {
    padding: 8px 12px;
  } 

    /* === Core: prevent clipping === */
  .data-table-container,
  .search-container,
  .table-controls,
  .database-container {
    overflow: visible !important;
  }

  /* Sort panel itself */
  .sort-options {
    position: absolute;
    z-index: 9999;
    background: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);           /* narrower default width */
  }

  /* Individual custom selects */
  .custom-select {
    position: relative;
  }

  /* Detached dropdown popout (after JS reparenting) */
  .custom-options {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 180px;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    z-index: 10000;
    display: none;
  }

  /* Show state */
  .custom-select.open .custom-options {
    display: block;
  }
  
  /* Highlight both trigger and dropdown when active */
.custom-select.open .custom-select-trigger,
.custom-select.open .custom-options {
  border: 2px solid #dc3545; /* Red border same as your trigger */
  box-shadow: 0 0 5px rgba(220, 53, 69, 0.5); /* Optional subtle glow */
}


  /* Actions inside sort-options */
  .actions {
    flex-direction: row;
    justify-content: space-between;
    width: 100%;
    margin-top: 10px;
    gap: 8px;
  }

  .apply-btn,
  .reset-btn {
    flex: 1 1 48%;
    padding: 8px 12px;
  }

  /* Table actions */
  .table-actions {
    flex-direction: row;
    gap: 6px;
    flex-wrap: wrap;
  }

  .table-actions button {
    flex: 1 1 48%;
    font-size: 0.85r  em;
    padding: 6px 8px;
  }
}

/* Extra small screens */
@media (max-width: 480px) {
  .search-row {
    flex-direction: column;
    gap: 8px;
  }

  .search-box input {
    font-size: 0.9rem;
    padding: 8px 34px 8px 12px;
  }

  .results-count,
  .sort-by {
    flex: 1 1 100%;
    text-align: center;
    width: 100%;
  }

  .sort-by {
    padding: 6px 10px;
    font-size: 0.85rem;
    
  }

  /* Actions inside sort-options */
  .actions {
    flex-direction: column;
    gap: 6px;
  }

  .apply-btn,
  .reset-btn {
    width: 100%;
    font-size: 0.85rem;
    padding: 8px 0;
  }

  /* Table actions */
  .table-actions {
    flex-direction: column;
    gap: 6px;
    width: 100%;
  }

  .table-actions button {
    width: 100%;
    font-size: 0.85rem;
    padding: 8px 0;
  }
}

</style>

<style>
/* === Base Container === */
.search-container {
  position: relative;
  background: #fff;
  border-radius: 12px;
  overflow: visible;
  padding: 8px 12px;
  width: 100%;
  max-width: 600px;
  margin: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* === Row Layout (Search + Results + Sort) === */
.search-row {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 18px;
  flex-wrap: nowrap;
  width: 100%;
}

/* === Search Box === */
.search-box {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1 1 45%;
  min-width: 240px;
  max-width: 400px;
}

.search-box input {
  width: 100%;
  font-size: clamp(0.85rem, 1vw + 0.5rem, 1rem);
  padding: 10px 38px 10px 14px;
  border: 2px solid #ccc;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.search-box input:focus {
  border-color: #c00;
  outline: none;
  box-shadow: 0 0 5px rgba(204, 0, 0, 0.3);
}

/* Search Icon */
.search-box .icon {
  position: absolute;
  right: 12px;
  font-size: 1rem;
  color: #777;
  pointer-events: none;
  transition: color 0.3s ease;
}

.search-box input:focus + .icon {
  color: #c00;
}

.search-box .icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-size: clamp(0.9rem, 1vw, 1.2rem);
  color: #888;
  transition: transform 0.4s ease, color 0.3s ease;
}

.search-box .icon:hover {
  color: #c00;
}

.search-box:focus-within .icon {
  transform: translateY(-50%) rotate(20deg);
  color: #c00;
}

/* === Results Count === */
.results-count {
  font-size: clamp(0.8rem, 0.9vw + 0.3rem, 0.95rem);
  color: #555;
  font-weight: 500;
  white-space: nowrap;
  text-align: center;
  flex: 0 1 auto;
  min-width: 90px;
}

/* === Sort By Button === */
.sort-by {
  font-weight: bold;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: #333;
  padding: 8px 14px;
  border: 2px solid #ccc;
  border-radius: 8px;
  background: #fff;
  transition: all 0.25s ease;
  flex-shrink: 0;
  white-space: nowrap;
  justify-content: center;
  align-items: center;
}

.sort-by:hover {
  background: #f2f2f2;
  transform: scale(1.05);
}

.sort-by .filter-icon {
  font-size: 16px;
  transition: color 0.25s ease, transform 0.25s ease;
}

.sort-by .icon {
  font-size: 18px;
  transition: transform 0.3s ease, color 0.25s ease;
}

.sort-by.active {
  background: #c00;
  color: #fff;
  transform: scale(1.05);
}

.sort-by.active .icon {
  transform: rotate(180deg);
  color: #fff;
}

.sort-by.active .filter-icon {
  color: #fff;
}

/* === Sort Options Dropdown === */
.sort-options {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  width: 100%;
  z-index: 99;
  max-height: 0;
  opacity: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 0 10px;
  transition: all 0.25s ease;
  pointer-events: none;
  outline: 2px solid #c00;
}

.sort-options.open {
  background: #f5f7fa;
  max-height: 800px;
  opacity: 1;
  pointer-events: auto;
  padding-top: 10px;
  padding-bottom: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.sort-options i {
  margin-right: 6px;
  transition: transform 0.2s ease, color 0.2s ease;
}

.sort-options i:hover {
  transform: rotate(-10deg) scale(1.1);
  color: #b71c1c;
}

.custom-select {
  background: #f5f7fa;
  position: relative;
  width: 100%;
  min-width: 160px;
  cursor: pointer;
  margin-bottom: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: border-color 0.2s, box-shadow 0.2s;
}

/* Trigger button */
.custom-select-trigger {
  display: block;
  width: 100%;
  background: white;
  border: 2px solid #ccc;
  padding: 8px;
  border-radius: 6px;
  transition: border-color 0.3s, background 0.3s;
}

/* Hover icon animation */
.custom-select-trigger:hover i {
  transform: rotate(-10deg);
  color: #e60000;
  transition: transform 0.2s ease, color 0.2s ease;
}

.custom-select-trigger i {
  transition: transform 0.2s ease, color 0.2s ease;
  color: #c62828;
}

.custom-select-trigger:hover {
  border-color: #dc3545;
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);
}

.custom-select.open .custom-select-trigger {
  border-color: #c00;
}

.custom-select-trigger,
.custom-option {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
  transition: color 0.2s ease, transform 0.2s ease;
}

.custom-select-trigger:hover {
  color: #e53935;
  transform: translateX(2px);
}

/* Dropdown options */
.custom-options {
  display: none;
  position: absolute;
  top: calc(100% + 2px);
  left: 0;
  width: 100%;
  max-height: 180px;
  overflow-y: auto;
  overflow-x: hidden;
  border: 2px solid #c00;
  background: #fff;
  border-radius: 6px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  z-index: 999;
}

.custom-select.open .custom-options {
  display: block;
}

/* Scrollbar styling */
.custom-options::-webkit-scrollbar {
  width: 6px;
}
.custom-options::-webkit-scrollbar-thumb {
  background-color: #c00;
  border-radius: 3px;
}

/* Individual option */
.custom-option {
  display: block;
  padding: 8px;
  transition: background 0.2s, color 0.2s;
}

.custom-option:hover {
  background: #c00;
  color: #fff;
}
.custom-option i {
  color: #666;
  min-width: 18px;
  text-align: center;
}

.custom-option:hover i {
  color: #fff;
}

/* Actions row (Reset / Apply) */
.actions {
  display: flex;
  justify-content: space-between;
  width: 100%;
  margin-top: 10px;
  align-items: center;
}

.right-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.right-actions button {
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}

/* --- Reset Button --- */
.reset-btn {
  background: #b8bcc0ff;
  color: #333;
  border: 1px solid #ccc;
  border-radius: 10px;
  padding: 8px 14px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.25s ease;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.reset-btn:hover {
  background: #d8dbdfff;
  border-color: #bbb;
  transform: translateY(-2px);
}

.reset-btn:active {
  transform: scale(0.97);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* --- Apply Button --- */
.apply-btn {
  background: #b2000c;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 8px 14px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.25s ease;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
}

.apply-btn:hover {
  background: #8f000a;
  transform: translateY(-2px);
}

.apply-btn:active {
  background: #6b0007;
  transform: scale(0.97);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
}

/* Remove container shadow (flat UI) */
.search-container {
  box-shadow: none !important;
}

/* Remove focus glow on the input */
.search-box input:focus {
  border-color: #c00;
  outline: none;
  box-shadow: none;
}

/* Remove dropdown shadows */
.sort-options,
.custom-options {
  box-shadow: none !important;
  background: inherit;
}

/* === Two-column layout for large screens (1920px and up) === */
@media (min-width: 1920px) {
  .sort-options {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr);
    column-gap: 16px;
    row-gap: 10px;
    align-items: start;
    padding: 12px 16px;
  }

  .custom-select {
    width: 100%;
    margin-bottom: 0;
  }

  .sort-options .actions {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-top: 12px;
    gap: 10px;
  }

  .sort-options .reset-btn,
  .sort-options .apply-btn {
    min-width: 100px;
    padding: 8px 14px;
    font-size: 0.9rem;
    border-radius: 8px;
  }

  .sort-options .apply-btn {
    background-color: #B2000C;
    color: #fff !important;
    border: none;
  }

  .sort-options .reset-btn {
    background: #b8bcc0ff;
    color: #333;
    border: 1px solid #ccc;
  }
}

/* --- Responsive scaling for smaller screens --- */
@media (max-width: 768px) {
  .reset-btn,
  .apply-btn {
    padding: 6px 10px;
    font-size: 0.8rem;
    border-radius: 8px;
  }

  .right-actions {
    gap: 6px;
  }
}

.search-row {
  align-items: center;
}

/* === Sort by Status Enhancements === */
.custom-options[data-field="status"] .custom-option i {
  min-width: 18px;
  text-align: center;
  transition: transform 0.2s ease, color 0.2s ease;
}

.custom-options[data-field="status"] .custom-option:hover i {
  transform: rotate(-10deg) scale(1.1);
}

/* Medium screens */
@media (max-width: 992px) {
  .search-row {
    gap: 12px;
  }

  .search-box {
    flex: 1 1 40%;
  }

  .results-count {
    font-size: 0.9rem;
  }

  .sort-by {
    padding: 6px 10px;
  }

  .table-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
  }

  .table-actions button {
    flex: 1 1 auto;
    font-size: 0.85rem;
  }
}

/* Small screens */
@media (max-width: 768px) {
  .search-row {
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
  }

  .search-box,
  .results-count,
  .sort-by {
    flex: 1 1 100%;
    text-align: center;
  }

  .sort-by {
    padding: 8px 12px;
  }

  .data-table-container,
  .search-container,
  .table-controls,
  .database-container {
    overflow: visible !important;
  }

  .sort-options {
    position: absolute;
    z-index: 9999;
    background: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }

  .custom-select {
    position: relative;
  }

  .custom-options {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 180px;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    z-index: 10000;
    display: none;
  }

  .custom-select.open .custom-options {
    display: block;
  }

  .custom-select.open .custom-select-trigger,
  .custom-select.open .custom-options {
    border: 2px solid #dc3545;
    box-shadow: 0 0 5px rgba(220, 53, 69, 0.5);
  }

  .actions {
    flex-direction: row;
    justify-content: space-between;
    width: 100%;
    margin-top: 10px;
    gap: 8px;
  }

  .apply-btn,
  .reset-btn {
    flex: 1 1 48%;
    padding: 8px 12px;
  }

  .table-actions {
    flex-direction: row;
    gap: 6px;
    flex-wrap: wrap;
  }

  .table-actions button {
    flex: 1 1 48%;
    font-size: 0.85rem;
    padding: 6px 8px;
  }
}

/* Extra small screens */
@media (max-width: 480px) {
  .search-row {
    flex-direction: column;
    gap: 8px;
  }

  .search-box input {
    font-size: 0.9rem;
    padding: 8px 34px 8px 12px;
  }

  .results-count,
  .sort-by {
    flex: 1 1 100%;
    text-align: center;
    width: 100%;
  }

  .sort-by {
    padding: 6px 10px;
    font-size: 0.85rem;
  }

  .actions {
    flex-direction: column;
    gap: 6px;
  }

  .apply-btn,
  .reset-btn {
    width: 100%;
    font-size: 0.85rem;
    padding: 8px 0;
  }

  .table-actions {
    flex-direction: column;
    gap: 6px;
    width: 100%;
  }

  .table-actions button {
    width: 100%;
    font-size: 0.85rem;
    padding: 8px 0;
  }
}

/* ---------- D3 NUMERIC FILTER CARD STYLES (Import Logs only) ---------- */
.numeric-filter-card {
    background: #fff;
    border: 2px solid #ccc;
    border-radius: 10px;
    padding: 12px;
    width: 100%;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.numeric-filter-title {
    font-size: 1rem;
    font-weight: bold;
    margin-bottom: 10px;
    color: #b2000c;
    display: flex;
    align-items: center;
    gap: 6px;
}

.numeric-filter-group {
    margin-bottom: 10px;
}

.numeric-filter-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #444;
}

.numeric-range {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
}

.numeric-range input {
    width: 100%;
    padding: 6px 8px;
    font-size: 0.85rem;
    border: 2px solid #ccc;
    border-radius: 6px;
}

.numeric-range input:focus {
    border-color: #b2000c;
    outline: none;
}

.numeric-range .dash {
    font-weight: bold;
    color: #555;
}
</style>

@php
    $isLogs = isset($tableId) && $tableId === 'import-logs-table';
@endphp

<!-- Integrated Search + Sort Bar -->
<div class="search-container"
     data-target-table="{{ $tableId }}">

    <div class="search-row">
        <!-- Search Bar -->
        <div class="search-box">
            <input type="text"
                   class="table-search"
                   placeholder="{{ $placeholder ?? 'Type keywords...' }}">
            <span class="icon"><i class="fas fa-search"></i></span>
        </div>

        <!-- Results Count -->
        <div class="results-count">0 Results</div>

        <!-- Sort Dropdown Toggle -->
        <div class="sort-by"
             role="button"
             tabindex="0"
             aria-expanded="false">
            <span class="label">Filter & Sort</span>
            <i class="fa-solid fa-filter filter-icon"></i>
            <span class="icon">⏷</span>
        </div>
    </div>

    <!-- SORT / FILTER AREA -->
    <div class="sort-options">

        @if(!$isLogs)
            {{-- ================= VOLUNTEERS (invalid/valid tables) ================= --}}
            <!-- Sort by Full Name -->
            <div class="custom-select" data-field="fullname">
                <div class="custom-select-trigger"
                     data-original-text="<i class='fa-solid fa-user'></i> Sort by Full Name">
                    <i class="fa-solid fa-user"></i> Sort by Full Name
                </div>

                <div class="custom-options">
                    <span class="custom-option" data-value="remove">
                        <i class="fa-solid fa-ban"></i> Remove Sort
                    </span>
                    <span class="custom-option" data-value="name-az">
                        <i class="fa-solid fa-arrow-down-a-z"></i> A → Z
                    </span>
                    <span class="custom-option" data-value="name-za">
                        <i class="fa-solid fa-arrow-down-z-a"></i> Z → A
                    </span>
                </div>
            </div>

            <!-- Sort by ID Number -->
            <div class="custom-select" data-field="idnum">
                <div class="custom-select-trigger"
                     data-original-text="<i class='fa-solid fa-id-card'></i> Sort by ID #">
                    <i class="fa-solid fa-id-card"></i> Sort by ID #
                </div>

                <div class="custom-options">
                    <span class="custom-option" data-value="remove">
                        <i class="fa-solid fa-ban"></i> Remove Sort
                    </span>
                    <span class="custom-option" data-value="id-asc">
                        <i class="fa-solid fa-arrow-up-1-9"></i> Lowest → Highest
                    </span>
                    <span class="custom-option" data-value="id-desc">
                        <i class="fa-solid fa-arrow-down-9-1"></i> Highest → Lowest
                    </span>
                </div>
            </div>

            <!-- Filter by Course -->
            <div class="custom-select" data-field="course">
                <div class="custom-select-trigger"
                     data-original-text="<i class='fa-solid fa-graduation-cap'></i> Filter by Course">
                    <i class="fa-solid fa-graduation-cap"></i> Filter by Course
                </div>

                <div class="custom-options">
                    <span class="custom-option" data-value="remove">
                        <i class="fa-solid fa-ban"></i> Remove Filter
                    </span>

                    @foreach ($courses as $course)
                        <span class="custom-option" data-value="{{ $course->course_name }}">
                            <i class="fa-solid fa-graduation-cap"></i> {{ $course->course_name }}
                        </span>
                    @endforeach
                </div>
            </div>

            <!-- Filter by Year -->
            <div class="custom-select" data-field="year">
                <div class="custom-select-trigger"
                     data-original-text="<i class='fa-solid fa-layer-group'></i> Filter by Year Level">
                    <i class="fa-solid fa-layer-group"></i> Filter by Year Level
                </div>

                <div class="custom-options">
                    <span class="custom-option" data-value="remove">
                        <i class="fa-solid fa-ban"></i> Remove Filter
                    </span>

                    @foreach ([1,2,3,4] as $year)
                        <span class="custom-option" data-value="{{ $year }}">
                            <i class="fa-solid fa-layer-group"></i>
                            {{ $year }}{{ ['st','nd','rd','th'][$year-1] }} Year
                        </span>
                    @endforeach
                </div>
            </div>

            <!-- Filter by Barangay -->
            <div class="custom-select" data-field="barangay">
                <div class="custom-select-trigger"
                     data-original-text="<i class='fa-solid fa-house'></i> Filter by Barangay">
                    <i class="fa-solid fa-house"></i> Filter by Barangay
                </div>

                <div class="custom-options">
                    <span class="custom-option" data-value="remove">
                        <i class="fa-solid fa-ban"></i> Remove Filter
                    </span>

                    @foreach ($barangays as $loc)
                        <span class="custom-option" data-value="{{ $loc->barangay }}">
                            <i class="fa-solid fa-location-dot"></i> {{ $loc->barangay }}
                        </span>
                    @endforeach
                </div>
            </div>

            <!-- Filter by District -->
            <div class="custom-select" data-field="district">
                <div class="custom-select-trigger"
                     data-original-text="<i class='fa-solid fa-map-location-dot'></i> Filter by District">
                    <i class="fa-solid fa-map-location-dot"></i> Filter by District
                </div>

                <div class="custom-options">
                    <span class="custom-option" data-value="remove">
                        <i class="fa-solid fa-ban"></i> Remove Filter
                    </span>

                    @foreach ($districts as $dist)
                        <span class="custom-option" data-value="District {{ $dist->district_id }}">
                            <i class="fa-solid fa-location-dot"></i> District {{ $dist->district_id }}
                        </span>
                    @endforeach
                </div>
            </div>

        @else
            {{-- ================= IMPORT LOGS TABLE ================= --}}
            <!-- Sort by File Name -->
            <div class="custom-select" data-field="filename">
                <div class="custom-select-trigger"
                     data-original-text="<i class='fa-solid fa-file'></i> Sort by File Name">
                    <i class="fa-solid fa-file"></i> Sort by File Name
                </div>

                <div class="custom-options">
                    <span class="custom-option" data-value="remove">
                        <i class="fa-solid fa-ban"></i> Remove Sort
                    </span>
                    <span class="custom-option" data-value="filename-az">
                        <i class="fa-solid fa-arrow-down-a-z"></i> A → Z
                    </span>
                    <span class="custom-option" data-value="filename-za">
                        <i class="fa-solid fa-arrow-down-z-a"></i> Z → A
                    </span>
                </div>
            </div>

            <!-- Sort by Uploaded By -->
            <div class="custom-select" data-field="uploaded_by">
                <div class="custom-select-trigger"
                     data-original-text="<i class='fa-solid fa-user'></i> Sort by Uploaded By">
                    <i class="fa-solid fa-user"></i> Sort by Uploaded By
                </div>

                <div class="custom-options">
                    <span class="custom-option" data-value="remove">
                        <i class="fa-solid fa-ban"></i> Remove Sort
                    </span>
                    <span class="custom-option" data-value="uploaded_by-az">
                        <i class="fa-solid fa-arrow-down-a-z"></i> A → Z
                    </span>
                    <span class="custom-option" data-value="uploaded_by-za">
                        <i class="fa-solid fa-arrow-down-z-a"></i> Z → A
                    </span>
                </div>
            </div>

            <!-- Sort by Uploaded At -->
            <div class="custom-select" data-field="uploaded_at">
                <div class="custom-select-trigger"
                     data-original-text="<i class='fa-solid fa-clock'></i> Sort by Date">
                    <i class="fa-solid fa-clock"></i> Sort by Date
                </div>

                <div class="custom-options">
                    <span class="custom-option" data-value="remove">
                        <i class="fa-solid fa-ban"></i> Remove Sort
                    </span>
                    <span class="custom-option" data-value="date-asc">
                        <i class="fa-solid fa-arrow-up-1-9"></i> Oldest → Newest
                    </span>
                    <span class="custom-option" data-value="date-desc">
                        <i class="fa-solid fa-arrow-down-9-1"></i> Newest → Oldest
                    </span>
                </div>
            </div>

            <!-- Filter by Status -->
            <div class="custom-select" data-field="status">
              <div class="custom-select-trigger"
                  data-original-text="<i class='fa-solid fa-traffic-light'></i> Filter by Status">
                  <i class="fa-solid fa-traffic-light"></i> Filter by Status
              </div>

              <div class="custom-options">
                  <span class="custom-option" data-value="remove">
                      <i class="fa-solid fa-ban"></i> Remove Filter
                  </span>
                  <span class="custom-option" data-value="pending">
                      <i class="fa-solid fa-circle"></i> Pending
                  </span>
                  <span class="custom-option" data-value="completed">
                      <i class="fa-solid fa-circle-check"></i> Completed
                  </span>
                  <span class="custom-option" data-value="cancelled">
                      <i class="fa-solid fa-circle-xmark"></i> Cancelled
                  </span>
                  <span class="custom-option" data-value="reset">
                      <i class="fa-solid fa-arrow-rotate-left"></i> Reset
                  </span>
                  <span class="custom-option" data-value="failed">
                      <i class="fa-solid fa-triangle-exclamation"></i> Failed
                  </span>
                  <span class="custom-option" data-value="abandoned">
                      <i class="fa-solid fa-person-walking-arrow-right"></i> Abandoned
                  </span>
              </div>
          </div>


          
        @endif

        <!-- Buttons -->
        <div class="actions">
            <div class="left-actions">
                <span class="results-inline" style="display:none;">0 Results</span>
            </div>

            <div class="right-actions">
                <button type="button" class="reset-btn">Reset</button>
                <button type="button" class="apply-btn">Apply Sort</button>
            </div>
        </div>

    </div>
</div>
<script>
(function () {

    function universalSearchEngine(container) {

        const tableId = container.dataset.targetTable;
        const table = document.getElementById(tableId);
        if (!table) return;

        const tbody = table.querySelector("tbody");
        const allRows = Array.from(tbody.querySelectorAll("tr:not(.no-search-results)"));
        const noResultsRow = tbody.querySelector(".no-search-results");

        const searchInput   = container.querySelector(".table-search");
        const resultsCount  = container.querySelector(".results-count");
        const sortBtn       = container.querySelector(".sort-by");
        const sortPanel     = container.querySelector(".sort-options");
        const customSelects = container.querySelectorAll(".custom-select");

        // Map fields from dropdowns → column indexes per table
        const FIELD_TO_COL =
            tableId === "import-logs-table"
                ? {
                    filename:      2,
                    uploaded_by:   3,
                    uploaded_at:   4,
                    total_records: 5,
                    valid_count:   6,
                    invalid_count: 7,
                    duplicate_count: 8,
                    status:        9   // <- status column
                  }
                : {
                    fullname:  2,
                    idnum:     3,
                    course:    4,
                    year:      5,
                    contact:   6,
                    email:     7,
                    emergency: 8,
                    fb:        9,
                    barangay:  10,
                    district:  11,
                    schedule:  12
                  };

        // Current filters/sort
        let activeFilters = {};  // per column index
        let activeSort = null;   // { colIndex, direction, type }

        // -------- helpers --------

        function getCellValue(cell, type) {
            if (!cell) {
                return type === "number" || type === "date" ? 0 : "";
            }

            // Prefer data-value, fall back to text
            let raw = cell.dataset.value !== undefined ? cell.dataset.value : cell.innerText;
            raw = (raw || "").trim();

            if (type === "number") {
                const n = parseFloat(raw.replace(/[^0-9.-]/g, ""));
                return isNaN(n) ? 0 : n;
            }

            if (type === "date") {
                const d = new Date(raw);
                return isNaN(d.getTime()) ? 0 : d.getTime();
            }

            return raw.toLowerCase();
        }

        function detectType(colIndex) {
            if (tableId === "import-logs-table") {
                if ([1,5,6,7,8].includes(colIndex)) return "number"; // #, Total, Valid, Invalid, Duplicate
                if (colIndex === 4) return "date";                   // Uploaded At
                return "string";
            }

            // Valid / Invalid tables
            if ([1,3,5].includes(colIndex)) return "number"; // #, School ID, Year
            return "string";
        }

        // ------------- core engine -------------

        function applySearchAndFilterAndSort() {
            let visibleRows = allRows.slice();

            // SEARCH
            const query = searchInput ? searchInput.value.trim().toLowerCase() : "";
            if (query) {
                visibleRows = visibleRows.filter(row =>
                    row.innerText.toLowerCase().includes(query)
                );
            }

            // FILTERS (by column index)
            Object.keys(activeFilters).forEach(colIdxStr => {
                const colIdx = parseInt(colIdxStr, 10);
                const filterVal = activeFilters[colIdxStr];
                if (!filterVal || filterVal === "remove") return;

                visibleRows = visibleRows.filter(row => {
                    const cell = row.children[colIdx];
                    const cellVal = (cell?.dataset.value || cell?.innerText || "")
                        .trim()
                        .toLowerCase();
                    return cellVal === filterVal.toLowerCase();
                });
            });

            // SORT
            if (activeSort && activeSort.colIndex != null) {
                const { colIndex, direction, type } = activeSort;

                visibleRows.sort((a, b) => {
                    const A = getCellValue(a.children[colIndex], type);
                    const B = getCellValue(b.children[colIndex], type);

                    if (direction === "az")   return A.localeCompare(B);
                    if (direction === "za")   return B.localeCompare(A);
                    if (direction === "asc")  return A - B;
                    if (direction === "desc") return B - A;

                    return 0;
                });
            }

            // HIDE ALL
            allRows.forEach(r => r.classList.add("d-none"));

            // NO RESULTS
            if (!visibleRows.length) {
                if (noResultsRow) {
                    noResultsRow.classList.remove("d-none");
                    tbody.appendChild(noResultsRow);
                }
                if (resultsCount) resultsCount.innerText = "0 Results";
                return;
            }

            if (noResultsRow) {
                noResultsRow.classList.add("d-none");
            }

            // RE-APPEND IN SORTED ORDER + RENUMBER
            let counter = 1;
            visibleRows.forEach(row => {
                row.classList.remove("d-none");
                if (row.children[1]) {
                    row.children[1].innerText = counter++;
                }
                tbody.appendChild(row);
            });

            // Keep no-results row at the end
            if (noResultsRow) {
                tbody.appendChild(noResultsRow);
            }

            if (resultsCount) {
                resultsCount.innerText = `${visibleRows.length} Results`;
            }
        }

        // ---------- search input ----------

        if (searchInput) {
            searchInput.addEventListener("input", applySearchAndFilterAndSort);
        }

        // ---------- dropdown sort panel ----------

        if (sortBtn && sortPanel) {
            sortBtn.addEventListener("click", e => {
                e.stopPropagation();
                const wasOpen = sortPanel.classList.contains("open");

                customSelects.forEach(s => s.classList.remove("open"));

                if (wasOpen) {
                    sortPanel.classList.remove("open");
                    sortBtn.classList.remove("active");
                } else {
                    sortPanel.classList.add("open");
                    sortBtn.classList.add("active");
                }
            });
        }

        document.addEventListener("click", e => {
            const clickInside = container.contains(e.target);

            if (!clickInside) {
                if (sortPanel) sortPanel.classList.remove("open");
                if (sortBtn)   sortBtn.classList.remove("active");
                customSelects.forEach(s => s.classList.remove("open"));
                return;
            }

            if (!e.target.closest(".custom-select")) {
                customSelects.forEach(s => s.classList.remove("open"));
            }
        });

        // ---------- custom select dropdowns ----------

        customSelects.forEach(select => {
            const trigger = select.querySelector(".custom-select-trigger");
            const options = select.querySelectorAll(".custom-option");

            if (!trigger) return;

            trigger.addEventListener("click", e => {
                e.stopPropagation();

                const wasOpen = select.classList.contains("open");
                customSelects.forEach(s => s.classList.remove("open"));
                if (!wasOpen) {
                    select.classList.add("open");
                }
            });

            options.forEach(opt => {
                opt.addEventListener("click", () => {
                    trigger.innerHTML = opt.innerHTML;
                    select.dataset.selected = opt.dataset.value;   // <-- this is what we read later
                    select.classList.remove("open");
                });
            });
        });

        // ---------- APPLY from dropdowns ----------

        const applyBtn = container.querySelector(".apply-btn");
        const resetBtn = container.querySelector(".reset-btn");

        if (applyBtn) {
            applyBtn.addEventListener("click", e => {
                e.preventDefault();

                activeFilters = {};
                activeSort = null;

                customSelects.forEach(select => {
                    const selected = select.dataset.selected;
                    const field    = select.dataset.field;

                    if (!selected || selected === "remove") return;
                    if (!FIELD_TO_COL[field]) return;

                    const colIndex = FIELD_TO_COL[field];

                    // STRING SORTS (name, filename, uploaded_by) — NOT STATUS
                    if (["fullname", "filename", "uploaded_by"].includes(field)) {
                        if (selected.endsWith("-az")) {
                            activeSort = { colIndex, direction: "az", type: "string" };
                        } else if (selected.endsWith("-za")) {
                            activeSort = { colIndex, direction: "za", type: "string" };
                        }
                    }
                    // NUMBER SORT (ID, counts, totals)
                    else if (["idnum", "total_records", "valid_count", "invalid_count", "duplicate_count"]
                             .includes(field)) {
                        if (selected.endsWith("-asc")) {
                            activeSort = { colIndex, direction: "asc", type: "number" };
                        } else if (selected.endsWith("-desc")) {
                            activeSort = { colIndex, direction: "desc", type: "number" };
                        }
                    }
                    // DATE SORT
                    else if (field === "uploaded_at") {
                        if (selected === "date-asc") {
                            activeSort = { colIndex, direction: "asc", type: "date" };
                        } else if (selected === "date-desc") {
                            activeSort = { colIndex, direction: "desc", type: "date" };
                        }
                    }
                    // EVERYTHING ELSE = FILTER (Course, Year, Barangay, District, Status, etc.)
                    else {
                        activeFilters[colIndex] = selected;
                    }
                });

                applySearchAndFilterAndSort();
            });
        }

        // ---------- RESET from dropdowns ----------

        if (resetBtn) {
            resetBtn.addEventListener("click", e => {
                e.preventDefault();

                if (searchInput) searchInput.value = "";
                activeFilters = {};
                activeSort = null;

                customSelects.forEach(sel => {
                    const trigger = sel.querySelector(".custom-select-trigger");
                    if (trigger && trigger.dataset.originalText) {
                        trigger.innerHTML = trigger.dataset.originalText;
                    }
                    sel.removeAttribute("data-selected");
                });

                applySearchAndFilterAndSort();
            });
        }

        // ---------- HEADER CLICK SORT ----------

        const headerCells = table.querySelectorAll("thead th");

        headerCells.forEach((th, index) => {
            // Skip the first column (checkbox)
            if (index === 0) return;

            th.style.cursor = "pointer";

            th.addEventListener("click", function () {
                const type = detectType(index);

                // Toggle direction on repeated click
                const previousDir = th.dataset.sortDirection || "none";
                const newDir = previousDir === "asc" ? "desc" : "asc";

                // Clear sort indicators on other headers
                headerCells.forEach(h => {
                    if (h !== th) {
                        delete h.dataset.sortDirection;
                    }
                });

                th.dataset.sortDirection = newDir;

                activeSort = {
                    colIndex: index,
                    direction: newDir,
                    type
                };

                applySearchAndFilterAndSort();
            });
        });

        // init originalText for triggers, then run once
        container.querySelectorAll(".custom-select-trigger").forEach(t => {
            if (!t.dataset.originalText) {
                t.dataset.originalText = t.innerHTML;
            }
        });

        applySearchAndFilterAndSort();
    }

    function initAll() {
        document.querySelectorAll(".search-container").forEach(container => {
            universalSearchEngine(container);
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initAll);
    } else {
        initAll();
    }

})();
</script>
