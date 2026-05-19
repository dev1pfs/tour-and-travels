<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: /404-page.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$msg     = $_GET['msg'] ?? "";

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT 
        o.id AS order_id,
        o.created_at AS order_date,
        o.status AS order_status,
        p.title,
        p.image,
        p.days,
        p.`base-currency` AS base_currency,
        oi.price,
        oi.quantity
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN packages p ON oi.package_id = p.id
    ORDER BY o.id DESC, oi.id DESC
";

$orderResult = $conn->query($sql);
$totalOrders = $orderResult->num_rows;

$commentResult = $conn->query("SELECT COUNT(*) AS total FROM comments");
$comment_count = ($commentResult) ? $commentResult->fetch_assoc()['total'] : 0;

$stmtUser = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();
$stmtUser->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/image/tour-travels-booking-fevicon.svg">
    <title>Admin - Bookings</title>
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
                            <li><a href="/cart.php">Visit Store</a></li>
                        </ul>
                    </li>
                    <li class="admin-comment">
                        <a class="comment-count" href="./comments.php">
                            <i class="fa-regular fa-comment"></i> <?= $comment_count ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="adminbar-right">
                <div class="user-menu">
                    <span class="btn btn-primary user-name"><?= htmlspecialchars($user['fullname'] ?? 'Admin') ?></span>
                    <a href="/logout.php" class="logout-btn btn btn-primary">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-holder">
        <div class="sidebar">
            <div class="sidebar-logo">
                <a href="/index.php">
                    <img src="../assets/image/tour-travels-booking-footer-logo.svg" alt="Logo">
                </a>
            </div>
            <ul class="sidebar-menu">
                <li class="nav-link"><a href="./dashboard.php">Dashboard</a></li>
                <li class="nav-link">
                    <a href="#" class="has-submenu">Destinations <i class="fa-solid fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="./destination.php">All Destinations</a></li>
                        <li><a href="./add-destination.php">Add Destination</a></li>
                    </ul>
                </li>
                <li class="nav-link">
                    <a href="#" class="has-submenu">Packages <i class="fa-solid fa-angle-right"></i></a>
                    <ul class="submenu">
                        <li><a href="./package.php">All Packages</a></li>
                        <li><a href="./add-package.php">Add Package</a></li>
                    </ul>
                </li>
                <li class="nav-link"><a href="./users.php">Users</a></li>
                <li class="nav-link active"><a href="./my-booking.php">Bookings</a></li>
                <li class="nav-link"><a href="./comments.php">Comments</a></li>
            </ul>
        </div>

        <div class="dashboard-holder-right">
            <div class="booking-content">
                <?php if ($msg === "OrderCancelled"): ?>
                    <div class="msg success" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px;">Order cancelled successfully.</div>
                <?php endif; ?>

                <div class="top">
                    <h1 class="dashboard-title">Bookings</h1>
                    <span>Total Items Sold: (<?= $totalOrders ?>)</span>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>S.N</th>
                            <th>Package</th>
                            <th>Order Date</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalOrders > 0):
                            $sn = 1;
                            while ($row = $orderResult->fetch_assoc()):
                        ?>
                                <tr>
                                    <td><?php echo $sn++; ?></td>
                                    <td>
                                        <div class="pkg" style="display: flex; align-items: center; gap: 10px;">
                                            <img src="../admin/uploads/<?= htmlspecialchars($row['image']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <div>
                                                <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                                                <small><?= (int)$row['days'] ?> Days</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= date("d/m/Y", strtotime($row['order_date'])) ?></td>
                                    <td>Rs <?= number_format($row['price'], 2) ?></td>
                                    <td><?= (int)$row['quantity'] ?></td>
                                    <td><strong>Rs <?= number_format($row['price'] * $row['quantity'], 2) ?></strong></td>
                                    <td>
                                        <span class="badge" style="padding: 5px 10px; border-radius: 4px; background: <?= ($row['order_status'] == 'Completed') ? '#0a500f' : '#652228'; ?>">
                                            <?= htmlspecialchars($row['order_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="update-status.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">

                                            <select name="status" onchange="return confirm('Change order status?') && this.form.submit()"
                                                style="padding: 5px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;">
                                                <option value="Pending" <?= $row['order_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Completed" <?= $row['order_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="Cancelled" <?= $row['order_status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" align="center">No bookings found in database.</td>
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