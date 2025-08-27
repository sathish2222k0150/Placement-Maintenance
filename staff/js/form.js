
const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
const Default = {
  scrollbarTheme: 'os-theme-light',
  scrollbarAutoHide: 'leave',
  scrollbarClickScroll: true,
};
document.addEventListener('DOMContentLoaded', function () {
  const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
  if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
    OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
      scrollbars: {
        theme: Default.scrollbarTheme,
        autoHide: Default.scrollbarAutoHide,
        clickScroll: Default.scrollbarClickScroll,
      },
    });
  }

  // Generic function to check unique fields
  function checkUniqueField(field, value, errorElement) {
    if (!value) {
      errorElement.style.display = 'none';
      return;
    }

    fetch(`?check_unique=1&field=${field}&value=${encodeURIComponent(value)}&edit_id=<?= $edit_id ?>`)
      .then(response => response.json())
      .then(data => {
        if (data.exists) {
          errorElement.style.display = 'block';
        } else {
          errorElement.style.display = 'none';
        }
      })
      .catch(error => {
        console.error(`Error checking ${field}:`, error);
      });
  }

  // Real-time validation for Registration Number
  const regNoInput = document.querySelector('.regno-input');
  if (regNoInput) {
    let regNoCheckTimeout;

    regNoInput.addEventListener('input', function () {
      const value = this.value.trim();
      const errorElement = document.querySelector('.regno-error');
      const existsError = document.querySelector('.regno-exists-error');

      // Clear previous timeout if any
      clearTimeout(regNoCheckTimeout);

      // Validate required field
      if (!value) {
        errorElement.style.display = 'block';
        existsError.style.display = 'none';
        return;
      } else {
        errorElement.style.display = 'none';
      }

      // Debounce the API call
      regNoCheckTimeout = setTimeout(() => {
        checkUniqueField('reg_no', value, existsError);
      }, 500); // 500ms delay after typing stops
    });
  }

  // Real-time validation for Aadhar
  const aadharInput = document.querySelector('.aadhar-input');
  if (aadharInput) {
    let aadharCheckTimeout;

    aadharInput.addEventListener('input', function () {
      const value = this.value.trim();
      const lengthError = document.querySelector('.aadhar-error');
      const existsError = document.querySelector('.aadhar-exists-error');

      // Clear previous timeout if any
      clearTimeout(aadharCheckTimeout);

      // Validate length
      if (value.length !== 12 && value.length > 0) {
        lengthError.style.display = 'block';
        existsError.style.display = 'none';
        return;
      } else {
        lengthError.style.display = 'none';
      }

      // Only check if we have exactly 12 digits
      if (value.length === 12) {
        // Debounce the API call
        aadharCheckTimeout = setTimeout(() => {
          checkUniqueField('aadhar', value, existsError);
        }, 500); // 500ms delay after typing stops
      } else {
        existsError.style.display = 'none';
      }
    });
  }

  // Real-time validation for Contact
  const contactInput = document.querySelector('.contact-input');
  if (contactInput) {
    contactInput.addEventListener('input', function () {
      const value = this.value.trim();
      const errorElement = document.querySelector('.contact-error');
      if (value.length !== 10 && value.length > 0) {
        errorElement.style.display = 'block';
      } else {
        errorElement.style.display = 'none';
      }
    });
  }

  // Real-time validation for Alt Contact
  const altContactInput = document.querySelector('.alt-contact-input');
  if (altContactInput) {
    altContactInput.addEventListener('input', function () {
      const value = this.value.trim();
      const errorElement = document.querySelector('.alt-contact-error');
      if (value.length !== 10 && value.length > 0) {
        errorElement.style.display = 'block';
      } else {
        errorElement.style.display = 'none';
      }
    });
  }
});

