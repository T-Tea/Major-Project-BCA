<?php
// Start session if not already started
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hostel";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Validate and sanitize input
    $user_id = (int)$_SESSION['user_id']; // Cast to integer
    
    // Validate complaint type
    $valid_complaint_types = ['Room Issue', 'Facility Issue', 'Maintenance Request', 'Noise Complaint', 'Other'];
    $complaint_type = $_POST['complaint_type'];
    if (!in_array($complaint_type, $valid_complaint_types)) {
        $error_message = "Invalid complaint type";
        // Handle error appropriately
    }

    // Validate recipient
    $valid_recipients = ['Warden', 'Admin'];
    $recipient = $_POST['recipient'];
    if (!in_array($recipient, $valid_recipients)) {
        $error_message = "Invalid recipient";
        // Handle error appropriately
    }
    
    // Sanitize text inputs
    $subject = htmlspecialchars(trim($_POST['subject']));
    $description = htmlspecialchars(trim($_POST['description']));
    
    // Validate required fields
    if (empty($subject) || empty($description)) {
        $error_message = "Subject and description are required fields";
        // Handle error appropriately
    }
    
    $attachment_path = NULL;

    // Only process if all validations passed
    if (!isset($error_message)) {
        // Handle file upload if a file was submitted
        if(isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            // Validate file type and size
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            $max_file_size = 5 * 1024 * 1024; // 5MB
            
            $file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $file_size = $_FILES['file']['size'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $error_message = "Invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
            } elseif ($file_size > $max_file_size) {
                $error_message = "File size exceeds the limit (5MB)";
            } else {
                $upload_dir = "uploads/";
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename with random string to prevent overwrites
                $filename = time() . '_' . bin2hex(random_bytes(8)) . '_' . basename($_FILES['file']['name']);
                $target_file = $upload_dir . $filename;
                
                // Move uploaded file to target directory
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                    $attachment_path = $target_file;
                } else {
                    $error_message = "Failed to upload file";
                }
            }
        }
    }
    
    // If all validations passed, insert into database
    if (!isset($error_message)) {
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO reports (user_id, complaint_type, recipient, subject, description, attachment_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $complaint_type, $recipient, $subject, $description, $attachment_path);

        // Execute statement
        if ($stmt->execute()) {
            $success_message = "Report submitted successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
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
            <!-- Navigation content remains the same -->
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
                <p class="report-p">Report an Issue</p>
                <h3>File a complaint or send information to the warden or admin.</h3>
            
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
            
                <form class="report-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                    <label for="complaint-type"><img src="../images/Hosteller/Reports/complaint.png" class="reportpage-icon"> Complaint Type</label>
                    <select id="complaint-type" name="complaint_type" required>
                        <option value="Room Issue">Room Issue</option>
                        <option value="Facility Issue">Facility Issue</option>
                        <option value="Maintenance Request">Maintenance Request</option>
                        <option value="Noise Complaint">Noise Complaint</option>
                        <option value="Other">Other</option>
                    </select>
            
                    <label for="recipient"><img src="../images/Hosteller/Reports/who.png" class="reportpage-icon">Send To</label>
                    <select id="recipient" name="recipient" required>
                        <option value="Warden">Warden</option>
                        <option value="Admin">Admin</option>
                    </select>
            
                    <label for="subject"><img src="../images/Hosteller/Reports/subject.png" class="reportpage-icon">Subject</label>
                    <input type="text" id="subject" name="subject" placeholder="Enter a short title" required maxlength="100">
            
                    <label for="description"><img src="../images/Hosteller/Reports/description.png" class="reportpage-icon">Description</label>
                    <textarea id="description" name="description" placeholder="Describe your issue..." required maxlength="2000"></textarea>
            
                    <label for="file"><img src="../images/Hosteller/Reports/folder.png" class="reportpage-icon">Attach File (Optional)</label>
                    <input type="file" id="file" name="file">
                    <small>Allowed file types: jpg, jpeg, png, pdf, doc, docx. Max size: 5MB</small>
            
                    <button type="submit">Submit Report</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="scripts/script.js"></script>
</body>
</html>