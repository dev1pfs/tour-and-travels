<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login-page.php"); 
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'user';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Order ID");
}

$order_id = (int) $_GET['id'];

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed");
}

if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id=?");
    $stmt->bind_param("i", $order_id);
} else {
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $order_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found or access denied.");
}

$order = $result->fetch_assoc();

if ($role === 'admin') {
    $update = $conn->prepare("UPDATE orders SET status='Cancelled' WHERE id=?");
    $update->bind_param("i", $order_id);
} else {
    if ($order['status'] !== 'Pending') {
        die("This order is already " . $order['status'] . " and cannot be cancelled by a user.");
    }
    $update = $conn->prepare("UPDATE orders SET status='Cancelled' WHERE id=? AND user_id=?");
    $update->bind_param("ii", $order_id, $user_id);
}

if ($update->execute()) {
    if ($role === 'admin') {
        header("Location: my-booking.php?msg=OrderCancelled");
    } else {
        header("Location: orders.php?msg=OrderCancelled");
    }
} else {
    echo "Error updating record: " . $conn->error;
}
exit;
