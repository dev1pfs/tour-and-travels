<?php

include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /login-page.php?redirect=account.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed");
}

$stmt = $conn->prepare("
    SELECT fullname, email, password, role 
    FROM users 
    WHERE id=?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$success = "";
$error = "";

if (isset($_POST['update_profile'])) {

    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);

    if ($fullname === "" || $email === "") {
        $error = "Full Name and Email are required!";
    } else {
        $update = $conn->prepare("
            UPDATE users 
            SET fullname=?, email=? 
            WHERE id=?
        ");
        $update->bind_param("ssi", $fullname, $email, $user_id);
        $update->execute();

        $success = "Account details updated successfully ";
    }
}

if (isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect!";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $change = $conn->prepare("
            UPDATE users 
            SET password=? 
            WHERE id=?
        ");
        $change->bind_param("si", $hashed, $user_id);
        $change->execute();

        $success = "Password changed successfully ";
    }
}
?>




<section class="page-header">
    <div class="container">
        <div class="page-title">
            <h1>My Account</h1>
        </div>
    </div>
</section>


<section class="my-account block">
    <div class="container">
        <div class="my-account-holder">
            <div class="my-account-left">
                <a href="<?php echo isset($_SESSION['user_id']) ? './my-account.php' : './login-page.php?redirect=my-account.php'; ?>">Dashboard</a>
                <a href="<?php echo isset($_SESSION['user_id']) ? './orders.php' : './login-page.php?redirect=orders.php'; ?>">Orders</a>
                <a href="<?php echo isset($_SESSION['user_id']) ? './account.php' : './login-page.php?redirect=account.php'; ?>">Account Details</a>
            </div>
            <div class="my-account-right">
                <div class="account-details">

                    <h2>Account Details</h2>

                    <?php if ($success) echo "<div class='success'>$success</div>"; ?>
                    <?php if ($error)   echo "<div class='error'>$error</div>"; ?>

                    <form class="account-details-form" method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>">
                        </div>

                    </form>


                    <h3>Change Password</h3>

                    <form class="password-change" method="POST">
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
                        <button class="btn btn-primary" name="change_password">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


<?php include('footer.php'); ?>