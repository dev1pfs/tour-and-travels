<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /404-page.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function redirect($msg)
{
    header("Location: messages.php?msg=" . urlencode($msg));
    exit();
}

function redirect_trash($msg)
{
    header("Location: trash-message.php?msg=" . urlencode($msg));
    exit();
}

// Permanent delete (from trash)
if (isset($_GET['id']) && isset($_GET['permanent'])) {
    $message_id = (int)$_GET['id'];

    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ? AND deleted_at IS NOT NULL");
    $stmt->bind_param("i", $message_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_trash("permanent_deleted");
    }
    $stmt->close();
    redirect_trash("error");
}

// Restore (from trash)
if (isset($_GET['id']) && isset($_GET['restore'])) {
    $message_id = (int)$_GET['id'];

    $stmt = $conn->prepare("UPDATE contact_messages SET deleted_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_trash("restored");
    }
    $stmt->close();
    redirect_trash("error");
}

// Soft delete (move to trash)
if (isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];

    $stmt = $conn->prepare("UPDATE contact_messages SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
    $stmt->bind_param("i", $message_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect("deleted");
    }
    $stmt->close();
    redirect("error");
}

// Delete selected (move to trash)
if (isset($_POST['delete_selected']) && !empty($_POST['message_ids'])) {
    $message_ids = array_map('intval', $_POST['message_ids']);
    $ids_str = implode(',', $message_ids);

    $conn->query("UPDATE contact_messages SET deleted_at = NOW() WHERE id IN ($ids_str) AND deleted_at IS NULL");
    redirect("deleted");
}

// Delete all active messages (move to trash)
if (isset($_POST['delete_all'])) {
    $conn->query("UPDATE contact_messages SET deleted_at = NOW() WHERE deleted_at IS NULL");
    redirect("all_deleted");
}

// Restore selected (from trash)
if (isset($_POST['restore_selected']) && !empty($_POST['message_ids'])) {
    $message_ids = array_map('intval', $_POST['message_ids']);
    $ids_str = implode(',', $message_ids);

    $conn->query("UPDATE contact_messages SET deleted_at = NULL WHERE id IN ($ids_str)");
    redirect_trash("restored");
}

// Delete selected permanently (from trash)
if (isset($_POST['delete_selected_permanent']) && !empty($_POST['message_ids'])) {
    $message_ids = array_map('intval', $_POST['message_ids']);
    $ids_str = implode(',', $message_ids);

    $conn->query("DELETE FROM contact_messages WHERE id IN ($ids_str) AND deleted_at IS NOT NULL");
    redirect_trash("permanent_deleted");
}

// Empty trash (permanent delete all trashed)
if (isset($_POST['empty_trash'])) {
    $conn->query("DELETE FROM contact_messages WHERE deleted_at IS NOT NULL");
    redirect_trash("trash_emptied");
}

redirect("error");
