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

$msg = $_GET['msg'] ?? "";

$total_comments = $conn->query(
    "SELECT COUNT(*) AS total FROM comments WHERE deleted_at IS NULL"
)->fetch_assoc()['total'];

$parent_comments = $conn->query(
    "SELECT COUNT(*) AS total FROM comments WHERE comment_parent = 0 AND deleted_at IS NULL"
)->fetch_assoc()['total'];

$reply_comments = $conn->query(
    "SELECT COUNT(*) AS total FROM comments WHERE comment_parent != 0 AND deleted_at IS NULL"
)->fetch_assoc()['total'];

$trash_count = $conn->query(
    "SELECT COUNT(*) AS total FROM comments WHERE deleted_at IS NOT NULL"
)->fetch_assoc()['total'];

$sql = "
    SELECT 
        c.comment_id,
        c.comment_content,
        c.comment_date,
        c.comment_parent,
        u.fullname AS user_name,
        p.title AS package_title
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN packages p ON c.comment_package_id = p.id
    WHERE c.deleted_at IS NULL
    ORDER BY c.comment_date DESC
";

$comments = $conn->query($sql);
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="./../assets/css/style.css">
    <link rel="icon" href="./../assets/image/tour-travels-booking-fevicon.svg">
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
                    <?php
                    $result = $conn->query("SELECT COUNT(*) AS total FROM comments WHERE deleted_at IS NULL");
                    $comment_count = $result->fetch_assoc()['total'];
                    ?>

                    <li class="admin-comment">
                        <a class="comment-count" href="./comments.php">
                            <i class="fa-regular fa-comment"></i>
                            <?php echo $comment_count; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="adminbar-right">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $conn = new mysqli("localhost", "root", "", "booking-system");
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $user_id = (int)$_SESSION['user_id'];
                    $stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    $conn->close();
                    ?>

                    <div class="user-menu">
                        <a href="./profile.php" class="btn btn-primary user-name"><?php echo htmlspecialchars($user['fullname']); ?></a>
                        <a href="/login-page.php" class="logout-btn btn btn-primary">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="/login-page.php">Login</a>
                <?php endif; ?>
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
                    <div class="msg-warn">Selected comment(s) moved to trash.</div>
                <?php elseif ($msg === "all_deleted"): ?>
                    <div class="msg-warn">All comments moved to trash.</div>
                <?php elseif ($msg === "error"): ?>
                    <div class="msg-danger">An error occurred. Please try again.</div>
                <?php endif; ?>

                <form id="bulk-delete-form" action="delete-comment.php" method="POST">
                    <div class="top">
                        <div>
                            <h1 class="dashboard-title">All Comments</h1>
                            <span>Total Comments: (<?= (int)$total_comments ?>)</span>
                        </div>
                        <?php if ($total_comments > 0): ?>
                            <div class="bulk-actions">
                                <button type="submit" name="delete_selected" onclick="return confirm('Are you sure you want to delete selected comments?')" class="action-btn danger">
                                    <i class="fa-solid fa-trash-can"></i> Delete Selected
                                </button>
                                <button type="submit" name="delete_all" onclick="return confirm('WARNING: Are you sure you want to DELETE ALL comments?')" class="action-btn danger-bold">
                                    <i class="fa-solid fa-dumpster"></i> Delete All
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAll"></th>
                                <th>S.N</th>
                                <th>User</th>
                                <th>Package</th>
                                <th>Comment</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($comments && $comments->num_rows > 0):
                                $sn = 1;
                                while ($row = $comments->fetch_assoc()):
                            ?>
                                    <tr draggable="true" data-comment-id="<?= (int)$row['comment_id'] ?>">
                                        <td>
                                            <input type="checkbox" name="comment_ids[]" value="<?= (int)$row['comment_id'] ?>" class="select-item">
                                        </td>
                                        <td><?php echo $sn++; ?></td>
                                        <td><?php echo htmlspecialchars($row['user_name'] ?? 'Deleted User'); ?></td>
                                        <td><?php echo htmlspecialchars($row['package_title'] ?? 'Deleted Package'); ?></td>
                                        <td><?php echo htmlspecialchars($row['comment_content']); ?></td>
                                        <td>
                                            <?php echo $row['comment_parent'] == 0
                                                ? '<span class="badge main">Main</span>'
                                                : '<span class="badge reply">Reply</span>'; ?>
                                        </td>
                                        <td><?php echo date("d M Y", strtotime($row['comment_date'])); ?></td>
                                        <td>
                                            <div class="action-group">
                                                <a href="delete-comment.php?id=<?= (int)$row['comment_id'] ?>"
                                                    onclick="return confirm('Are you sure you want to delete this comment?')"
                                                    class="action-btn red sm">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="8" align="center">No comments found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>

            <a id="trash-bin" class="trash-bin" href="./trash-comment.php" title="Drop a comment row here, or click to view trash">
                <i class="fa-solid fa-trash-can"></i>
                <span><?= (int)$trash_count ?> item(s) in trash</span>
            </a>
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

        const trashBin = document.getElementById('trash-bin');
        const commentRows = document.querySelectorAll('.admin-table tbody tr[data-comment-id]');

        commentRows.forEach(row => {
            row.classList.add('booking-row');

            row.addEventListener('dragstart', function(e) {
                row.classList.add('dragging');
                e.dataTransfer.setData('text/plain', row.dataset.commentId);
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
                const commentId = e.dataTransfer.getData('text/plain');
                if (commentId && confirm('Are you sure you want to delete this comment?')) {
                    window.location.href = 'delete-comment.php?id=' + encodeURIComponent(commentId);
                }
            });
        }
    </script>

</body>

</html>