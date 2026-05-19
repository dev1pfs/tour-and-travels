<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$total_comments = $conn->query(
    "SELECT COUNT(*) AS total FROM comments"
)->fetch_assoc()['total'];

$parent_comments = $conn->query(
    "SELECT COUNT(*) AS total FROM comments WHERE comment_parent = 0"
)->fetch_assoc()['total'];

$reply_comments = $conn->query(
    "SELECT COUNT(*) AS total FROM comments WHERE comment_parent != 0"
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
                                <a href="/cart.php">Visit Store</a>
                            </li>
                        </ul>
                    </li>
                    <?php
                    $result = $conn->query("SELECT COUNT(*) AS total FROM comments");
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
                        <span class="btn btn-primary user-name"><?php echo htmlspecialchars($user['fullname']); ?></span>
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
            </ul>
        </div>

        <div class="dashboard-holder-right">
            <div class="all-destination comment-table">
                <style>
                    .dashboard-cards {
                        display: flex;
                        gap: 20px;
                        margin-bottom: 30px;
                    }

                    .card {
                        background: #fff;
                        padding: 20px;
                        border-radius: 6px;
                        box-shadow: 0 2px 6px rgba(0, 0, 0, .1);
                        flex: 1;
                        text-align: center;
                    }

                    .admin-table {
                        width: 100%;
                        border-collapse: collapse;
                    }

                    .admin-table th,
                    .admin-table td {
                        padding: 12px;
                        border: 1px solid #ddd;
                    }

                    .badge.main {
                        background: #007bff;
                        color: #fff;
                        padding: 3px 8px;
                        border-radius: 4px;
                    }

                    .badge.reply {
                        background: #28a745;
                        color: #fff;
                        padding: 3px 8px;
                        border-radius: 4px;
                    }

                    .btn.delete {
                        color: red;
                        text-decoration: none;
                    }
                </style>
                <h1 class="dashboard-title">All Comments</h1>

                <table>
                    <thead>
                        <tr>
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
                                <tr>
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
                                        <a href="delete-comment.php?id=<?php echo $row['comment_id']; ?>"
                                            class="btn delete"
                                            onclick="return confirm('Delete this comment?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="7">No comments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>


    <script src="../assets/js/script.js"></script>

</body>

</html>