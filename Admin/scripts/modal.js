// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
  // Modal elements
  const hostellerModal = document.getElementById('hosteller-modal');
  const wardenModal = document.getElementById('warden-modal');
  const addHostellerBtn = document.getElementById('add-hosteller-btn');
  const addWardenBtn = document.getElementById('add-warden-btn');
  const cancelHostellerBtn = document.getElementById('cancel-hosteller-btn');
  const cancelWardenBtn = document.getElementById('cancel-warden-btn');
  const closeModalButtons = document.querySelectorAll('.close-modal');
  
  // Modal titles
  const hostellerModalTitle = document.getElementById('hosteller-modal-title');
  const wardenModalTitle = document.getElementById('warden-modal-title');
  
  // Open hosteller modal for adding new hosteller
  if (addHostellerBtn) {
    addHostellerBtn.addEventListener('click', function() {
      // Reset form
      document.getElementById('add-hosteller-form').reset();
      document.getElementById('hosteller-id').value = '';
      hostellerModalTitle.textContent = 'Add New Hosteller';
      document.getElementById('hosteller-password').required = true;
      document.getElementById('password-info').style.display = 'none';
      
      // Show modal
      hostellerModal.style.display = 'block';
    });
  }
  
  // Open warden modal for adding new warden
  if (addWardenBtn) {
    addWardenBtn.addEventListener('click', function() {
      // Reset form
      document.getElementById('add-warden-form').reset();
      document.getElementById('warden-id').value = '';
      wardenModalTitle.textContent = 'Add New Warden';
      document.getElementById('warden-password').required = true;
      document.getElementById('warden-password-info').style.display = 'none';
      
      // Show modal
      wardenModal.style.display = 'block';
    });
  }
  
  // Close modals with Cancel button
  if (cancelHostellerBtn) {
    cancelHostellerBtn.addEventListener('click', function() {
      hostellerModal.style.display = 'none';
    });
  }
  
  if (cancelWardenBtn) {
    cancelWardenBtn.addEventListener('click', function() {
      wardenModal.style.display = 'none';
    });
  }
  
  // Close modals with X button
  closeModalButtons.forEach(button => {
    button.addEventListener('click', function() {
      hostellerModal.style.display = 'none';
      wardenModal.style.display = 'none';
    });
  });
  
  // Close modals when clicking outside
  window.addEventListener('click', function(event) {
    if (event.target === hostellerModal) {
      hostellerModal.style.display = 'none';
    }
    if (event.target === wardenModal) {
      wardenModal.style.display = 'none';
    }
  });
  
  // Edit hosteller - will be called from the main script
  window.editHosteller = function(id) {
    // Fetch hosteller data
    const formData = new FormData();
    formData.append('action', 'get_hosteller');
    formData.append('id', id);
    
    fetch('', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        const hosteller = data.data;
        
        // Fill form with hosteller data
        document.getElementById('hosteller-id').value = hosteller.id;
        document.getElementById('hosteller-name').value = hosteller.name;
        document.getElementById('hosteller-course').value = hosteller.course;
        document.getElementById('hosteller-semester').value = hosteller.semester;
        document.getElementById('hosteller-room').value = hosteller.room;
        document.getElementById('hosteller-contact').value = hosteller.phone_number;
        
        // Password field is optional for updates
        document.getElementById('hosteller-password').required = false;
        document.getElementById('hosteller-password').value = '';
        document.getElementById('password-info').style.display = 'block';
        
        // Update modal title
        hostellerModalTitle.textContent = 'Edit Hosteller';
        
        // Show modal
        hostellerModal.style.display = 'block';
      } else {
        showMessage(data.message, 'error');
      }
    })
    .catch(error => {
      showMessage('Error fetching hosteller data: ' + error, 'error');
    });
  };
  
  // Edit warden - will be called from the main script
  window.editWarden = function(id) {
    // Fetch warden data
    const formData = new FormData();
    formData.append('action', 'get_warden');
    formData.append('id', id);
    
    fetch('', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        const warden = data.data;
        
        // Fill form with warden data
        document.getElementById('warden-id').value = warden.id;
        document.getElementById('warden-name').value = warden.name;
        document.getElementById('warden-building').value = warden.building;
        document.getElementById('warden-contact').value = warden.phone_number;
        document.getElementById('warden-address').value = warden.address;
        
        // Password field is optional for updates
        document.getElementById('warden-password').required = false;
        document.getElementById('warden-password').value = '';
        document.getElementById('warden-password-info').style.display = 'block';
        
        // Update modal title
        wardenModalTitle.textContent = 'Edit Warden';
        
        // Show modal
        wardenModal.style.display = 'block';
      } else {
        showMessage(data.message, 'error');
      }
    })
    .catch(error => {
      showMessage('Error fetching warden data: ' + error, 'error');
    });
  };
});