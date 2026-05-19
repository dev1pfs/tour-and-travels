<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$conn = new mysqli("localhost", "root", "", "booking-system");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request");
}

$cart_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    DELETE FROM cart_items 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();

header("Location: cart.php");
exit;
