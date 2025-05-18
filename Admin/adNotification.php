<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: ../login.php"); // Redirect to your login page
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "hostel");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$notification_message = ""; // Variable to store notification messages

// Process the form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['body'];
    $sender_role = $_SESSION['role']; // Get the role from session
    $attachment_path = null;
    $date_sent = date("Y-m-d H:i:s"); // Current date and time
    
    // Handle file upload if attachment is provided
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $upload_dir = "uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $unique_filename;
        
        // Move uploaded file to target location
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
            $attachment_path = $target_file;
        } else {
            $notification_message = "Sorry, there was an error uploading your file.";
        }
    }
    
    // Insert notification into database
    $stmt = $conn->prepare("INSERT INTO notifications (sender_role, title, description, attachment, date_sent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $sender_role, $title, $description, $attachment_path, $date_sent);
    
    if ($stmt->execute()) {
        $notification_message = "Notification successfully added!";
    } else {
        $notification_message = "Error adding notification: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/adstyle.css">
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
                <li class="manage"><img src="../images/Hosteller/Home/multiple-users-silhouette.png" class="reports-icon"> <span>Manage User Data</span></li>
                <li class="notification"><img src="../images/Hosteller/Home/notification.png" class="notification-icon"> <span> Manage Notifications</span></li>
                <li class="status"><img src="../images/Hosteller/Home/clipboard.png" class="status-icon"> <span>View room Status</span></li>
                <li class="payments"><img src="../images/Hosteller/Home/cash-payment.png" class="payments-icon"> <span>Fee Payments</span></li>
                <li class="reports"><img src="../images/Hosteller/Home/report.png" class="reports-icon"> <span>View Reports</span></li>
                <li class="transaction"><img src="../images/Hosteller/Home/settings.png" class="transaction-icon"> <span>View Transaction History</span></li>
            </ul>
        </nav>

        <div class="right-content">
            <div class="nav-bar">
                <div>
                    <h1>Administrator Dashboard</h1>
                </div>
                <div class="nav-icon">
                    <div><i class="fas fa-power-off menu-icon" id="logout-btn"></i></div>
                    <div id="tooltip-user" class="tooltip-target"><i class="fas fa-user-circle menu-icon"></i></div>
                </div>
            </div>

            <div class="right-container">
                <?php if (!empty($notification_message)): ?>
                <div class="notification-message <?php echo strpos($notification_message, 'Error') !== false ? 'error' : ''; ?> show">
                    <?php echo $notification_message; ?>
                </div>
                <?php endif; ?>
                
                <div class="notification-update-container">
                    <h2><img src="../images/Admin/Adnotification/notification (1).png" class="adnoti-icon">Update Notification</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <label for="title">Notification Title:</label>
                        <input type="text" id="title" name="title" required>
                        
                        <label for="body">Notification Body:</label>
                        <textarea id="body" name="body" rows="4" required></textarea>
                        
                        <label for="attachment">Upload Attachment (optional):</label>
                        <input type="file" id="attachment" name="attachment">
                        
                        <button type="submit">Update Notification</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="scripts/adscript.js"></script>
    <script>
        // Auto-hide notification message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            let notifications = document.querySelectorAll('.notification-message.show');
            notifications.forEach(function(notification) {
                setTimeout(function() {
                    notification.classList.remove('show');
                }, 5000);
            });
        });
    </script>
</body>
</html>