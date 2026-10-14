<?php
require_once 'database.php';
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, fullname, password, role FROM users WHERE email = ? AND deleted_at IS NULL");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === "admin") {
            header("Location: /admin/dashboard.php");
            exit;
        } else {
            header("Location: index.php");
            exit;
        }
    } else {
        $message = "Invalid email or password!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour and Travels Booking System</title>
    <link rel="stylesheet" href="./assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class="login">
    <section class="login-form">
        <div class="container">
            <div class="form-box" id="login-form">
                <form method="POST">
                    <h2>Login</h2>
                    <input type="email" name="email" placeholder="Email" required>
                    <div class="password-field">
                        <input id="password" type="password" name="password" placeholder="Password" required>

                        <button type="button" class="eye-btn" id="togglePassword">


                            <i id="eye-open" class="fa-regular fa-eye" style="display: none;"></i>
                            <i id="eye-closed" class="fa-solid fa-eye-slash"></i>

                        </button>
                    </div>
                    <?php if ($message): ?>
                        <p class="incored"><?= htmlspecialchars($message) ?></p>
                    <?php endif; ?>
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                    <p>Don't have an account? <a href="/registration.php" id="show-register">Register</a></p>
                </form>
            </div>
        </div>
    </section>



    <script src="./assets/js/script.js"></script>

</body>

</html>