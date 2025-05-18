<?php
// Database connection
$servername = "localhost";
$username = "root"; // Change as per your database credentials
$password = ""; // Change as per your database credentials
$dbname = "hostel";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new hosteller
    if (isset($_POST['add_hosteller'])) {
        $name = mysqli_real_escape_string($conn, $_POST['hosteller-name']);
        $course = mysqli_real_escape_string($conn, $_POST['hosteller-course']);
        $semester = mysqli_real_escape_string($conn, $_POST['hosteller-semester']);
        $room = mysqli_real_escape_string($conn, $_POST['hosteller-room']);
        $phone = mysqli_real_escape_string($conn, $_POST['hosteller-contact']);
        $password = password_hash($_POST['hosteller-password'], PASSWORD_DEFAULT); // Hash password
        
        // Determine building based on room number (assuming room numbers follow a pattern)
        $building = (intval($room) >= 200) ? 'Boys' : 'Girls';
        
        // Default values for fees
        $mess_fee = 3000; // Default mess fee
        $room_rent = 5000; // Default room rent
        
        $sql = "INSERT INTO hostellers (name, password, course, semester, phone_number, room, building, mess_fee, room_rent) 
                VALUES ('$name', '$password', '$course', '$semester', '$phone', '$room', '$building', $mess_fee, $room_rent)";
        
        if ($conn->query($sql) === TRUE) {
            $response = ["status" => "success", "message" => "Hosteller added successfully"];
        } else {
            $response = ["status" => "error", "message" => "Error: " . $conn->error];
        }
        
        echo json_encode($response);
        exit;
    }
    
    // Add new warden
    if (isset($_POST['add_warden'])) {
        $name = mysqli_real_escape_string($conn, $_POST['warden-name']);
        $building = mysqli_real_escape_string($conn, $_POST['warden-building']);
        $phone = mysqli_real_escape_string($conn, $_POST['warden-contact']);
        $status = mysqli_real_escape_string($conn, $_POST['warden-status']);
        $password = password_hash($_POST['warden-password'], PASSWORD_DEFAULT); // Hash password
        $address = ""; // Default empty address, could be added to form later
        
        $sql = "INSERT INTO warden (name, password, address, phone_number, building, status) 
                VALUES ('$name', '$password', '$address', '$phone', '$building', '$status')";
        
        if ($conn->query($sql) === TRUE) {
            $response = ["status" => "success", "message" => "Warden added successfully"];
        } else {
            $response = ["status" => "error", "message" => "Error: " . $conn->error];
        }
        
        echo json_encode($response);
        exit;
    }
    
    // Delete hosteller
    if (isset($_POST['delete_hosteller'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        $sql = "DELETE FROM hostellers WHERE id = '$id'";
        
        if ($conn->query($sql) === TRUE) {
            $response = ["status" => "success", "message" => "Hosteller deleted successfully"];
        } else {
            $response = ["status" => "error", "message" => "Error: " . $conn->error];
        }
        
        echo json_encode($response);
        exit;
    }
    
    // Delete warden
    if (isset($_POST['delete_warden'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        $sql = "DELETE FROM warden WHERE id = '$id'";
        
        if ($conn->query($sql) === TRUE) {
            $response = ["status" => "success", "message" => "Warden deleted successfully"];
        } else {
            $response = ["status" => "error", "message" => "Error: " . $conn->error];
        }
        
        echo json_encode($response);
        exit;
    }
}

// Fetch hostellers with filtering
function getHostellers($conn, $filters = []) {
    $where = [];
    
    if (!empty($filters['semester'])) {
        $where[] = "semester = '" . mysqli_real_escape_string($conn, $filters['semester']) . "'";
    }
    
    if (!empty($filters['course'])) {
        $where[] = "course = '" . mysqli_real_escape_string($conn, $filters['course']) . "'";
    }
    
    if (!empty($filters['floor'])) {
        // Assuming floor can be derived from room number (first digit)
        $floor = mysqli_real_escape_string($conn, $filters['floor']);
        $where[] = "LEFT(room, 1) = '$floor'";
    }
    
    if (!empty($filters['room'])) {
        $where[] = "room = '" . mysqli_real_escape_string($conn, $filters['room']) . "'";
    }
    
    if (!empty($filters['name'])) {
        $where[] = "name LIKE '%" . mysqli_real_escape_string($conn, $filters['name']) . "%'";
    }
    
    if (!empty($filters['address'])) {
        // No address field in hostellers table as per your schema
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $sql = "SELECT * FROM hostellers $whereClause ORDER BY id DESC";
    
    // Add pagination
    $page = isset($filters['page']) ? intval($filters['page']) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $countSql = "SELECT COUNT(*) as total FROM hostellers $whereClause";
    $countResult = $conn->query($countSql);
    $totalRows = $countResult->fetch_assoc()['total'];
    
    $sql .= " LIMIT $perPage OFFSET $offset";
    
    $result = $conn->query($sql);
    $hostellers = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $hostellers[] = $row;
        }
    }
    
    return [
        'data' => $hostellers,
        'total' => $totalRows,
        'pages' => ceil($totalRows / $perPage),
        'current_page' => $page
    ];
}

// Fetch wardens with filtering
function getWardens($conn, $filters = []) {
    $where = [];
    
    if (!empty($filters['name'])) {
        $where[] = "name LIKE '%" . mysqli_real_escape_string($conn, $filters['name']) . "%'";
    }
    
    if (!empty($filters['building'])) {
        $where[] = "building = '" . mysqli_real_escape_string($conn, $filters['building']) . "'";
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $sql = "SELECT * FROM warden $whereClause ORDER BY id DESC";
    
    // Add pagination
    $page = isset($filters['page']) ? intval($filters['page']) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $countSql = "SELECT COUNT(*) as total FROM warden $whereClause";
    $countResult = $conn->query($countSql);
    $totalRows = $countResult->fetch_assoc()['total'];
    
    $sql .= " LIMIT $perPage OFFSET $offset";
    
    $result = $conn->query($sql);
    $wardens = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $wardens[] = $row;
        }
    }
    
    return [
        'data' => $wardens,
        'total' => $totalRows,
        'pages' => ceil($totalRows / $perPage),
        'current_page' => $page
    ];
}

// API endpoint for getting hostellers
if (isset($_GET['action']) && $_GET['action'] == 'get_hostellers') {
    $filters = [
        'semester' => isset($_GET['semester']) ? $_GET['semester'] : '',
        'course' => isset($_GET['course']) ? $_GET['course'] : '',
        'floor' => isset($_GET['floor']) ? $_GET['floor'] : '',
        'room' => isset($_GET['room']) ? $_GET['room'] : '',
        'name' => isset($_GET['name']) ? $_GET['name'] : '',
        'page' => isset($_GET['page']) ? $_GET['page'] : 1
    ];
    
    $result = getHostellers($conn, $filters);
    echo json_encode($result);
    exit;
}

// API endpoint for getting wardens
if (isset($_GET['action']) && $_GET['action'] == 'get_wardens') {
    $filters = [
        'name' => isset($_GET['name']) ? $_GET['name'] : '',
        'building' => isset($_GET['building']) ? $_GET['building'] : '',
        'page' => isset($_GET['page']) ? $_GET['page'] : 1
    ];
    
    $result = getWardens($conn, $filters);
    echo json_encode($result);
    exit;
}

// Get specific hosteller details
if (isset($_GET['action']) && $_GET['action'] == 'get_hosteller') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $sql = "SELECT * FROM hostellers WHERE id = '$id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $hosteller = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $hosteller]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hosteller not found']);
    }
    exit;
}

