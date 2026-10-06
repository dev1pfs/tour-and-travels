<?php
session_start();

// Admin Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /404-page.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $status   = $_POST['status'] ?? 'Pending';

    // स्वीकृत Status हरू
    $allowed_statuses = ['Pending', 'Completed', 'Cancelled'];

    if (in_array($status, $allowed_statuses) && $order_id > 0) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: my-booking.php?msg=status_updated");
            exit();
        }
    }
}

header("Location: my-booking.php?msg=error");
exit();
