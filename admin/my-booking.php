<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /404-page.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$msg     = $_GET['msg'] ?? "";

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Orders
$sql = "
    SELECT 
        o.id AS order_id,
        o.created_at AS order_date,
        o.status AS order_status,
        p.title,
        p.image,
        p.days,
        p.`base-currency` AS base_currency,
        oi.id AS item_id,
        oi.price,
        oi.quantity
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN packages p ON oi.package_id = p.id
    ORDER BY o.id DESC, oi.id DESC
";

$orderResult = $conn->query($sql);
$totalOrders = $orderResult ? $orderResult->num_rows : 0;

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
                            <i class="fa-regular fa-comment"></i> <?= (int)$comment_count ?>
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

                <?php if ($msg === "booked_success"): ?>
                    <div class="msg success" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">New booking placed successfully (Status: Pending).</div>
                <?php elseif ($msg === "status_updated"): ?>
                    <div class="msg success" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">Order status updated successfully.</div>
                <?php elseif ($msg === "deleted"): ?>
                    <div class="msg success" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">Selected booking(s) deleted successfully.</div>
                <?php elseif ($msg === "all_deleted"): ?>
                    <div class="msg success" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">All bookings deleted successfully.</div>
                <?php elseif ($msg === "error"): ?>
                    <div class="msg error" style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px;">An error occurred. Please try again.</div>
                <?php endif; ?>

                <form id="bulk-delete-form" action="delete-booking.php" method="POST">
                    <div class="top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div>
                            <h1 class="dashboard-title" style="margin: 0;">Bookings</h1>
                            <span>Total Items Sold: (<?= $totalOrders ?>)</span>
                        </div>
                        <?php if ($totalOrders > 0): ?>
                            <div class="bulk-actions" style="display: flex; gap: 10px;">
                                <button type="submit" name="delete_selected" onclick="return confirm('Are you sure you want to delete selected items?')" style="background: #d9534f; color: white; border: none; padding: 8px 14px; border-radius: 4px; cursor: pointer;">
                                    <i class="fa-solid fa-trash-can"></i> Delete Selected
                                </button>
                                <button type="submit" name="delete_all" onclick="return confirm('WARNING: Are you sure you want to DELETE ALL bookings?')" style="background: #c9302c; color: white; border: none; padding: 8px 14px; border-radius: 4px; cursor: pointer; font-weight: bold;">
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
                            <?php if ($totalOrders > 0): $sn = 1; ?>
                                <?php while ($row = $orderResult->fetch_assoc()): ?>
                                    <?php
                                    // यदि Empty/Null भए सुरुमा सधैं Pending मान्ने
                                    $current_status = !empty($row['order_status']) ? $row['order_status'] : 'Pending';
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="order_ids[]" value="<?= (int)$row['order_id'] ?>" class="select-item">
                                        </td>
                                        <td><?= $sn++; ?></td>
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
                                            <!-- Status Badge Color -->
                                            <span class="badge" style="padding: 5px 10px; border-radius: 4px; color: #fff; background: <?= ($current_status === 'Completed') ? '#0a500f' : ($current_status === 'Cancelled' ? '#652228' : '#e67e22'); ?>">
                                                <?= htmlspecialchars($current_status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <!-- Admin Control Dropdown -->
                                                <select onchange="updateStatus(<?= (int)$row['order_id'] ?>, this.value)" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;">
                                                    <option value="Pending" <?= $current_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="Completed" <?= $current_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                    <option value="Cancelled" <?= $current_status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>

                                                <a href="delete-booking.php?id=<?= (int)$row['order_id'] ?>"
                                                    onclick="return confirm('Are you sure you want to delete this booking?')"
                                                    style="background: #dc3545; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; display: inline-block;">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" align="center">No bookings found in database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>

                <form id="status-form" action="update-status.php" method="POST" style="display:none;">
                    <input type="hidden" name="order_id" id="status_order_id">
                    <input type="hidden" name="status" id="status_value">
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

        function updateStatus(orderId, status) {
            if (confirm('Change order status to ' + status + '?')) {
                document.getElementById('status_order_id').value = orderId;
                document.getElementById('status_value').value = status;
                document.getElementById('status-form').submit();
            }
        }
    </script>
</body>

</html>