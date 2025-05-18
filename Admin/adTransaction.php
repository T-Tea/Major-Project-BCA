<?php
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "hostel"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize filter variables
$name_filter = "";
$semester_filter = "";
$room_filter = "";
$start_date = "";
$end_date = "";

// Process filter form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter'])) {
    $name_filter = $_POST['name'] ?? "";
    $semester_filter = $_POST['semester'] ?? "";
    $room_filter = $_POST['room'] ?? "";
    $start_date = $_POST['start_date'] ?? "";
    $end_date = $_POST['end_date'] ?? "";
}

// Build the SQL query with filters
$sql = "SELECT ph.id, ph.user_id, h.name, h.semester, h.room, ph.fee_type, 
        ph.amount, ph.payment_method, ph.transaction_id, ph.payment_date, ph.status 
        FROM payment_history ph
        JOIN hostellers h ON ph.user_id = h.id
        WHERE 1=1";

if (!empty($name_filter)) {
    $sql .= " AND h.name LIKE '%" . $conn->real_escape_string($name_filter) . "%'";
}
if (!empty($semester_filter)) {
    $sql .= " AND h.semester = '" . $conn->real_escape_string($semester_filter) . "'";
}
if (!empty($room_filter)) {
    $sql .= " AND h.room = '" . $conn->real_escape_string($room_filter) . "'";
}
if (!empty($start_date)) {
    $sql .= " AND ph.payment_date >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $sql .= " AND ph.payment_date <= '" . $conn->real_escape_string($end_date) . "'";
}

$sql .= " ORDER BY ph.payment_date DESC";

// Execute query
$result = $conn->query($sql);

// Get distinct semesters for dropdown
$semester_query = "SELECT DISTINCT semester FROM hostellers ORDER BY semester";
$semester_result = $conn->query($semester_query);

// Get distinct room numbers for dropdown
$room_query = "SELECT DISTINCT room FROM hostellers ORDER BY room";
$room_result = $conn->query($room_query);
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
                <div class="transactions-container">
                    <h2>Past Transactions</h2>
                    
                    <div class="filters-section">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="filter-form">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label for="name">Student Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name_filter); ?>" placeholder="Search by name">
                                </div>
                                
                                <div class="filter-group">
                                    <label for="semester">Semester</label>
                                    <select id="semester" name="semester">
                                        <option value="">All Semesters</option>
                                        <?php while($semester_row = $semester_result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($semester_row['semester']); ?>" 
                                                <?php if($semester_filter == $semester_row['semester']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($semester_row['semester']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="filter-group">
                                    <label for="room">Room Number</label>
                                    <select id="room" name="room">
                                        <option value="">All Rooms</option>
                                        <?php while($room_row = $room_result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($room_row['room']); ?>"
                                                <?php if($room_filter == $room_row['room']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($room_row['room']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                                </div>
                                
                                <div class="filter-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                                </div>
                                
                                <div class="filter-buttons">
                                    <button type="submit" name="filter" class="filter-btn">Apply Filters</button>
                                    <button type="submit" name="reset" class="reset-btn">Reset</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="transactions-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student Name</th>
                                    <th>Semester</th>
                                    <th>Room</th>
                                    <th>Fee Type</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Transaction ID</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["semester"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["room"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["fee_type"]) . "</td>";
                                        echo "<td>₹" . htmlspecialchars($row["amount"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["payment_method"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["transaction_id"]) . "</td>";
                                        echo "<td>" . date('d-m-Y', strtotime($row["payment_date"])) . "</td>";
                                        echo "<td><span class='status " . strtolower($row["status"]) . "'>" . htmlspecialchars($row["status"]) . "</span></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='10' class='no-data'>No transactions found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="pagination">
                        <!-- Pagination would go here - implement if needed -->
                        <button class="pagination-btn"><i class="fas fa-angle-left"></i></button>
                        <span class="page-info">Page 1 of 1</span>
                        <button class="pagination-btn"><i class="fas fa-angle-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="scripts/adscript.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>