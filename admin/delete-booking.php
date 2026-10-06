<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: /404-page.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt1->bind_param("i", $order_id);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt2->bind_param("i", $order_id);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
        header("Location: my-booking.php?msg=deleted");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: my-booking.php?msg=error");
        exit();
    }
}

if (isset($_POST['delete_selected']) && !empty($_POST['order_ids'])) {
    $order_ids = array_map('intval', $_POST['order_ids']);
    $ids_str = implode(',', $order_ids);

    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM order_items WHERE order_id IN ($ids_str)");
        $conn->query("DELETE FROM orders WHERE id IN ($ids_str)");

        $conn->commit();
        header("Location: my-booking.php?msg=deleted");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: my-booking.php?msg=error");
        exit();
    }
}

if (isset($_POST['delete_all'])) {
    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM order_items");
        $conn->query("DELETE FROM orders");

        $conn->commit();
        header("Location: my-booking.php?msg=all_deleted");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: my-booking.php?msg=error");
        exit();
    }
}

header("Location: my-booking.php");
exit();
