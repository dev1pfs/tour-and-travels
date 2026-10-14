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

function redirect_packages($msg)
{
    header("Location: package.php?msg=" . urlencode($msg));
    exit();
}

function redirect_trash($msg)
{
    header("Location: trash-package.php?msg=" . urlencode($msg));
    exit();
}

function delete_package_image($conn, $id)
{
    $stmt = $conn->prepare("SELECT image FROM packages WHERE id = ?");
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
    $package_id = (int)$_GET['id'];

    delete_package_image($conn, $package_id);

    $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->bind_param("i", $package_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_trash("permanent_deleted");
    }
    $stmt->close();
    redirect_trash("error");
}

// Restore (from trash)
if (isset($_GET['id']) && isset($_GET['restore'])) {
    $package_id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE packages SET deleted_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $package_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_trash("restored");
    }
    $stmt->close();
    redirect_trash("error");
}

// Soft delete (move to trash)
if (isset($_GET['id'])) {
    $package_id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE packages SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL");
    $stmt->bind_param("i", $package_id);
    if ($stmt->execute()) {
        $stmt->close();
        redirect_packages("deleted");
    }
    $stmt->close();
    redirect_packages("error");
}

// Delete selected packages (move to trash)
if (isset($_POST['delete_selected']) && !empty($_POST['package_ids'])) {
    $package_ids = array_map('intval', $_POST['package_ids']);
    $ids_str = implode(',', $package_ids);

    $conn->query("UPDATE packages SET deleted_at = NOW() WHERE id IN ($ids_str) AND deleted_at IS NULL");
    redirect_packages("deleted");
}

// Restore selected packages (from trash)
if (isset($_POST['restore_selected']) && !empty($_POST['package_ids'])) {
    $package_ids = array_map('intval', $_POST['package_ids']);
    $ids_str = implode(',', $package_ids);

    $conn->query("UPDATE packages SET deleted_at = NULL WHERE id IN ($ids_str)");
    redirect_trash("restored");
}

// Delete selected packages permanently (from trash)
if (isset($_POST['delete_selected_permanent']) && !empty($_POST['package_ids'])) {
    $package_ids = array_map('intval', $_POST['package_ids']);

    foreach ($package_ids as $id) {
        delete_package_image($conn, $id);
    }

    $ids_str = implode(',', $package_ids);
    $conn->query("DELETE FROM packages WHERE id IN ($ids_str) AND deleted_at IS NOT NULL");
    redirect_trash("permanent_deleted");
}

// Delete all packages (move to trash)
if (isset($_POST['delete_all'])) {
    $conn->query("UPDATE packages SET deleted_at = NOW() WHERE deleted_at IS NULL");
    redirect_packages("all_deleted");
}

// Empty trash (permanent delete all trashed packages)
if (isset($_POST['empty_trash'])) {
    $result = $conn->query("SELECT id FROM packages WHERE deleted_at IS NOT NULL");
    while ($row = $result->fetch_assoc()) {
        delete_package_image($conn, (int)$row['id']);
    }

    $conn->query("DELETE FROM packages WHERE deleted_at IS NOT NULL");
    redirect_trash("trash_emptied");
}

redirect_packages("error");