async function openPreview() {
  const data = window.studentFormData || {};
  let html = '<div class="table-responsive"><table class="table table-bordered table-striped">';
  const manualQualifyKeys = ['ITI', 'Diploma', 'Graduate', 'Post Graduate', 'B.E/B.Tech', 'Others'];

  const qualificationValue = data['qualification'] || '';
  const qualificationDetail = data['qualification_detail'] || '';
  const casteValue = data['caste'] || '';
  const casteDetail = data['caste_detail'] || '';

  // 🔄 Step 1: Fetch project name if missing
  if (!data['project_name'] && data['project_id']) {
    try {
      const projectRes = await fetch(`get-project-name.php?id=${data['project_id']}`);
      const project = await projectRes.json();
      data['project_name'] = project.name || '';
    } catch (err) {
      console.error("Failed to fetch project name:", err);
      data['project_name'] = '';
    }
  }

  // 🔄 Step 2: Fetch batch code if missing
  if (!data['batch_code'] && data['batch_id']) {
    try {
      const batchRes = await fetch(`./get-batch-name.php?id=${data['batch_id']}`);
      const batch = await batchRes.json();
      data['batch_code'] = batch.code || '';
    } catch (err) {
      console.error("Failed to fetch batch name:", err);
      data['batch_code'] = '';
    }
  }

  // ✅ Project display
  if (data['project_name']) {
    html += `<tr><th style="width:30%">Project</th><td>${data['project_name']}</td></tr>`;
  }

  // ✅ Batch display
  if (data['batch_code']) {
    html += `<tr><th style="width:30%">Centre & Batch No</th><td>${data['batch_code']}</td></tr>`;
  }

  // ✅ Rest of the fields
  for (let k in data) {
    if (!Object.prototype.hasOwnProperty.call(data, k)) continue;

    if (['qualification_detail', 'caste_detail', 'save_draft', 'project_id', 'batch_id', 'project_name', 'batch_code'].includes(k)) continue;

    let label = k.replace(/_/g, ' ').replace(/\b\w/g, s => s.toUpperCase());
    let val = (data[k] ?? '').toString();

    if (k === 'qualification' && manualQualifyKeys.includes(qualificationValue) && qualificationDetail.trim() !== '') {
      val = qualificationValue + ' - ' + qualificationDetail;
    }

    if (k === 'caste' && casteValue === 'Others' && casteDetail.trim() !== '') {
      val = 'Others - ' + casteDetail;
    }

    html += `<tr><th style="width:30%">${label}</th><td>${val}</td></tr>`;
  }

  if ('save_draft' in data) {
    html += `<tr><th style="width:30%">Save Draft</th><td>Yes</td></tr>`;
  }

  html += '</table></div>';
  document.getElementById('previewContent').innerHTML = html;
  new bootstrap.Modal(document.getElementById('previewModal')).show();
}


document.getElementById('regForm').addEventListener('submit', function (e) {
  let form = e.target;
  let valid = true;
  let msg = '';

  // Only validate fields visible in the current step
  const visibleStep = form.querySelectorAll('.row:has([name])'); // only validate visible groups
  const currentFields = visibleStep[visibleStep.length - 1].querySelectorAll('[required]');

  currentFields.forEach(input => {
    const value = input.value.trim();
    const name = input.name.replace(/_/g, ' ');
    if (!value) {
      valid = false;
      msg += `${name} is required.\n`;
    }

    // Custom validations
    if (input.name === 'aadhar' && value && !/^\d{12}$/.test(value)) {
      valid = false;
      msg += 'Aadhar must be exactly 12 digits.\n';
    }
    if (input.name === 'contact' && value && !/^\d{10}$/.test(value)) {
      valid = false;
      msg += 'Contact must be exactly 10 digits.\n';
    }
    if (input.name === 'alt_contact' && value && !/^\d{10}$/.test(value)) {
      valid = false;
      msg += 'Alternate contact must be exactly 10 digits.\n';
    }
  });

  // Check for existing registration number
  const regNoExistsError = document.querySelector('.regno-exists-error');
  if (regNoExistsError && regNoExistsError.style.display === 'block') {
    valid = false;
    msg += 'This registration number is already registered. Please use a different one.\n';
  }

  // Check for existing Aadhar
  const aadharExistsError = document.querySelector('.aadhar-exists-error');
  if (aadharExistsError && aadharExistsError.style.display === 'block') {
    valid = false;
    msg += 'This Aadhar number is already registered. Please use a different Aadhar.\n';
  }

  if (!valid) {
    e.preventDefault();
    alert(msg);
  }
});


