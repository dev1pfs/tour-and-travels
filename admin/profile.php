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

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT fullname, email, role, profile_image, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$success = "";
$error = "";

if (isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);

    if ($fullname === "" || $email === "") {
        $error = "Full Name and Email are required!";
    } else {
        $update = $conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
        $update->bind_param("ssi", $fullname, $email, $user_id);
        $update->execute();

        $user['fullname'] = $fullname;
        $user['email']    = $email;
        $success = "Profile updated successfully!";
    }
}

if (isset($_FILES['profile_image']) && !empty($_FILES['profile_image']['name'])) {

    if (!empty($_FILES['profile_image']['name'])) {
        $uploadDir = __DIR__ . "/uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowed = ["jpg", "jpeg", "png", "gif", "webp"];
        $ext     = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG, GIF and WEBP images are allowed!";
        } elseif ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            $error = "Image size must be less than 2MB!";
        } else {
            $imageName   = "profile-" . $user_id . "-" . time() . "." . $ext;
            $uploadPath  = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {

                if (!empty($user['profile_image'])) {
                    $oldPath = $uploadDir . $user['profile_image'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->bind_param("si", $imageName, $user_id);
                $stmt->execute();
                $stmt->close();

                $user['profile_image'] = $imageName;
                $success = "Profile image updated successfully!";
            } else {
                $error = "Image upload failed!";
            }
        }
    } else {
        $error = "Please choose an image to upload!";
    }
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $db_password = $stmt->get_result()->fetch_assoc()['password'];
    $stmt->close();

    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif (!password_verify($current_password, $db_password)) {
        $error = "Current password is incorrect!";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $change = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $change->bind_param("si", $hashed, $user_id);
        $change->execute();
        $success = "Password changed successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/image/tour-travels-booking-fevicon.svg">
    <title>Admin Profile - Tour and Travels</title>
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
                            <li><a href="/index.php">Visit Site</a></li>
                            <li><a href="/shop.php">Visit Store</a></li>
                        </ul>
                    </li>
                    <li class="admin-comment">
                        <a class="comment-count" href="./comments.php">
                            <i class="fa-regular fa-comment"></i>
                            <?= $conn->query("SELECT COUNT(*) AS total FROM comments")->fetch_assoc()['total'] ?>
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
                    <img src="../assets/image/tour-travels-booking-footer-logo.svg" alt="Logo">
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
                <h1 class="dashboard-title">My Profile</h1>

                <?php if (!empty($success)): ?>
                    <div class="msg success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="msg error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="profile-header">
                    <div class="profile-cover"></div>
                    <div class="profile-user">
                        <form method="POST" enctype="multipart/form-data" id="avatar-upload-form">
                            <label for="avatar-input" class="profile-avatar">
                                <?php if (!empty($user['profile_image']) && file_exists(__DIR__ . "/uploads/" . $user['profile_image'])): ?>
                                    <img src="./uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="<?= htmlspecialchars($user['fullname']) ?>">
                                <?php else: ?>
                                    <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                                <?php endif; ?>
                                <span class="avatar-camera"><i class="fa-solid fa-camera"></i></span>
                            </label>
                            <input type="file" id="avatar-input" name="profile_image" accept="image/*" hidden>
                            <button type="submit" name="upload_image" class="hidden-form">Upload</button>
                        </form>
                        <div>
                            <h2><?= htmlspecialchars($user['fullname']) ?></h2>
                        </div>
                    </div>
                </div>

                <div class="profile-body">
                    <div class="profile-card">
                        <h3><i class="fa-solid fa-user-pen"></i> Account Details</h3>

                        <form method="POST">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>

                    <div class="profile-card">
                        <h3><i class="fa-solid fa-lock"></i> Change Password</h3>

                        <form method="POST">
                            <div class="form-group">
                                <label>Current Password</label>
                                <div class="password-field">
                                    <input type="password" name="current_password" required>
                                    <button type="button" class="eye-btn pw-toggle">
                                        <i class="fa-regular fa-eye eye-open"></i>
                                        <i class="fa-solid fa-eye-slash eye-closed"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <div class="password-field">
                                    <input type="password" name="new_password" required>
                                    <button type="button" class="eye-btn pw-toggle">
                                        <i class="fa-regular fa-eye eye-open"></i>
                                        <i class="fa-solid fa-eye-slash eye-closed"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <div class="password-field">
                                    <input type="password" name="confirm_password" required>
                                    <button type="button" class="eye-btn pw-toggle">
                                        <i class="fa-regular fa-eye eye-open"></i>
                                        <i class="fa-solid fa-eye-slash eye-closed"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>

</html>