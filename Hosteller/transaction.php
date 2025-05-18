<?php
// Database connection configuration
$db_host = "localhost";
$db_user = "root";     // Change to your database username
$db_pass = "";         // Change to your database password
$db_name = "hostel";   // Change to your database name

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch hosteller details
$hosteller_query = "SELECT * FROM hostellers WHERE id = ?";
$stmt = $conn->prepare($hosteller_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$hosteller_result = $stmt->get_result();
$hosteller = $hosteller_result->fetch_assoc();

// Fetch transaction history
$transaction_query = "SELECT * FROM payment_history WHERE user_id = ? ORDER BY payment_date DESC";
$stmt = $conn->prepare($transaction_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transaction_result = $stmt->get_result();

// Function to get status class
function getStatusClass($status) {
    switch(strtolower($status)) {
        case 'completed':
        case 'success':
            return 'status-success';
        case 'pending':
            return 'status-pending';
        case 'failed':
            return 'status-failed';
        default:
            return 'status-other';
    }
}

// Function to format date
function formatDate($date) {
    return date("d M Y, h:i A", strtotime($date));
}
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
    <link rel="stylesheet" href="styles/transaction.css">
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
            <div class="transaction-history-container">
                <div class="transaction-header">
                    <h2>Transaction History</h2>
                    <div class="filter-controls">
                        <select id="fee-type-filter">
                            <option value="all">All Fee Types</option>
                            <option value="room_rent">Room Rent</option>
                            <option value="mess_fee">Mess Fee</option>
                        </select>
                        <select id="status-filter">
                            <option value="all">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                <div class="payment-status-summary">
                    <div class="status-card">
                        <div class="status-icon room-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="status-details">
                            <h3>Room Rent</h3>
                            <p class="status-label <?php echo $hosteller['room_rent'] ? 'paid' : 'unpaid'; ?>">
                                <?php echo $hosteller['room_rent'] ? 'Paid' : 'Unpaid'; ?>
                            </p>
                        </div>
                    </div>
                    <div class="status-card">
                        <div class="status-icon mess-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="status-details">
                            <h3>Mess Fee</h3>
                            <p class="status-label <?php echo $hosteller['mess_fee'] ? 'paid' : 'unpaid'; ?>">
                                <?php echo $hosteller['mess_fee'] ? 'Paid' : 'Unpaid'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="transactions-table-container">
                    <?php if ($transaction_result->num_rows > 0): ?>
                        <table class="transactions-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Fee Type</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Transaction ID</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($transaction = $transaction_result->fetch_assoc()): ?>
                                    <tr data-fee-type="<?php echo $transaction['fee_type']; ?>" data-status="<?php echo strtolower($transaction['status']); ?>">
                                        <td><?php echo formatDate($transaction['payment_date']); ?></td>
                                        <td>
                                            <span class="fee-type-badge <?php echo $transaction['fee_type']; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $transaction['fee_type'])); ?>
                                            </span>
                                        </td>
                                        <td>₹<?php echo number_format($transaction['amount'], 2); ?></td>
                                        <td><?php echo $transaction['payment_method']; ?></td>
                                        <td>
                                            <span class="transaction-id"><?php echo $transaction['transaction_id']; ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo getStatusClass($transaction['status']); ?>">
                                                <?php echo $transaction['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-transactions">
                            <i class="fas fa-receipt"></i>
                            <p>No transaction history found</p>
                            <span>Your payment history will appear here once you make payments</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </div>
        </div>
                
    
    <script src="scripts/script.js"></script>
    <script src="scripts/transaction.js"></script>
</body>
</html>