<?php
require_once 'database.php';
session_start();
?>
<?php

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $message = "Email already exists!";
    } elseif ($password !== $cpassword) {
        $message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = "user";

        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$fullname, $email, $hashed_password, $role]);

        if ($success) {
            $message = "Registration successful! <a href='login.php'>Login</a>";
        } else {
            $message = "Something went wrong!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour and Travels Booking System</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class="login">
    <section class="login-form">
        <div class="container">
            <div class="form-box" id="register-form">
                <form method="POST">
                    <h2>Register</h2>
                    <input type="text" name="fullname" placeholder="Full Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="password" name="cpassword" placeholder="Confirm Password" required>
                    <?php if ($message): ?>

                        <?php echo $message; ?>
                    <?php endif; ?>
                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                    <p>Already have an account? <a href="/login-page.php" id="show-login">Login</a></p>
                </form>
            </div>
        </div>
    </section>
    <script src="./assets/js/script.js"></script>
</body>

</html>