<?php
// Start the session
session_start();

// Database connection parameters
$servername = "localhost";
$username = "root"; // Change according to your MySQL credentials
$password = ""; // Change according to your MySQL credentials
$dbname = "hostel";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the logged-in user's information
$sql = "SELECT * FROM hostellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Get room information and occupants
$room_number = $user['room'];
$building = $user['building'];
$sql = "SELECT h.id, h.name, h.room_rent, h.mess_fee 
        FROM hostellers h 
        WHERE h.room = ? AND h.building = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $room_number, $building);
$stmt->execute();
$occupants_result = $stmt->get_result();
$occupants = [];
while ($row = $occupants_result->fetch_assoc()) {
    $occupants[] = $row;
}
$stmt->close();

// Handle payment update request submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_update'])) {
    $fee_type = $_POST['fee_type'];
    
    // Prepare and execute SQL statement
    $sql = "INSERT INTO payment_request (user_id, room, building, occupant, fee_type) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $room_number, $building, $user['name'], $fee_type);
    
    if ($stmt->execute()) {
        $success_message = "Payment update request submitted successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Close connection if not needed anymore
// $conn->close();
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
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
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
          <?php if(isset($success_message)): ?>
          <div class="alert alert-success">
            <?php echo $success_message; ?>
          </div>
          <?php endif; ?>
          
          <?php if(isset($error_message)): ?>
          <div class="alert alert-danger">
            <?php echo $error_message; ?>
          </div>
          <?php endif; ?>
          
          <div class="payment-container">
            <h2>Room Payment Status</h2>
            
            <div class="room-details">
              <p><strong>Room No:</strong> <?php echo htmlspecialchars($room_number); ?> (<?php echo htmlspecialchars($building); ?>)</p>
            </div>
          
            <div class="occupants">
              <?php foreach ($occupants as $occupant): ?>
              <div class="occupant">
                <p><img src="../images/Hosteller/Home/cactus.png" class="user-icon"> <span class="occupant-name"><?php echo htmlspecialchars($occupant['name']); ?></span></p>
                <p><strong>Room Rent:</strong> 
                  <span class="paystatus <?php echo ($occupant['room_rent'] == 1) ? 'paid' : 'unpaid'; ?>">
                    <?php echo ($occupant['room_rent'] == 1) ? 'Paid' : 'Unpaid'; ?>
                  </span>
                </p>
                <p><strong>Mess Fee:</strong> 
                  <span class="paystatus <?php echo ($occupant['mess_fee'] == 1) ? 'paid' : 'unpaid'; ?>">
                    <?php echo ($occupant['mess_fee'] == 1) ? 'Paid' : 'Unpaid'; ?>
                  </span>
                </p>
              </div>
              <?php endforeach; ?>
            </div>
          
            <div class="payment-qr">
              <p>If payment is due, you can pay it here:</p>
              <button id="pay-fees-btn" class="update-btn">Pay fees</button>
              <br>
              <p>Or scan this QR code:</p>
              <img src="images/icons/Payment/exampleqr.png" alt="QR Code" class="qr-code">
            </div>
          
            <div class="request-update">
              <p>If payments are already made but status is not changed, request for status to be changed from here:</p>
              <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <select name="fee_type" required>
                  <option value="">Select payment type</option>
                  <?php if ($user['room_rent'] == '0'): ?>
                  <option value="room_rent">Room Rent</option>
                  <?php endif; ?>
                  <?php if ($user['mess_fee'] == '0'): ?>
                  <option value="mess_fee">Mess Fee</option>
                  <?php endif; ?>
                </select>
                <button type="submit" name="request_update" class="update-btn">Request update</button>
              </form>
            </div>
          </div>
        </div>        

        </div>

      </div>

    </div>
    
        <!-- Payment Modal -->
    <div id="payment-modal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Fee Payment</h2>
        
        <form id="payment-form" method="post" action="process_dummy_payment.php">
          <div class="form-group">
            <label for="fee-type">Fee Type:</label>
            <select id="fee-type" name="fee_type" required>
              <option value="">Select Payment Type</option>
              <?php if ($user['room_rent'] == '0'): ?>
              <option value="room_rent" data-amount="1500">Room Rent - Rs 1500</option>
              <?php endif; ?>
              <?php if ($user['mess_fee'] == '0'): ?>
              <option value="mess_fee" data-amount="15000">Mess Fee - Rs 15000</option>
              <?php endif; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="amount">Amount (Rs):</label>
            <input type="text" id="amount" name="amount" readonly>
          </div>
          
          <div class="payment-methods">
            <p>Payment Method:</p>
            <div class="payment-options">
              <label class="payment-option">
                <input type="radio" name="payment_method" value="upi" checked>
                <span>UPI</span>
              </label>
              <label class="payment-option">
                <input type="radio" name="payment_method" value="card">
                <span>Card</span>
              </label>
              <label class="payment-option">
                <input type="radio" name="payment_method" value="netbanking">
                <span>Net Banking</span>
              </label>
            </div>
          </div>
          
          <div class="form-group payment-reference">
            <label for="reference">Transaction Reference (Optional):</label>
            <input type="text" id="reference" name="reference" placeholder="For your records">
          </div>
          
          <div class="form-group">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="room" value="<?php echo $room_number; ?>">
            <input type="hidden" name="building" value="<?php echo $building; ?>">
            <button type="submit" class="payment-btn">Process Payment</button>
          </div>
        </form>
      </div>
    </div>
    
    <script src="scripts/script.js"></script>
    <script src="scripts/payment-modal.js"></script>
  </body>
</html>