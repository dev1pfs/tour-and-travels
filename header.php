<?php
require_once 'database.php';
require_once 'config.php';
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: admin-dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour and Travels</title>
    <link rel="stylesheet" href="/assets/vendors/swiper/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" href="/assets/image/tour-travels-booking-fevicon.svg">
</head>

<body>

    <div class="cursor"></div>
    <!-- <div id="preloader">
        <div class="circle"></div>
        <div class="text">
            <span>T</span>
            <span>O</span>
            <span>U</span>
            <span>R</span>
            <span>T</span>
            <span>R</span>
            <span>A</span>
            <span>V</span>
            <span>E</span>
            <span>L</span>
            <span>S</span>
        </div>
    </div> -->
    <div class="site">
        <header class="site-header">
            <div class="container">
                <div class="header-wrapper">
                    <div class="header-logo">
                        <a href="/index.php">
                            <img src="/assets/image/tour-travels-booking-logo.svg" alt="logo">
                        </a>
                    </div>
                    <div class="navbar-wrapper">
                        <div class="toggle-menu">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <ul class="navbar">
                            <li>
                                <a class="nav-link active" href="/index.php">Home</a>
                            </li>
                            <li>
                                <a class="nav-link" href="/about.php">About Us</a>
                            </li>
                            <li>
                                <a class="nav-link" href="/destinations-page.php">Destinations</a>
                            </li>

                            <li>
                                <a class="nav-link" href="#">Page <i class="fa-solid fa-angle-down"></i></a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="/shop.php">Shop</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo isset($_SESSION['user_id']) ? '/my-account.php' : '/login-page.php?redirect=my-account.php'; ?>">My Account</a>
                                    </li>
                                    <li>
                                        <a href="/contact-page.php">Contact</a>
                                    </li>
                                </ul>
                            </li>


                        </ul>

                        <div class="header-icon-wrap">
                            <div id="search-icon">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>


                            <?php

                            $conn = new mysqli("localhost", "root", "", "booking-system");
                            if ($conn->connect_error) {
                                die("Connection failed");
                            }

                            $cart_count = 0;

                            if (isset($_SESSION['user_id'])) {
                                $user_id = $_SESSION['user_id'];

                                $stmt = $conn->prepare("SELECT SUM(quantity) AS total_items FROM cart_items WHERE user_id = ?");
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result()->fetch_assoc();
                                $cart_count = $result['total_items'] ?? 0;
                                $stmt->close();
                            }
                            ?>

                            <div class="card-icon">
                                <a href="<?php echo isset($_SESSION['user_id']) ? '/cart.php' : '/login-page.php?redirect=cart.php'; ?>">
                                    <i class="fa fa-shopping-cart"></i>
                                    <span><?php echo $cart_count; ?></span>
                                </a>
                            </div>


                        </div>
                        <ul class="navbar">
                            <li class="nav-item dropdown">
                                <?php if (isset($_SESSION['fullname'])): ?>
                                    <?php
                                    $dashboardLink = ($_SESSION['role'] === 'admin')
                                        ? '/admin/dashboard.php'
                                        : '/my-account.php';
                                    $profileLink = ($_SESSION['role'] === 'admin')
                                        ? '/admin/profile.php'
                                        : '/account.php';
                                    ?>
                                    <div class="dropdown">
                                        <a href="<?php echo $dashboardLink; ?>" class="btn btn-primary" id="userMenu">
                                            <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                                        </a>

                                        <ul class="dropdown-menu" aria-labelledby="userMenu">
                                            <li><a class="dropdown-item" href="<?php echo $profileLink; ?>">Profile</a></li>
                                            <li><a class="dropdown-item" href="<?php echo $dashboardLink; ?>">Dashboard</a></li>
                                            <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <a href="/login-page.php" class="btn btn-primary">Sign Up</a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <div id="search-form">
            <div id="closebtn">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <form action="/search.php" method="GET" autocomplete="off">
                <input type="text" id="product-search" name="q" placeholder="Search packages..." required>
                <div class="submit-btn-wrap">
                    <button type="submit"></button>
                </div>
            </form>
        </div>