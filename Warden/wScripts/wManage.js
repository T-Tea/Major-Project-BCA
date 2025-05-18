document.addEventListener('DOMContentLoaded', function() {
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
  
        // Load hostellers without filters
        loadHostellers(1);
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
  
    if (semester) formData.append('semester', semester);
    if (course) formData.append('course', course);
    if (room) formData.append('room', room);
    if (name) formData.append('name', name);
  
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
  
  // Function to render hosteller table
  function renderHostellerTable(hostellers) {
    const tableBody = document.getElementById('hosteller-table-body');
    tableBody.innerHTML = '';
  
    if (hostellers.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="6" class="no-results">No hostellers found</td>
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
      `;
      tableBody.appendChild(row);
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
  
    const paginationNav = document.createElement('div');
    paginationNav.className = 'pagination-nav';
  
    const prevBtn = document.createElement('button');
    prevBtn.className = 'pagination-btn';
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Previous';
    prevBtn.disabled = pagination.current_page === 1;
    prevBtn.addEventListener('click', () => loadFunction(pagination.current_page - 1));
    paginationNav.appendChild(prevBtn);
  
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
  
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
  
    for (let i = startPage; i <= endPage; i++) {
      const pageBtn = document.createElement('button');
      pageBtn.className = `pagination-btn ${i === pagination.current_page ? 'active' : ''}`;
      pageBtn.textContent = i;
      pageBtn.addEventListener('click', () => loadFunction(i));
      paginationNav.appendChild(pageBtn);
    }
  
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
  
    const nextBtn = document.createElement('button');
    nextBtn.className = 'pagination-btn';
    nextBtn.innerHTML = 'Next <i class="fas fa-chevron-right"></i>';
    nextBtn.disabled = pagination.current_page === pagination.total_pages;
    nextBtn.addEventListener('click', () => loadFunction(pagination.current_page + 1));
    paginationNav.appendChild(nextBtn);
  
    container.appendChild(paginationNav);
  }
  