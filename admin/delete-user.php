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

function redirect_users($msg)
{
    header("Location: users.php?msg=" . urlencode($msg));
    exit();
}

function redirect_trash($msg)
{
    header("Location: trash-users.php?msg=" . urlencode($msg));
    exit();
}

// Permanent delete (from trash)
if (isset($_GET['id']) && isset($_GET['permanent'])) {
    $user_id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_trash("permanent_deleted");
    }
    $stmt->close();
    redirect_trash("error");
}

// Restore (from trash)
if (isset($_GET['id']) && isset($_GET['restore'])) {
    $user_id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE users SET deleted_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_trash("restored");
    }
    $stmt->close();
    redirect_trash("error");
}

// Soft delete (move to trash)
if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_users("deleted");
    }
    $stmt->close();
    redirect_users("error");
}

// Delete selected users (move to trash)
if (isset($_POST['delete_selected']) && !empty($_POST['user_ids'])) {
    $user_ids = array_map('intval', $_POST['user_ids']);
    $ids_str = implode(',', $user_ids);

    $conn->query("UPDATE users SET deleted_at = NOW() WHERE id IN ($ids_str) AND deleted_at IS NULL");
    redirect_users("deleted");
}

// Restore selected users (from trash)
if (isset($_POST['restore_selected']) && !empty($_POST['user_ids'])) {
    $user_ids = array_map('intval', $_POST['user_ids']);
    $ids_str = implode(',', $user_ids);

    $conn->query("UPDATE users SET deleted_at = NULL WHERE id IN ($ids_str)");
    redirect_trash("restored");
}

// Delete selected users permanently (from trash)
if (isset($_POST['delete_selected_permanent']) && !empty($_POST['user_ids'])) {
    $user_ids = array_map('intval', $_POST['user_ids']);
    $ids_str = implode(',', $user_ids);

    $conn->query("DELETE FROM users WHERE id IN ($ids_str) AND deleted_at IS NOT NULL");
    redirect_trash("permanent_deleted");
}

// Delete all users (move to trash; admins kept)
if (isset($_POST['delete_all'])) {
    $conn->query("UPDATE users SET deleted_at = NOW() WHERE role != 'admin' AND deleted_at IS NULL");
    redirect_users("all_deleted");
}

// Empty trash (permanent delete all trashed users)
if (isset($_POST['empty_trash'])) {
    $conn->query("DELETE FROM users WHERE deleted_at IS NOT NULL");
    redirect_trash("trash_emptied");
}

redirect_users("error");