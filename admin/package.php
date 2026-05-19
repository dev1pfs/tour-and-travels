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

if (isset($_GET['delete'], $_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conn->prepare("SELECT image FROM packages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $imageData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($imageData && !empty($imageData['image'])) {
        $filePath = "./uploads/" . $imageData['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: package.php?deleted=1");
    exit();
}

$packageResult = $conn->query("SELECT * FROM packages ORDER BY id DESC");

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
                                <a href="/cart.php">Visit Store</a>
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
                    <span class="btn btn-primary user-name">
                        <?= htmlspecialchars($user['fullname']) ?>
                    </span>
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
            <div class="all-destination">
                <h2>All Packages</h2>

                <?php if (isset($_GET['deleted'])): ?>
                    <p class="msg success">Package deleted successfully!</p>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <th>S.N</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Days</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($packageResult->num_rows > 0): ?>
                            <?php $sn = 1;
                            while ($row = $packageResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $sn++ ?></td>

                                    <td>
                                        <img src="./uploads/<?= htmlspecialchars($row['image']) ?>" width="100">
                                    </td>

                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= (int)$row['days'] ?> Days</td>
                                    <td><?= htmlspecialchars($row['base-currency']) ?> <?= number_format($row['price'], 2) ?></td>

                                    <td class="actions">
                                        <a href="edit-package.php?id=<?= $row['id'] ?>">Edit</a>
                                        <a class="delete"
                                            href="package.php?delete=1&id=<?= $row['id'] ?>"
                                            onclick="return confirm('Are you sure you want to delete this package?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" align="center">No packages found</td>
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