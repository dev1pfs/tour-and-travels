<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: /404-page.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "booking-system");

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

require_once __DIR__ . '/../config.php';

$users        = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='user' AND deleted_at IS NULL")->fetch_assoc()['total'];
$packages     = $conn->query("SELECT COUNT(*) AS total FROM packages WHERE deleted_at IS NULL")->fetch_assoc()['total'];
$destinations = $conn->query("SELECT COUNT(*) AS total FROM destinations WHERE deleted_at IS NULL")->fetch_assoc()['total'];
$orders       = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$comments     = $conn->query("SELECT COUNT(*) AS total FROM comments")->fetch_assoc()['total'];
$revenue      = $conn->query("SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders")->fetch_assoc()['total'];

$recent_orders = $conn->query(
    "SELECT o.id, o.created_at, o.total_amount, o.status, u.fullname
     FROM orders o
     JOIN users u ON o.user_id = u.id
     ORDER BY o.id DESC
     LIMIT 5"
);

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
            <div class="dashboard-container">
                <h1 class="dashboard-title">Dashboard</h1>
                <div class="dashboard-card-wrap">
                    <div class="card">
                        <div class="card-icon"><i class="fa-solid fa-users"></i></div>
                        <div class="card-foot">
                            <h3>Users</h3>
                            <span><?= $users ?></span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-icon"><i class="fa-solid fa-suitcase"></i></div>
                        <div class="card-foot">
                            <h3>Packages</h3>
                            <span><?= $packages ?></span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="card-foot">
                            <h3>Destinations</h3>
                            <span><?= $destinations ?></span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                        <div class="card-foot">
                            <h3>Orders</h3>
                            <span><?= $orders ?></span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-icon"><i class="fa-solid fa-comments"></i></div>
                        <div class="card-foot">
                            <h3>Comments</h3>
                            <span><?= $comments ?></span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                        <div class="card-foot">
                            <h3>Revenue</h3>
                            <span><?= format_price($revenue, null, 0) ?></span>
                        </div>
                    </div>
                </div>

                <div class="recent-orders">
                    <h3>Recent Orders</h3>

                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= (int)$order['id'] ?></td>
                                        <td><?= htmlspecialchars($order['fullname']) ?></td>
                                        <td><?= format_price($order['total_amount']) ?></td>
                                        <td>
                                            <span class="status-badge <?= htmlspecialchars($order['status']) ?>">
                                                <?= htmlspecialchars($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date("M j, Y", strtotime($order['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-cell">No orders yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="charts">

                    <div class="chart-card">
                        <h3>Overview Bar Chart</h3>
                        <canvas id="barChart"></canvas>
                    </div>

                    <div class="chart-card">
                        <h3>Overview Pie Chart</h3>
                        <canvas id="pieChart"></canvas>
                    </div>

                </div>

            </div>
        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/script.js"></script>


    <script>
        const dataValues = {
            users: <?= $users ?>,
            packages: <?= $packages ?>,
            destinations: <?= $destinations ?>,
            orders: <?= $orders ?>,
            comments: <?= $comments ?>
        };

        new Chart(document.getElementById("barChart"), {
            type: "bar",
            data: {
                labels: ["Users", "Packages", "Destinations", "Orders", "Comments"],
                datasets: [{
                    label: "Total Count",
                    data: [
                        dataValues.users,
                        dataValues.packages,
                        dataValues.destinations,
                        dataValues.orders,
                        dataValues.comments
                    ],
                    backgroundColor: ["#007bff", "#28a745", "#ffc107", "#dc3545", "#6610f2"]
                }]
            }
        });

        new Chart(document.getElementById("pieChart"), {
            type: "pie",
            data: {
                labels: ["Users", "Packages", "Destinations", "Orders", "Comments"],
                datasets: [{
                    data: [
                        dataValues.users,
                        dataValues.packages,
                        dataValues.destinations,
                        dataValues.orders,
                        dataValues.comments
                    ],
                    backgroundColor: ["#007bff", "#28a745", "#ffc107", "#dc3545", "#6610f2"]
                }]
            }
        });
    </script>


</body>

</html>