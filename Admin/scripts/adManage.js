// Hosteller & Warden Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Section toggle functionality
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    const contentSections = document.querySelectorAll('.content-section');
    
    toggleButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all buttons
        toggleButtons.forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Hide all content sections
        contentSections.forEach(section => section.classList.remove('active'));
        
        // Show target content section
        const targetSection = document.getElementById(this.dataset.target);
        if (targetSection) {
          targetSection.classList.add('active');
          
          // Load data for the active section
          if (this.dataset.target === 'hosteller-section') {
            loadHostellers();
          } else if (this.dataset.target === 'warden-section') {
            loadWardens();
          }
        }
      });
    });
    
    // Filter panel toggle
    const toggleFiltersBtn = document.getElementById('toggle-filters');
    const filterContent = document.querySelector('.filter-content');
    const filterActions = document.querySelector('.filter-actions');
    
    if (toggleFiltersBtn) {
      toggleFiltersBtn.addEventListener('click', function() {
        filterContent.classList.toggle('hidden');
        filterActions.classList.toggle('hidden');
        
        // Toggle icon
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-chevron-up');
        icon.classList.toggle('fa-chevron-down');
      });
    }
    
    // Filter functionality
    const applyFiltersBtn = document.getElementById('apply-filters');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    if (applyFiltersBtn) {
      applyFiltersBtn.addEventListener('click', function() {
        loadHostellers(1); // Load with page 1
      });
    }
    
    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', function() {
        // Clear all filter inputs
        document.getElementById('semester-filter').value = '';
        document.getElementById('course-filter').value = '';
        document.getElementById('room-search').value = '';
        document.getElementById('name-search').value = '';
        //document.getElementById('address-search').value = '';
        
        // Load hostellers without filters
        loadHostellers(1);
      });
    }
    
    // Warden search functionality
    const searchWardenBtn = document.getElementById('search-warden-btn');
    const clearWardenSearchBtn = document.getElementById('clear-warden-search');
    
    if (searchWardenBtn) {
      searchWardenBtn.addEventListener('click', function() {
        loadWardens(1);
      });
    }
    
    if (clearWardenSearchBtn) {
      clearWardenSearchBtn.addEventListener('click', function() {
        document.getElementById('warden-name-search').value = '';
        loadWardens(1);
      });
    }
    
    // Form submissions
    const addHostellerForm = document.getElementById('add-hosteller-form');
    const addWardenForm = document.getElementById('add-warden-form');
    
    if (addHostellerForm) {
      addHostellerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const hostellerId = document.getElementById('hosteller-id').value;
        const action = hostellerId ? 'update_hosteller' : 'add_hosteller';
        
        const formData = new FormData(this);
        formData.append('action', action);
        
        fetch('', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            document.getElementById('hosteller-modal').style.display = 'none';
            showMessage(data.message, 'success');
            loadHostellers(); // Reload hostellers
          } else {
            showMessage(data.message, 'error');
          }
        })
        .catch(error => {
          showMessage('Error: ' + error, 'error');
        });
      });
    }
    
    if (addWardenForm) {
      addWardenForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const wardenId = document.getElementById('warden-id').value;
        const action = wardenId ? 'update_warden' : 'add_warden';
        
        const formData = new FormData(this);
        formData.append('action', action);
        
        fetch('', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            document.getElementById('warden-modal').style.display = 'none';
            showMessage(data.message, 'success');
            loadWardens(); // Reload wardens
          } else {
            showMessage(data.message, 'error');
          }
        })
        .catch(error => {
          showMessage('Error: ' + error, 'error');
        });
      });
    }
    
    // Load hostellers on initial page load
    loadHostellers();
    
    // Initial toggle state
    filterContent.classList.remove('hidden');
    filterActions.classList.remove('hidden');
  });
  
  // Function to show message
  function showMessage(message, type) {
    const messageContainer = document.getElementById('message-container');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    
    messageContainer.innerHTML = `
      <div class="alert ${alertClass}">
        ${message}
      </div>
    `;
    
    // Auto-hide message after 5 seconds
    setTimeout(() => {
      messageContainer.innerHTML = '';
    }, 5000);
  }
  
  // Function to load hostellers with filters and pagination
  function loadHostellers(page = 1) {
    const formData = new FormData();
    formData.append('action', 'get_hostellers');
    formData.append('page', page);
    
    // Add filters if they have values
    const semester = document.getElementById('semester-filter').value;
    const course = document.getElementById('course-filter').value;
    const room = document.getElementById('room-search').value;
    const name = document.getElementById('name-search').value;
    //const address = document.getElementById('address-search').value;
    
    if (semester) formData.append('semester', semester);
    if (course) formData.append('course', course);
    if (room) formData.append('room', room);
    if (name) formData.append('name', name);
    //if (address) formData.append('address', address);
    
    fetch('', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        renderHostellerTable(data.data);
        renderPagination(data.pagination, 'hosteller-pagination', loadHostellers);
        document.getElementById('results-count').textContent = data.pagination.total_items;
      } else {
        showMessage(data.message, 'error');
      }
    })
    .catch(error => {
      showMessage('Error loading hostellers: ' + error, 'error');
    });
  }
  
  // Function to load wardens with filters and pagination
  function loadWardens(page = 1) {
    const formData = new FormData();
    formData.append('action', 'get_wardens');
    formData.append('page', page);
    
    // Add filter if it has a value
    const nameSearch = document.getElementById('warden-name-search').value;
    if (nameSearch) formData.append('name', nameSearch);
    
    fetch('', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        renderWardenTable(data.data);
        renderPagination(data.pagination, 'warden-pagination', loadWardens);
        document.getElementById('warden-results-count').textContent = data.pagination.total_items;
      } else {
        showMessage(data.message, 'error');
      }
    })
    .catch(error => {
      showMessage('Error loading wardens: ' + error, 'error');
    });
  }
  
  // Function to render hosteller table
  function renderHostellerTable(hostellers) {
    const tableBody = document.getElementById('hosteller-table-body');
    tableBody.innerHTML = '';
    
    if (hostellers.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="7" class="no-results">No hostellers found</td>
        </tr>
      `;
      return;
    }
    
    hostellers.forEach(hosteller => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${hosteller.id}</td>
        <td>${hosteller.name}</td>
        <td>${hosteller.room}</td>
        <td>${hosteller.course}</td>
        <td>${hosteller.semester}</td>
        <td>${hosteller.phone_number}</td>
        <td>
          <button class="action-btn edit-btn" data-id="${hosteller.id}">
            <i class="fas fa-edit"></i> Edit
          </button>
          <button class="action-btn delete-btn" data-id="${hosteller.id}">
            <i class="fas fa-trash"></i> Delete
          </button>
        </td>
      `;
      
      tableBody.appendChild(row);
    });
    
    // Add event listeners to buttons
    addActionButtonEventListeners('hosteller');
  }
  
  // Function to render warden table
  function renderWardenTable(wardens) {
    const tableBody = document.getElementById('warden-table-body');
    tableBody.innerHTML = '';
    
    if (wardens.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="6" class="no-results">No wardens found</td>
        </tr>
      `;
      return;
    }
    
    wardens.forEach(warden => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${warden.id}</td>
        <td>${warden.name}</td>
        <td>${warden.building}</td>
        <td>${warden.phone_number}</td>
        <td><span class="status-badge ${warden.status.toLowerCase()}">${warden.status}</span></td>
        <td>
          <button class="action-btn edit-btn" data-id="${warden.id}">
            <i class="fas fa-edit"></i> Edit
          </button>
          <button class="action-btn delete-btn" data-id="${warden.id}">
            <i class="fas fa-trash"></i> Delete
          </button>
        </td>
      `;
      
      tableBody.appendChild(row);
    });
    
    // Add event listeners to buttons
    addActionButtonEventListeners('warden');
  }
  
  // Function to add event listeners to action buttons
  function addActionButtonEventListeners(type) {
    // Edit buttons
    const editButtons = document.querySelectorAll(`#${type}-table-body .edit-btn`);
    editButtons.forEach(button => {
      button.addEventListener('click', function() {
        const id = this.dataset.id;
        if (type === 'hosteller') {
          window.editHosteller(id);
        } else {
          window.editWarden(id);
        }
      });
    });
    
    // Delete buttons
    const deleteButtons = document.querySelectorAll(`#${type}-table-body .delete-btn`);
    deleteButtons.forEach(button => {
      button.addEventListener('click', function() {
        const id = this.dataset.id;
        if (confirm(`Are you sure you want to delete this ${type}?`)) {
          deleteRecord(type, id);
        }
      });
    });
  }
  
  // Function to delete a record
  function deleteRecord(type, id) {
    const formData = new FormData();
    formData.append('action', `delete_${type}`);
    formData.append('id', id);
    
    fetch('', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        showMessage(data.message, 'success');
        if (type === 'hosteller') {
          loadHostellers();
        } else {
          loadWardens();
        }
      } else {
        showMessage(data.message, 'error');
      }
    })
    .catch(error => {
      showMessage(`Error deleting ${type}: ` + error, 'error');
    });
  }
  
  // Function to render pagination
  function renderPagination(pagination, containerId, loadFunction) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = '';
    
    if (pagination.total_pages <= 1) {
      return;
    }
    
    // Create pagination controls
    const paginationNav = document.createElement('div');
    paginationNav.className = 'pagination-nav';
    
    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.className = 'pagination-btn';
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Previous';
    prevBtn.disabled = pagination.current_page === 1;
    prevBtn.addEventListener('click', () => loadFunction(pagination.current_page - 1));
    paginationNav.appendChild(prevBtn);
    
    // Page buttons
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    // First page
    if (startPage > 1) {
      const firstBtn = document.createElement('button');
      firstBtn.className = 'pagination-btn';
      firstBtn.textContent = '1';
      firstBtn.addEventListener('click', () => loadFunction(1));
      paginationNav.appendChild(firstBtn);
      
      if (startPage > 2) {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'pagination-ellipsis';
        ellipsis.textContent = '...';
        paginationNav.appendChild(ellipsis);
      }
    }
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
      const pageBtn = document.createElement('button');
      pageBtn.className = `pagination-btn ${i === pagination.current_page ? 'active' : ''}`;
      pageBtn.textContent = i;
      pageBtn.addEventListener('click', () => loadFunction(i));
      paginationNav.appendChild(pageBtn);
    }
    
    // Last page
    if (endPage < pagination.total_pages) {
      if (endPage < pagination.total_pages - 1) {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'pagination-ellipsis';
        ellipsis.textContent = '...';
        paginationNav.appendChild(ellipsis);
      }
      
      const lastBtn = document.createElement('button');
      lastBtn.className = 'pagination-btn';
      lastBtn.textContent = pagination.total_pages;
      lastBtn.addEventListener('click', () => loadFunction(pagination.total_pages));
      paginationNav.appendChild(lastBtn);
    }
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = 'pagination-btn';
    nextBtn.innerHTML = 'Next <i class="fas fa-chevron-right"></i>';
    nextBtn.disabled = pagination.current_page === pagination.total_pages;
    nextBtn.addEventListener('click', () => loadFunction(pagination.current_page + 1));
    paginationNav.appendChild(nextBtn);
    
    container.appendChild(paginationNav);
  }