<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$conn = new mysqli("localhost", "root", "", "booking-system");

$cart_id = intval($_POST['cart_id']);
$action = $_POST['action'];

$result = $conn->query("SELECT quantity FROM cart_items WHERE id = $cart_id");
$row = $result->fetch_assoc();
$quantity = $row['quantity'];

if ($action == "increase") {
    $quantity += 1;
}

if ($action == "decrease" && $quantity > 1) {
    $quantity -= 1;
}

$conn->query("UPDATE cart_items SET quantity = $quantity WHERE id = $cart_id");

header("Location: cart.php");
exit;
