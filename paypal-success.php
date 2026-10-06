<?php
session_start();

header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "booking-system");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['orderID']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request or session expired']);
    exit;
}

$user_id = $_SESSION['user_id'];
$paypal_order_id = $data['orderID'];
$amount = $data['amount'];

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("INSERT INTO orders (user_id, paypal_order_id, total_amount, status) VALUES (?, ?, ?, 'Pending')");
    $stmt->bind_param("isd", $user_id, $paypal_order_id, $amount);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();

    $conn->query("INSERT INTO order_items (order_id, package_id, quantity, price) 
                  SELECT $order_id, package_id, quantity, price 
                  FROM cart_items 
                  JOIN packages ON packages.id = cart_items.package_id 
                  WHERE user_id = $user_id");

    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
