<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: comments.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed");
}

$comment_id = (int) $_GET['id'];

$conn->query(
    "DELETE FROM comments WHERE comment_parent = $comment_id"
);

$conn->query(
    "DELETE FROM comments WHERE comment_id = $comment_id"
);

header("Location: comments.php");
exit;
