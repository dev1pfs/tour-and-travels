<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];

    $status = 'Pending';

    $stmt = $conn->prepare("INSERT INTO orders (user_id, status, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $status);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, package_id, price, quantity) VALUES (?, ?, ?, ?)");

            foreach ($_SESSION['cart'] as $item) {
                $package_id = (int)$item['package_id'];
                $price      = (float)$item['price'];
                $quantity   = (int)$item['quantity'];

                $stmtItem->bind_param("iidi", $order_id, $package_id, $price, $quantity);
                $stmtItem->execute();
            }
            $stmtItem->close();
        }

        unset($_SESSION['cart']);
        header("Location: ./my-booking.php?msg=booked_success");
        exit();
    } else {
        header("Location: /cart.php?msg=error");
        exit();
    }
}
