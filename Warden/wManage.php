<?php
// Start session for authentication
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root"; // Change as per your database credentials
$password = ""; // Change as per your database credentials
$dbname = "hostel"; // Change as per your database name

// Create connection
$conn = null;

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_hosteller':
            add_hosteller();
            break;
        case 'update_hosteller':
            update_hosteller();
            break;
        case 'delete_hosteller':
            delete_hosteller();
            break;
        case 'get_hosteller':
            get_hosteller();
            break;
        case 'get_hostellers':
            get_hostellers();
            break;
        case 'add_warden':
            add_warden();
            break;
        case 'update_warden':
            update_warden();
            break;
        case 'delete_warden':
            delete_warden();
            break;
        case 'get_warden':
            get_warden();
            break;
        case 'get_wardens':
            get_wardens();
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
    
    exit;
}




// Function to get single hosteller data
function get_hosteller() {
    global $conn;
    
    try {
        $id = sanitize_input($_POST['id']);
        
        $stmt = $conn->prepare("SELECT id, name, course, semester, phone_number, room, building FROM hostellers WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            throw new Exception("Hosteller not found");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Function to get hostellers with filters
function get_hostellers() {
    global $conn;
    
    try {
        // Initialize where clauses
        $where_clauses = [];
        $params = [];
        $types = "";
        
        // Get filter parameters
        $semester = isset($_POST['semester']) ? sanitize_input($_POST['semester']) : '';
        $course = isset($_POST['course']) ? sanitize_input($_POST['course']) : '';
        $room = isset($_POST['room']) ? sanitize_input($_POST['room']) : '';
        $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
        $address = isset($_POST['address']) ? sanitize_input($_POST['address']) : '';
        
        // Build where clause based on filters
        if (!empty($semester)) {
            $where_clauses[] = "semester = ?";
            $params[] = $semester;
            $types .= "s";
        }
        
        if (!empty($course)) {
            $where_clauses[] = "course = ?";
            $params[] = $course;
            $types .= "s";
        }
        
        if (!empty($room)) {
            $where_clauses[] = "room LIKE ?";
            $params[] = "%$room%";
            $types .= "s";
        }
        
        if (!empty($name)) {
            $where_clauses[] = "name LIKE ?";
            $params[] = "%$name%";
            $types .= "s";
        }
        
        // Pagination
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;
        
        // Prepare query
        $query = "SELECT id, name, course, semester, phone_number, room, building FROM hostellers";
        
        if (count($where_clauses) > 0) {
            $query .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        // Add limit for pagination
        $query .= " LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $items_per_page;
        $types .= "ii";
        
        // Get total count for pagination
        $count_query = "SELECT COUNT(*) as total FROM hostellers";
        
        if (count($where_clauses) > 0) {
            $count_query .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        // Execute count query
        $count_stmt = $conn->prepare($count_query);
        
        if (count($params) > 0 && count($where_clauses) > 0) {
            // Remove last two params which are for pagination
            $count_params = array_slice($params, 0, -2);
            $count_types = substr($types, 0, -2);
            
            if (!empty($count_types)) {
                $count_stmt->bind_param($count_types, ...$count_params);
            }
        }
        
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count_row = $count_result->fetch_assoc();
        $total_items = $count_row['total'];
        $total_pages = ceil($total_items / $items_per_page);
        
        // Execute main query
        $stmt = $conn->prepare($query);
        
        if (count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $hostellers = [];
        while ($row = $result->fetch_assoc()) {
            $hostellers[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $hostellers,
            'pagination' => [
                'total_items' => $total_items,
                'total_pages' => $total_pages,
                'current_page' => $page,
                'items_per_page' => $items_per_page
            ]
        ]);
        
        $stmt->close();
        $count_stmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}



// Function to get single warden data
function get_warden() {
    global $conn;
    
    try {
        $id = sanitize_input($_POST['id']);
        
        $stmt = $conn->prepare("SELECT id, name, address, phone_number, building FROM warden WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            throw new Exception("Warden not found");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Function to get wardens with filters
function get_wardens() {
    global $conn;
    
    try {
        // Initialize where clauses
        $where_clauses = [];
        $params = [];
        $types = "";
        
        // Get filter parameters
        $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
        $building = isset($_POST['building']) ? sanitize_input($_POST['building']) : '';
        
        // Build where clause based on filters
        if (!empty($name)) {
            $where_clauses[] = "name LIKE ?";
            $params[] = "%$name%";
            $types .= "s";
        }
        
        if (!empty($building)) {
            $where_clauses[] = "building = ?";
            $params[] = $building;
            $types .= "s";
        }
        
        // Pagination
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;
        
        // Prepare query
        $query = "SELECT id, name, building, phone_number FROM warden";
        
        if (count($where_clauses) > 0) {
            $query .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        // Add limit for pagination
        $query .= " LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $items_per_page;
        $types .= "ii";
        
        // Get total count for pagination
        $count_query = "SELECT COUNT(*) as total FROM warden";
        
        if (count($where_clauses) > 0) {
            $count_query .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        // Execute count query
        $count_stmt = $conn->prepare($count_query);
        
        if (count($params) > 0 && count($where_clauses) > 0) {
            // Remove last two params which are for pagination
            $count_params = array_slice($params, 0, -2);
            $count_types = substr($types, 0, -2);
            
            if (!empty($count_types)) {
                $count_stmt->bind_param($count_types, ...$count_params);
            }
        }
        
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count_row = $count_result->fetch_assoc();
        $total_items = $count_row['total'];
        $total_pages = ceil($total_items / $items_per_page);
        
        // Execute main query
        $stmt = $conn->prepare($query);
        
        if (count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $wardens = [];
        while ($row = $result->fetch_assoc()) {
            // Add status field (always active for now)
            $row['status'] = 'Active';
            $wardens[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $wardens,
            'pagination' => [
                'total_items' => $total_items,
                'total_pages' => $total_pages,
                'current_page' => $page,
                'items_per_page' => $items_per_page
            ]
        ]);
        
        $stmt->close();
        $count_stmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Function to handle logout
function logout() {
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
}

// Check if user is logged in
  if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "warden") {
  header("Location: ../login.php"); // Redirect to your login page
  exit;
}

// Close connection
if ($conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="wStyles/wStyle.css">
    <link rel="stylesheet" href="wStyles/wManage.css">
    <link rel="website icon" type="png" href="images/Hosteller/Home/airplane.png">
    <style>
      .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
      }
      .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
      }
      .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
      }
    </style>
  </head>
  <body>
    <div class="dashboard-container">

      <nav class="left-nav">
        <div class="title">
            <img src="../images/Hosteller/Home/airplane.png">
            <p class="nielit">NIELIT Hostel</p>
        </div>
        <ul>
          <li class="home"><img src="../images/Hosteller/Home/home-icon-silhouette.png" class="home-icon"> <span>Home</span></li>
          <li class="notification"><img src="../images/Hosteller/Home/notification.png" class="notification-icon"> <span>Notifications</span></li>
          <li class="manage"><img src="../images/Hosteller/Home/multiple-users-silhouette.png" class="status-icon"> <span>View Hostellers</span></li>
          <li class="checkout"><img src="../images/Hosteller/Home/logout.png" class="status-icon"> <span>Checkout Requests</span></li>
          <li class="payments"><img src="../images/Hosteller/Home/cash-payment.png" class="payments-icon"> <span>Payments</span></li>
          <li class="reports"><img src="../images/Hosteller/Home/report.png" class="reports-icon"> <span>Reports</span></li>
          
      </ul>
      </nav>

      <div class="right-content">

        <div class="nav-bar">
          <div>
            <h1>Warden Dashboard</h1>
          </div>
          <div class="nav-icon">
            <div><i class="fas fa-power-off menu-icon" id="logout-btn"></i></div>
            <div id="tooltip-user" class="tooltip-target"><i class="fas fa-user-circle menu-icon"></i></div>
          </div>
        </div>

        <div class="right-container">
          <!-- Message container for alerts -->
          <div id="message-container"></div>

          <!-- Hosteller Database Management Content -->
          <div id="hosteller-section" class="content-section active">
            <div class="database-section">
              <div class="section-header">
                <h2><i class="fas fa-users"></i> Hosteller Database</h2>
              </div>

              <!-- Filter Panel -->
              <div class="filter-panel">
                <div class="filter-header">
                  <h3><i class="fas fa-filter"></i> Filter Options</h3>
                  <button id="toggle-filters" class="toggle-btn"><i class="fas fa-chevron-up"></i></button>
                </div>
                
                <div class="filter-content">
                  <div class="filter-row">
                    <div class="filter-group">
                      <label for="semester-filter">Semester</label>
                      <select id="semester-filter" class="filter-select">
                        <option value="">All Semesters</option>
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                        <option value="3">3rd Semester</option>
                        <option value="4">4th Semester</option>
                        <option value="5">5th Semester</option>
                        <option value="6">6th Semester</option>
                        <option value="7">7th Semester</option>
                        <option value="8">8th Semester</option>
                      </select>
                    </div>
                    
                    <div class="filter-group">
                      <label for="course-filter">Course</label>
                      <select id="course-filter" class="filter-select">
                        <option value="">All Courses</option>
                        <option value="btech">B.Tech</option>
                        <option value="mtech">M.Tech</option>
                        <option value="bca">BCA</option>
                        <option value="mca">MCA</option>
                        <option value="phd">PhD</option>
                      </select>
                    </div>
                  
                  <div class="filter-group search-group">
                      <label for="room-search">Room</label>
                      <input type="text" id="room-search" class="filter-input" placeholder="Search by room...">
                    </div>
                    
                    <div class="filter-group search-group">
                      <label for="name-search">Name</label>
                      <input type="text" id="name-search" class="filter-input" placeholder="Search by name...">
                    </div>
                  </div>
                  
                  <div class="filter-actions">
                    <button id="apply-filters" class="primary-btn"><i class="fas fa-search"></i> Apply Filters</button>
                    <button id="clear-filters" class="secondary-btn"><i class="fas fa-undo"></i> Clear Filters</button>
                  </div>
                </div>
              </div>
              
              <!-- Results Summary -->
              <div class="results-summary">
                <p><span id="results-count">0</span> hostellers found</p>
                <div class="view-options">
                  <button class="view-btn active" data-view="table"><i class="fas fa-list"></i> Table View</button>
                </div>
              </div>
              
              <!-- Table View -->
              <div class="table-view active-view" id="table-view">
                <table class="hosteller-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Room</th>
                      <th>Course</th>
                      <th>Semester</th>
                      <th>Contact</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="hosteller-table-body">
                    <!-- Hosteller data will be loaded here -->
                  </tbody>
                </table>
                
                <div class="pagination" id="hosteller-pagination">
                  <!-- Pagination will be populated dynamically -->
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <script src="wScripts/wManage.js"></script>
    <script src="scripts/modal.js"></script>
    <script src="wScripts/wscript.js"></script>

</body>
</html>