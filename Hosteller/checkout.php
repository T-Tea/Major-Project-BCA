<?php
session_start();
// Database connection parameters
$servername = "localhost";
$username = "root"; // replace with your DB username
$password = ""; // replace with your DB password
$dbname = "hostel";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize response array
$response = array();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in as a hosteller
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'hosteller') {
        $response['status'] = 'error';
        $response['message'] = "You must be logged in as a hosteller to submit a checkout request.";
        echo json_encode($response);
        exit();
    }
    
    // Use the user_id from session as hosteller_id
    $hosteller_id = $_SESSION['user_id'];
    
    $subject = $_POST['subject'];
    $from_date = $_POST['from_date'];
    $from_time = $_POST['from_time'];
    $to_date = $_POST['to_date'];
    $to_time = $_POST['to_time'];
    $description = $_POST['description'];
    
    // Format datetime strings
    $from_datetime = date('Y-m-d H:i:s', strtotime("$from_date $from_time"));
    $to_datetime = date('Y-m-d H:i:s', strtotime("$to_date $to_time"));
    $submit_datetime = date('Y-m-d H:i:s'); // Current date and time
    
    // Initialize file path variable
    $file_path = null;
    
    // Handle file upload if present
    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $target_dir = "uploads/checkout_files/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        $unique_filename = uniqid('checkout_') . '.' . $file_extension;
        $target_file = $target_dir . $unique_filename;
        
        // Check file size (limit to 5MB)
        if ($_FILES["file"]["size"] > 5000000) {
            $response['status'] = 'error';
            $response['message'] = "File is too large. Maximum size is 5MB.";
            echo json_encode($response);
            exit();
        }
        
        // Try to upload file
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            $response['status'] = 'error';
            $response['message'] = "Error uploading file.";
            echo json_encode($response);
            exit();
        }
    }
    
    // Prepare SQL statement - Using hosteller_id from the session's user_id
    $stmt = $conn->prepare("INSERT INTO checkout (hosteller_id, subject, from_datetime, to_datetime, description, file_path, status, submit_datetime) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
    
    // Bind parameters
    $stmt->bind_param("issssss", $hosteller_id, $subject, $from_datetime, $to_datetime, $description, $file_path, $submit_datetime);
    
    // Execute statement
    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = "Checkout request submitted successfully.";
    } else {
        $response['status'] = 'error';
        $response['message'] = "Error: " . $stmt->error;
    }
    
    // Close statement
    $stmt->close();
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hosteller dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="website icon" type="png" href="images/Hosteller/Home/airplane.png">
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
                <li class="status"><img src="../images/Hosteller/Home/clipboard.png" class="status-icon"> <span>Status</span></li>
                <li class="payments"><img src="../images/Hosteller/Home/cash-payment.png" class="payments-icon"> <span>Payments</span></li>
                <li class="checkout"><img src="../images/Hosteller/Home/logout.png" class="payments-icon"> <span>Request Checkout</span></li>
                <li class="reports"><img src="../images/Hosteller/Home/report.png" class="reports-icon"> <span>Reports</span></li>
                <li class="transaction"><img src="../images/Hosteller/Home/settings.png" class="transaction-icon"> <span>Transaction History</span></li>
            </ul>
        </nav>
        
        <div class="right-content">
            <div class="nav-bar">
                <div>
                    <h1>Hosteller Dashboard</h1>
                </div>
                <div class="nav-icon">
                    <div><i class="fas fa-power-off menu-icon" id="logout-btn"></i></div>
                    <div id="tooltip-user" class="tooltip-target"><i class="fas fa-user-circle menu-icon"></i></div>
                </div>
            </div>
            
            <div class="right-container">
                <p class="report-p">Request a checkout</p>
                <h3>Send your Hostel leave form here</h3>
                
                <div id="message-container"></div>
                
                <form class="report-form" id="checkout-form" method="post" enctype="multipart/form-data">
                    <label for="subject"><img src="../images/Hosteller/Reports/subject.png" class="reportpage-icon">Subject</label>
                    <input type="text" id="subject" name="subject" placeholder="Enter your reasonings here" required>
                    
                    <label><img src="../images/Hosteller/Reports/who.png" class="reportpage-icon">Leave Duration</label>
                    <div class="time-inputs">
                        <div class="date-range">
                            <label for="from-date">From:</label>
                            <input type="date" id="from-date" name="from_date" required>
                            <input type="time" id="from-time" name="from_time" required>
                        </div>
                        <div class="date-range">
                            <label for="to-date">To:</label>
                            <input type="date" id="to-date" name="to_date" required>
                            <input type="time" id="to-time" name="to_time" required>
                        </div>
                    </div>
                    <input type="hidden" id="time" name="time" value="">
                    
                    <label for="description"><img src="../images/Hosteller/Reports/description.png" class="reportpage-icon">Description</label>
                    <textarea id="description" name="description" placeholder="Describe your reason for leaving..." required></textarea>
                    
                    <label for="file"><img src="../images/Hosteller/Reports/folder.png" class="reportpage-icon">Attach File (Optional)</label>
                    <input type="file" id="file" name="file">
                    
                    <button type="submit">Submit Request</button>
                </form>
                
            </div>
        </div>
    </div>
    
    <script src="scripts/script.js"></script>
    <script src="scripts/checkout.js"></script>
</body>
</html>