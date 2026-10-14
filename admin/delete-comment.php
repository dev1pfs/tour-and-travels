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
    header("Location: comments.php?msg=" . urlencode($msg));
    exit();
}

function redirect_trash($msg)
{
    header("Location: trash-comment.php?msg=" . urlencode($msg));
    exit();
}

// Permanent delete (from trash) - also removes replies
if (isset($_GET['id']) && isset($_GET['permanent'])) {
    $comment_id = (int)$_GET['id'];

    $conn->query("DELETE FROM comments WHERE comment_parent = $comment_id AND deleted_at IS NOT NULL");
    $conn->query("DELETE FROM comments WHERE comment_id = $comment_id AND deleted_at IS NOT NULL");
    redirect_trash("permanent_deleted");
}

// Restore (from trash) - also restores replies
if (isset($_GET['id']) && isset($_GET['restore'])) {
    $comment_id = (int)$_GET['id'];

    $conn->query("UPDATE comments SET deleted_at = NULL WHERE comment_parent = $comment_id");
    $conn->query("UPDATE comments SET deleted_at = NULL WHERE comment_id = $comment_id");
    redirect_trash("restored");
}

// Soft delete (move to trash) - also moves replies to trash
if (isset($_GET['id'])) {
    $comment_id = (int)$_GET['id'];

    $conn->query("UPDATE comments SET deleted_at = NOW() WHERE comment_parent = $comment_id AND deleted_at IS NULL");
    $conn->query("UPDATE comments SET deleted_at = NOW() WHERE comment_id = $comment_id AND deleted_at IS NULL");
    redirect("deleted");
}

// Delete selected (move to trash) - also moves replies
if (isset($_POST['delete_selected']) && !empty($_POST['comment_ids'])) {
    $comment_ids = array_map('intval', $_POST['comment_ids']);
    $ids_str = implode(',', $comment_ids);

    $conn->query("UPDATE comments SET deleted_at = NOW() WHERE (comment_id IN ($ids_str) OR comment_parent IN ($ids_str)) AND deleted_at IS NULL");
    redirect("deleted");
}

// Delete all active comments (move to trash)
if (isset($_POST['delete_all'])) {
    $conn->query("UPDATE comments SET deleted_at = NOW() WHERE deleted_at IS NULL");
    redirect("all_deleted");
}

// Restore selected (from trash) - also restores replies
if (isset($_POST['restore_selected']) && !empty($_POST['comment_ids'])) {
    $comment_ids = array_map('intval', $_POST['comment_ids']);
    $ids_str = implode(',', $comment_ids);

    $conn->query("UPDATE comments SET deleted_at = NULL WHERE comment_id IN ($ids_str) OR comment_parent IN ($ids_str)");
    redirect_trash("restored");
}

// Delete selected permanently (from trash) - also removes replies
if (isset($_POST['delete_selected_permanent']) && !empty($_POST['comment_ids'])) {
    $comment_ids = array_map('intval', $_POST['comment_ids']);
    $ids_str = implode(',', $comment_ids);

    $conn->query("DELETE FROM comments WHERE (comment_id IN ($ids_str) OR comment_parent IN ($ids_str)) AND deleted_at IS NOT NULL");
    redirect_trash("permanent_deleted");
}

// Empty trash (permanent delete all trashed)
if (isset($_POST['empty_trash'])) {
    $conn->query("DELETE FROM comments WHERE deleted_at IS NOT NULL");
    redirect_trash("trash_emptied");
}

redirect("error");
