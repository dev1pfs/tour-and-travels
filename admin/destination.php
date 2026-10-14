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

$destinationResult = $conn->query("SELECT * FROM destinations WHERE deleted_at IS NULL ORDER BY id DESC");
$totalDestinations = $destinationResult ? $destinationResult->num_rows : 0;

$trashResult = $conn->query("SELECT COUNT(*) AS total FROM destinations WHERE deleted_at IS NOT NULL");
$trash_count = $trashResult ? (int)$trashResult->fetch_assoc()['total'] : 0;

$commentResult = $conn->query("SELECT COUNT(*) AS total FROM comments");
$comment_count = $commentResult->fetch_assoc()['total'];

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/image/tour-travels-booking-fevicon.svg">
    <title>Tour and Travels</title>
</head>

<body class="dashboard-body">

    <div class="adminbar">
        <div class="adminbar-holder">
            <div class="adminbar-left">
                <ul class="adminbar-menu">
                    <li class="admin-links">
                        <a class="adminbar-link" href="/index.php">
                            <i class="fa-solid fa-house"></i>
                            Tour and Travels
                        </a>
                        <ul class="adminbar-submenu">
                            <li>
                                <a href="/index.php">Visit Site</a>
                            </li>
                            <li>
                                <a href="/shop.php">Visit Store</a>
                            </li>
                        </ul>
                    </li>

                    <li class="admin-comment">
                        <a class="comment-count" href="./comments.php">
                            <i class="fa-regular fa-comment"></i>
                            <?= $comment_count ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="adminbar-right">
                <div class="user-menu">
                    <a href="./profile.php" class="btn btn-primary user-name">
                        <?= htmlspecialchars($user['fullname']) ?>
                    </a>
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
                <li class="nav-link active"><a href="./dashboard.php">Dashboard</a></li>
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

                <?php if ($msg === "deleted"): ?>
                    <div class="msg-warn">Selected destination(s) moved to trash.</div>
                <?php elseif ($msg === "all_deleted"): ?>
                    <div class="msg-warn">All destinations moved to trash.</div>
                <?php elseif ($msg === "error"): ?>
                    <div class="msg-danger">An error occurred. Please try again.</div>
                <?php endif; ?>

                <form id="bulk-delete-form" action="delete-destination.php" method="POST">
                    <div class="top">
                        <div>
                            <h1 class="dashboard-title">Destinations</h1>
                            <span>Total Destinations: (<?= $totalDestinations ?>)</span>
                        </div>
                        <div class="bulk-actions">
                            <a href="add-destination.php" class="action-btn primary">
                                <i class="fa-solid fa-plus"></i> Add Destination
                            </a>
                            <?php if ($totalDestinations > 0): ?>
                                <button type="submit" name="delete_selected" onclick="return confirm('Are you sure you want to delete selected destinations?')" class="action-btn danger">
                                    <i class="fa-solid fa-trash-can"></i> Delete Selected
                                </button>
                                <button type="submit" name="delete_all" onclick="return confirm('WARNING: Are you sure you want to DELETE ALL destinations?')" class="action-btn danger-bold">
                                    <i class="fa-solid fa-dumpster"></i> Delete All
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAll"></th>
                                <th>S.N</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($totalDestinations > 0): $sn = 1; ?>
                                <?php while ($row = $destinationResult->fetch_assoc()): ?>
                                    <tr draggable="true" data-destination-id="<?= (int)$row['id'] ?>">
                                        <td>
                                            <input type="checkbox" name="destination_ids[]" value="<?= (int)$row['id'] ?>" class="select-item">
                                        </td>
                                        <td><?= $sn++; ?></td>
                                        <td>
                                            <img src="./uploads/<?= htmlspecialchars($row['image']) ?>" class="table-img" alt="">
                                        </td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td>
                                            <div class="action-group">
                                                <a href="edit-destination.php?id=<?= (int)$row['id'] ?>" class="action-btn primary sm">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="delete-destination.php?id=<?= (int)$row['id'] ?>"
                                                    onclick="return confirm('Are you sure you want to delete this destination?')"
                                                    class="action-btn red sm">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" align="center">No destinations found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <a id="trash-bin" class="trash-bin" href="./trash-destination.php" title="Drop a destination here, or click to view trash">
            <i class="fa-solid fa-trash-can"></i>
            <span><?= $trash_count ?> destination(s) in trash</span>
        </a>
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

        const trashBin = document.getElementById('trash-bin');
        const destRows = document.querySelectorAll('tr[data-destination-id]');

        destRows.forEach(row => {
            row.classList.add('booking-row');

            row.addEventListener('dragstart', function(e) {
                row.classList.add('dragging');
                e.dataTransfer.setData('text/plain', row.dataset.destinationId);
                e.dataTransfer.effectAllowed = 'move';
            });

            row.addEventListener('dragend', function() {
                row.classList.remove('dragging');
                trashBin.classList.remove('bin-active');
            });
        });

        if (trashBin) {
            trashBin.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                trashBin.classList.add('bin-active');
            });

            trashBin.addEventListener('dragleave', function() {
                trashBin.classList.remove('bin-active');
            });

            trashBin.addEventListener('drop', function(e) {
                e.preventDefault();
                trashBin.classList.remove('bin-active');
                const destId = e.dataTransfer.getData('text/plain');
                if (destId && confirm('Are you sure you want to delete this destination?')) {
                    window.location.href = 'delete-destination.php?id=' + encodeURIComponent(destId);
                }
            });
        }
    </script>
</body>

</html>