// Get specific warden details
if (isset($_GET['action']) && $_GET['action'] == 'get_warden') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $sql = "SELECT * FROM warden WHERE id = '$id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $warden = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $warden]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Warden not found']);
    }
    exit;
}

// Update hosteller
if (isset($_POST['update_hosteller'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['hosteller-name']);
    $course = mysqli_real_escape_string($conn, $_POST['hosteller-course']);
    $semester = mysqli_real_escape_string($conn, $_POST['hosteller-semester']);
    $room = mysqli_real_escape_string($conn, $_POST['hosteller-room']);
    $phone = mysqli_real_escape_string($conn, $_POST['hosteller-contact']);
    
    // Password handling - only update if provided
    $passwordClause = "";
    if (!empty($_POST['hosteller-password'])) {
        $password = password_hash($_POST['hosteller-password'], PASSWORD_DEFAULT);
        $passwordClause = ", password = '$password'";
    }
    
    $building = (intval($room) >= 200) ? 'Boys' : 'Girls';
    
    $sql = "UPDATE hostellers SET 
            name = '$name', 
            course = '$course', 
            semester = '$semester', 
            phone_number = '$phone', 
            room = '$room',
            building = '$building'
            $passwordClause
            WHERE id = '$id'";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Hosteller updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

// Update warden
if (isset($_POST['update_warden'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $building = mysqli_real_escape_string($conn, $_POST['building']);
    $phone = mysqli_real_escape_string($conn, $_POST['warden-contact']);
    $status = mysqli_real_escape_string($conn, $_POST['warden-status']);
    
    // Password handling - only update if provided
    $passwordClause = "";
    if (!empty($_POST['warden-password'])) {
        $password = password_hash($_POST['warden-password'], PASSWORD_DEFAULT);
        $passwordClause = ", password = '$password'";
    }
    
    $sql = "UPDATE warden SET 
            name = '$name', 
            building = '$building', 
            phone_number = '$phone',
            status = '$status'
            $passwordClause
            WHERE id = '$id'";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Warden updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
    }
    exit;
}

// If not an API request, load the regular page
?>