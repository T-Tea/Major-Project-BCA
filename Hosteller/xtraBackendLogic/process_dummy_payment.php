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
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Process the form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : $_SESSION['user_id'];
    $fee_type = $_POST['fee_type'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $transaction_id = $_POST['transaction_id'] ?? 'TXN' . rand(1000000, 9999999);
    $room = $_POST['room'];
    $building = $_POST['building'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update the user's payment status
        $update_field = ($fee_type === 'room_rent') ? 'room_rent' : 'mess_fee';
        $sql = "UPDATE hostellers SET $update_field = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Log the payment in payment_history table
        // Note: You need to create this table first if it doesn't exist
        $sql = "INSERT INTO payment_history (user_id, fee_type, amount, payment_method, transaction_id, payment_date) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $user_id, $fee_type, $amount, $payment_method, $transaction_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Payment processed successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>