document.addEventListener('DOMContentLoaded', function () {
  const projectDropdown = document.getElementById('projectDropdown');
  const batchDropdown = document.getElementById('batchDropdown');
  const batchStartDate = document.getElementById('batchStartDate');
  const editId = document.getElementById('regForm')?.dataset?.editId || 0;

  // Initialize form data from window object or empty object
  window.studentFormData = window.studentFormData || {};
  
  // Debugging: Log initial form data
  console.log('Initial form data:', window.studentFormData);

  // ✅ Restore project selection if previously saved
  if (studentFormData['project_id']) {
    projectDropdown.value = studentFormData['project_id'];
    console.log('Restored project selection:', studentFormData['project_id']);
  }

  // ✅ Function to load batches for a project
  const loadBatches = (projectId, selectedBatchId = null) => {
    if (!projectId) {
      batchDropdown.innerHTML = '<option value="">Select Batch</option>';
      batchStartDate.value = '';
      return;
    }

    batchDropdown.innerHTML = '<option value="">Loading...</option>';
    
    fetch(`./get-batches.php?project_id=${projectId}`)
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(data => {
        let options = '<option value="">Select Batch</option>';
        data.forEach(batch => {
          const isSelected = selectedBatchId ? (batch.id == selectedBatchId) : (studentFormData['batch_id'] == batch.id);
          options += `<option value="${batch.id}" 
                      data-date="${batch.start_date}" 
                      ${isSelected ? 'selected' : ''}>
                      ${batch.code}
                    </option>`;
        });
        batchDropdown.innerHTML = options;

        // If we have a selected batch, update the start date
        const selectedOption = batchDropdown.options[batchDropdown.selectedIndex];
        if (selectedOption && selectedOption.value) {
          batchStartDate.value = selectedOption.getAttribute('data-date') || '';
          console.log('Restored batch selection:', selectedOption.value);
        }
      })
      .catch(error => {
        console.error('Failed to load batches:', error);
        batchDropdown.innerHTML = '<option value="">Error loading batches</option>';
      });
  };

  // ✅ Initial batch load if project is selected
  if (studentFormData['project_id']) {
    console.log('Loading batches for initial project:', studentFormData['project_id']);
    loadBatches(studentFormData['project_id'], studentFormData['batch_id']);
  }

  // ✅ On project change
  projectDropdown.addEventListener('change', function () {
    const projectId = this.value;
    const projectName = this.options[this.selectedIndex]?.text || '';

    // Update form data
    studentFormData['project_id'] = projectId;
    studentFormData['project_name'] = projectName;
    studentFormData['batch_id'] = '';
    studentFormData['batch_code'] = '';
    studentFormData['batch_start'] = '';

    console.log('Project changed to:', projectId);
    loadBatches(projectId);
  });

  // ✅ On batch change
  batchDropdown.addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    const batchId = selected.value;
    const batchCode = selected.text;
    const startDate = selected.getAttribute('data-date') || '';

    batchStartDate.value = startDate;

    // Update form data
    studentFormData['batch_id'] = batchId;
    studentFormData['batch_code'] = batchCode;
    studentFormData['batch_start'] = startDate;

    console.log('Batch changed to:', batchId, 'Start date:', startDate);
  });

  // ✅ If we're editing and no data is loaded, try to force reload
  if (editId > 0 && (!studentFormData || Object.keys(studentFormData).length === 0)) {
    console.warn('Editing but no form data found - attempting to reload');
    // You might want to trigger a data reload here if needed
    // This would depend on your application architecture
  }
});


