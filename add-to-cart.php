<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login-page.php");
    exit;
}

if (!isset($_GET['package_id'])) {
    die("Invalid Package");
}

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Database Error");
}

$user_id    = $_SESSION['user_id'];
$package_id = intval($_GET['package_id']);

$check = $conn->prepare("
    SELECT id, quantity 
    FROM cart_items 
    WHERE user_id = ? AND package_id = ?
");
$check->bind_param("ii", $user_id, $package_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();
    $qty = $row['quantity'] + 1;

    $update = $conn->prepare("
        UPDATE cart_items SET quantity = ? WHERE id = ?
    ");
    $update->bind_param("ii", $qty, $row['id']);
    $update->execute();
} else {

    $insert = $conn->prepare("
        INSERT INTO cart_items (user_id, package_id, quantity)
        VALUES (?, ?, 1)
    ");
    $insert->bind_param("ii", $user_id, $package_id);
    $insert->execute();
}

header("Location: cart.php");
exit;
