<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /404-page.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

require_once __DIR__ . '/../config.php';

$msg = $_GET['msg'] ?? "";

$commentResult = $conn->query("SELECT COUNT(*) AS total FROM comments");
$comment_count = $commentResult ? (int)$commentResult->fetch_assoc()['total'] : 0;

$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$trashResult = $conn->query("SELECT * FROM destinations WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
$totalDestinations = $trashResult ? $trashResult->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/image/tour-travels-booking-fevicon.svg">
    <title>Admin - Trash</title>
</head>

<body class="dashboard-body">

    <div class="adminbar">
        <div class="adminbar-holder">
            <div class="adminbar-left">
                <ul class="adminbar-menu">
                    <li class="admin-links">
                        <a class="adminbar-link" href="/index.php"><i class="fa-solid fa-house"></i> Tour and Travels</a>
                        <ul class="adminbar-submenu">
                            <li><a href="/index.php">Visit Site</a></li>
                            <li><a href="/shop.php">Visit Store</a></li>
                        </ul>
                    </li>
                    <li class="admin-comment">
                        <a class="comment-count" href="./comments.php">
                            <i class="fa-regular fa-comment"></i> <?= (int)$comment_count ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="adminbar-right">
                <div class="user-menu">
                    <a href="./profile.php" class="btn btn-primary user-name"><?= htmlspecialchars($user['fullname'] ?? 'Admin') ?></a>
                    <a href="/logout.php" class="logout-btn btn btn-primary">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-holder">
        <div class="sidebar">
            <div class="sidebar-logo">
                <a href="/index.php">
                    <img src="./../assets/image/tour-travels-booking-footer-logo.svg" alt="Logo">
                </a>
            </div>
            <ul class="sidebar-menu">
                <li class="nav-link"><a href="./dashboard.php">Dashboard</a></li>
                <li class="nav-link"><a href="./profile.php">Profile</a></li>
                <li class="nav-link">
                    <a href="#" class="has-submenu">Destinations <i class="fa-solid fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="./destination.php">All Destinations</a></li>
                        <li><a href="./add-destination.php">Add Destinations</a></li>
                    </ul>
                </li>
                <li class="nav-link">
                    <a href="#" class="has-submenu">Packages <i class="fa-solid fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="./package.php">All Packages</a></li>
                        <li><a href="./add-package.php">Add Packages</a></li>
                    </ul>
                </li>
                <li class="nav-link">
                    <a href="#" class="has-submenu">Users <i class="fa-solid fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="./users.php">All Users</a></li>
                        <li><a href="./add-user.php">Add Users</a></li>
                    </ul>
                </li>
                <li class="nav-link"><a href="./my-booking.php">Bookings</a></li>
                <li class="nav-link"><a href="./comments.php">Comments</a></li>
                <li class="nav-link"><a href="./messages.php">Mail</a></li>
            </ul>
        </div>

        <div class="dashboard-holder-right">
            <div class="booking-content">

                <?php if ($msg === "permanent_deleted"): ?>
                    <div class="msg-danger">Destination(s) permanently deleted.</div>
                <?php elseif ($msg === "restored"): ?>
                    <div class="msg-info">Destination(s) restored successfully.</div>
                <?php elseif ($msg === "trash_emptied"): ?>
                    <div class="msg-danger">Trash emptied. All deleted destinations were removed permanently.</div>
                <?php elseif ($msg === "error"): ?>
                    <div class="msg-danger">An error occurred. Please try again.</div>
                <?php endif; ?>

                <form action="delete-destination.php" method="POST">
                    <div class="top">
                        <div>
                            <h1 class="dashboard-title"> Trash</h1>
                            <span>Deleted Destinations: (<?= $totalDestinations ?>)</span>
                        </div>
                        <?php if ($totalDestinations > 0): ?>
                            <div class="bulk-actions">
                                <button type="submit" name="restore_selected" onclick="return confirm('Restore selected destinations?')" class="action-btn success">
                                    <i class="fa-solid fa-rotate-left"></i> Restore Selected
                                </button>
                                <button type="submit" name="delete_selected_permanent" onclick="return confirm('Permanently delete selected items? This cannot be undone!')" class="action-btn danger">
                                    <i class="fa-solid fa-xmark"></i> Delete Permanently
                                </button>
                                <button type="submit" name="empty_trash" onclick="return confirm('WARNING: Permanently delete EVERYTHING in the trash? This cannot be undone!')" class="action-btn danger-bold">
                                    <i class="fa-solid fa-dumpster"></i> Empty Trash
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAll"></th>
                                <th>S.N</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Deleted At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($totalDestinations > 0): $sn = 1; ?>
                                <?php while ($row = $trashResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><input type="checkbox" name="destination_ids[]" value="<?= (int)$row['id'] ?>" class="select-item"></td>
                                        <td><?= $sn++; ?></td>
                                        <td>
                                            <img src="./uploads/<?= htmlspecialchars($row['image']) ?>" class="table-img" alt="">
                                        </td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td><?= date("d/m/Y H:i", strtotime($row['deleted_at'])) ?></td>
                                        <td>
                                            <div class="action-group">
                                                <a href="delete-destination.php?id=<?= (int)$row['id'] ?>&restore=1"
                                                    onclick="return confirm('Restore this destination?')"
                                                    class="action-btn success sm">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </a>
                                                <a href="delete-destination.php?id=<?= (int)$row['id'] ?>&permanent=1"
                                                    onclick="return confirm('Permanently delete this destination? This cannot be undone!')"
                                                    class="action-btn red sm">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" align="center">Trash is empty.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        const selectAll = document.getElementById('selectAll');
        const selectItems = document.querySelectorAll('.select-item');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                selectItems.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }
    </script>
</body>

</html>