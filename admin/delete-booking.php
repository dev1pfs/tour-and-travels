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

function redirect($msg)
{
    header("Location: my-booking.php?msg=" . urlencode($msg));
    exit();
}

function redirect_trash($msg)
{
    header("Location: trash-booking.php?msg=" . urlencode($msg));
    exit();
}

// Permanent delete (from trash)
if (isset($_GET['id']) && isset($_GET['permanent'])) {
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
        redirect_trash("permanent_deleted");
    } catch (Exception $e) {
        $conn->rollback();
        redirect_trash("error");
    }
}

// Restore (from trash)
if (isset($_GET['id']) && isset($_GET['restore'])) {
    $order_id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE orders SET deleted_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) {
        redirect_trash("restored");
    }
    $stmt->close();
    redirect_trash("error");
}

// Soft delete (move to trash)
if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];

    $stmt = $conn->prepare("UPDATE orders SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) {
        redirect("deleted");
    }
    $stmt->close();
    redirect("error");
}

// Delete selected (move to trash)
if (isset($_POST['delete_selected']) && !empty($_POST['order_ids'])) {
    $order_ids = array_map('intval', $_POST['order_ids']);
    $ids_str = implode(',', $order_ids);

    $conn->query("UPDATE orders SET deleted_at = NOW() WHERE id IN ($ids_str) AND deleted_at IS NULL");
    redirect("deleted");
}

// Restore selected (from trash)
if (isset($_POST['restore_selected']) && !empty($_POST['order_ids'])) {
    $order_ids = array_map('intval', $_POST['order_ids']);
    $ids_str = implode(',', $order_ids);

    $conn->query("UPDATE orders SET deleted_at = NULL WHERE id IN ($ids_str)");
    redirect_trash("restored");
}

// Delete selected permanently (from trash)
if (isset($_POST['delete_selected_permanent']) && !empty($_POST['order_ids'])) {
    $order_ids = array_map('intval', $_POST['order_ids']);
    $ids_str = implode(',', $order_ids);

    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM order_items WHERE order_id IN ($ids_str)");
        $conn->query("DELETE FROM orders WHERE id IN ($ids_str) AND deleted_at IS NOT NULL");

        $conn->commit();
        redirect_trash("permanent_deleted");
    } catch (Exception $e) {
        $conn->rollback();
        redirect_trash("error");
    }
}

// Delete all active bookings (move to trash)
if (isset($_POST['delete_all'])) {
    $conn->query("UPDATE orders SET deleted_at = NOW() WHERE deleted_at IS NULL");
    redirect("all_deleted");
}

// Empty trash (permanent delete all trashed)
if (isset($_POST['empty_trash'])) {
    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE deleted_at IS NOT NULL)");
        $conn->query("DELETE FROM orders WHERE deleted_at IS NOT NULL");

        $conn->commit();
        redirect_trash("trash_emptied");
    } catch (Exception $e) {
        $conn->rollback();
        redirect_trash("error");
    }
}

redirect("error");