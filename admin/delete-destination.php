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

function redirect_destinations($msg)
{
    header("Location: destination.php?msg=" . urlencode($msg));
    exit();
}

function redirect_trash($msg)
{
    header("Location: trash-destination.php?msg=" . urlencode($msg));
    exit();
}

function delete_destination_image($conn, $id)
{
    $stmt = $conn->prepare("SELECT image FROM destinations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $imageData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($imageData && !empty($imageData['image'])) {
        $filePath = "./uploads/" . $imageData['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

// Permanent delete (from trash)
if (isset($_GET['id']) && isset($_GET['permanent'])) {
    $dest_id = (int)$_GET['id'];

    delete_destination_image($conn, $dest_id);

    $stmt = $conn->prepare("DELETE FROM destinations WHERE id = ?");
    $stmt->bind_param("i", $dest_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_trash("permanent_deleted");
    }
    $stmt->close();
    redirect_trash("error");
}

// Restore (from trash)
if (isset($_GET['id']) && isset($_GET['restore'])) {
    $dest_id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE destinations SET deleted_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $dest_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_trash("restored");
    }
    $stmt->close();
    redirect_trash("error");
}

// Soft delete (move to trash)
if (isset($_GET['id'])) {
    $dest_id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE destinations SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
    $stmt->bind_param("i", $dest_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_destinations("deleted");
    }
    $stmt->close();
    redirect_destinations("error");
}

// Delete selected destinations (move to trash)
if (isset($_POST['delete_selected']) && !empty($_POST['destination_ids'])) {
    $dest_ids = array_map('intval', $_POST['destination_ids']);
    $ids_str = implode(',', $dest_ids);

    $conn->query("UPDATE destinations SET deleted_at = NOW() WHERE id IN ($ids_str) AND deleted_at IS NULL");
    redirect_destinations("deleted");
}

// Restore selected destinations (from trash)
if (isset($_POST['restore_selected']) && !empty($_POST['destination_ids'])) {
    $dest_ids = array_map('intval', $_POST['destination_ids']);
    $ids_str = implode(',', $dest_ids);

    $conn->query("UPDATE destinations SET deleted_at = NULL WHERE id IN ($ids_str)");
    redirect_trash("restored");
}

// Delete selected destinations permanently (from trash)
if (isset($_POST['delete_selected_permanent']) && !empty($_POST['destination_ids'])) {
    $dest_ids = array_map('intval', $_POST['destination_ids']);

    foreach ($dest_ids as $id) {
        delete_destination_image($conn, $id);
    }

    $ids_str = implode(',', $dest_ids);
    $conn->query("DELETE FROM destinations WHERE id IN ($ids_str) AND deleted_at IS NOT NULL");
    redirect_trash("permanent_deleted");
}

// Delete all destinations (move to trash)
if (isset($_POST['delete_all'])) {
    $conn->query("UPDATE destinations SET deleted_at = NOW() WHERE deleted_at IS NULL");
    redirect_destinations("all_deleted");
}

// Empty trash (permanent delete all trashed destinations)
if (isset($_POST['empty_trash'])) {
    $result = $conn->query("SELECT id FROM destinations WHERE deleted_at IS NOT NULL");
    while ($row = $result->fetch_assoc()) {
        delete_destination_image($conn, (int)$row['id']);
    }

    $conn->query("DELETE FROM destinations WHERE deleted_at IS NOT NULL");
    redirect_trash("trash_emptied");
}

redirect_destinations("error");