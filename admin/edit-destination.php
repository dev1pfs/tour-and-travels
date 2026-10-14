<?php
$conn = new mysqli("localhost", "root", "", "booking-system");

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

session_start();

if ($_SESSION['role'] != "admin") {
    header("Location: /404-page.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM destinations WHERE id=? AND deleted_at IS NULL");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Destination not found!");
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = $_POST['title'];
    $description = $_POST['description'];
    $newImage = $data['image'];

    if (!empty($_FILES['image']['name'])) {

        $imgName = time() . "-" . basename($_FILES['image']['name']);
        $tmp = $_FILES['image']['tmp_name'];

        if (move_uploaded_file($tmp, "uploads/" . $imgName)) {

            if (file_exists("uploads/" . $data['image'])) {
                unlink("uploads/" . $data['image']);
            }

            $newImage = $imgName;
        } else {
            $error = "Image upload failed!";
        }
    }

    $stmt = $conn->prepare("UPDATE destinations SET title=?, description=?, image=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $description, $newImage, $id);

    if ($stmt->execute()) {
        header("Location: destination.php");
        exit;
    } else {
        $error = "Update failed!";
    }
}
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
                    <img src="./../assets//image/tour-travels-booking-footer-logo.svg" alt="Logo">
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
                    <a href="#" class="has-submenu">Packages
                        <i class="fa-solid fa-angle-right"></i></a>
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
            <div class="edit">
                <h2>Edit Destination</h2>

                <?php if ($error): ?>
                    <p class="form-error"><?= $error ?></p>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="from-group">
                        <label>Destination Title:</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($data['title']) ?>" required>
                    </div>

                    <div class="from-group">
                        <label>Destination Description:</label>
                        <textarea name="description" rows="5" required><?= htmlspecialchars($data['description']) ?></textarea>
                    </div>

                    <div class="from-group">
                        <label>Current Image:</label>
                        <img src="uploads/<?= htmlspecialchars($data['image']) ?>" width="150">
                    </div>

                    <div class="from-group">
                        <label>Upload New Image</label>
                        <input type="file" name="image">
                    </div>

                    <button class="btn btn-primary" type="submit">Update</button>
                </form>
            </div>
        </div>

    </div>




    <script src="./../assets/js/script.js"></script>
</body>

</html>

</html>