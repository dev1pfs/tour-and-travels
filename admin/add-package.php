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

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageName = time() . "-" . basename($_FILES['image']['name']);
    $imageTmp  = $_FILES['image']['tmp_name'];
    $uploadPath = $uploadDir . $imageName;

    if (move_uploaded_file($imageTmp, $uploadPath)) {

        $title       = $_POST['title'];
        $description = $_POST['description'];
        $days        = $_POST['days'];
        $price       = $_POST['price'];
        $currency    = $_POST['base-currency'];

        $stmt = $conn->prepare("
            INSERT INTO packages (image, title, description, price, days, `base-currency`)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss", $imageName, $title, $description, $price, $days, $currency);

        if ($stmt->execute()) {
            $success = "Package added successfully!";
        } else {
            $error = "Database Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error = "Image upload failed!";
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


        <div class="dashboard-holder-right ">
            <div class="package-content edit-package-content">
                <h2>Add New Package</h2>

                <?php if (!empty($success)) { ?>
                    <p style="color:green;"><?= $success ?></p>
                <?php } ?>

                <?php if (!empty($error)) { ?>
                    <p style="color:red;"><?= $error ?></p>
                <?php } ?>

                <form action="" method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>Destination Image:</label>
                        <input type="file" name="image" required>
                    </div>

                    <div class="form-group">
                        <label>Destination Title:</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Days:</label>
                        <input type="text" name="days" required>
                    </div>

                    <div class="form-group">
                        <label>Price</label>
                        <input type="text" name="price" required>
                    </div>

                    <div class="form-group">
                        <label>Currency:</label>
                        <select name="base-currency" required>
                            <option value="Rs">Rs</option>
                            <option value="$">$</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Package</button>

                </form>

            </div>
        </div>

    </div>

    <script src="./../assets/js/script.js"></script>
</body>

</html>