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

require_once __DIR__ . '/../config.php';

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
    WHERE o.deleted_at IS NULL
    ORDER BY o.id DESC, oi.id DESC
";

$orderResult = $conn->query($sql);
$totalOrders = $orderResult ? $orderResult->num_rows : 0;

$trashResult = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE deleted_at IS NOT NULL");
$trash_count = ($trashResult) ? (int)$trashResult->fetch_assoc()['total'] : 0;

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
                    <img src="../assets/image/tour-travels-booking-footer-logo.svg" alt="Logo">
                </a>
            </div>
            <ul class="sidebar-menu">
                <li class="nav-link"><a href="./dashboard.php">Dashboard</a></li>
                <li class="nav-link"><a href="./profile.php">Profile</a></li>
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
                <li class="nav-link"><a href="./messages.php">Mail</a></li>
            </ul>
        </div>

        <div class="dashboard-holder-right">
            <div class="booking-content">

                <?php if ($msg === "booked_success"): ?>
                    <div class="msg-info">New booking placed successfully (Status: Pending).</div>
                <?php elseif ($msg === "status_updated"): ?>
                    <div class="msg-info">Order status updated successfully.</div>
                <?php elseif ($msg === "deleted"): ?>
                    <div class="msg-warn">Selected booking(s) moved to trash.</div>
                <?php elseif ($msg === "all_deleted"): ?>
                    <div class="msg-warn">All bookings moved to trash.</div>
                <?php elseif ($msg === "error"): ?>
                    <div class="msg-danger">An error occurred. Please try again.</div>
                <?php endif; ?>

                <form id="bulk-delete-form" action="delete-booking.php" method="POST">
                    <div class="top">
                        <div>
                            <h1 class="dashboard-title">Bookings</h1>
                            <span>Total Items Sold: (<?= $totalOrders ?>)</span>
                        </div>
                        <?php if ($totalOrders > 0): ?>
                            <div class="bulk-actions">
                                <button type="submit" name="delete_selected" onclick="return confirm('Are you sure you want to delete selected items?')" class="action-btn danger">
                                    <i class="fa-solid fa-trash-can"></i> Delete Selected
                                </button>
                                <button type="submit" name="delete_all" onclick="return confirm('WARNING: Are you sure you want to DELETE ALL bookings?')" class="action-btn danger-bold">
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
                                    // à¤¯à¤¦à¤¿ Empty/Null à¤­à¤ à¤¸à¥à¤°à¥à¤®à¤¾ à¤¸à¤§à¥ˆà¤‚ Pending à¤®à¤¾à¤¨à¥à¤¨à¥‡
                                    $current_status = !empty($row['order_status']) ? $row['order_status'] : 'Pending';
                                    ?>
                                    <tr draggable="true" data-order-id="<?= (int)$row['order_id'] ?>">
                                        <td>
                                            <input type="checkbox" name="order_ids[]" value="<?= (int)$row['order_id'] ?>" class="select-item">
                                        </td>
                                        <td><?= $sn++; ?></td>
                                        <td>
                                            <div class="pkg">
                                                <img src="../admin/uploads/<?= htmlspecialchars($row['image']) ?>" alt="">
                                                <div>
                                                    <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                                                    <small><?= (int)$row['days'] ?> Days</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= date("d/m/Y", strtotime($row['order_date'])) ?></td>
                                        <td><?= format_price($row['price']) ?></td>
                                        <td><?= (int)$row['quantity'] ?></td>
                                        <td><strong><?= format_price($row['price'] * $row['quantity']) ?></strong></td>
                                        <td>
                                            <!-- Status Badge Color -->
<span class="badge <?= strtolower(htmlspecialchars($current_status)) ?>">
                                        <?= htmlspecialchars($current_status) ?>
                                    </span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <select class="status-select" onchange="updateStatus(<?= (int)$row['order_id'] ?>, this.value)">
                                                    <option value="Pending" <?= $current_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="Completed" <?= $current_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                    <option value="Cancelled" <?= $current_status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>

                                                <a href="delete-booking.php?id=<?= (int)$row['order_id'] ?>"
                                                    onclick="return confirm('Are you sure you want to delete this booking?')"
                                                    class="action-btn red sm">
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

                <form id="status-form" action="update-status.php" method="POST" class="hidden-form">
                    <input type="hidden" name="order_id" id="status_order_id">
                    <input type="hidden" name="status" id="status_value">
                </form>
            </div>

            <a id="trash-bin" class="trash-bin" href="./trash-booking.php" title="Drop a booking row here, or click to view trash">
                <i class="fa-solid fa-trash-can"></i>
                <span><?= $trash_count ?> item(s) in trash</span>
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

        function updateStatus(orderId, status) {
            if (confirm('Change order status to ' + status + '?')) {
                document.getElementById('status_order_id').value = orderId;
                document.getElementById('status_value').value = status;
                document.getElementById('status-form').submit();
            }
        }

        const trashBin = document.getElementById('trash-bin');
        const bookingRows = document.querySelectorAll('.admin-table tbody tr[data-order-id]');

        bookingRows.forEach(row => {
            row.setAttribute('draggable', 'true');
            row.classList.add('booking-row');

            row.addEventListener('dragstart', function(e) {
                row.classList.add('dragging');
                e.dataTransfer.setData('text/plain', row.dataset.orderId);
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
                const orderId = e.dataTransfer.getData('text/plain');
                if (orderId && confirm('Are you sure you want to delete this booking?')) {
                    window.location.href = 'delete-booking.php?id=' + encodeURIComponent(orderId);
                }
            });
        }
    </script>
</body>

</html>