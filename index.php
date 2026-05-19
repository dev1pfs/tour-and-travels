<?php include('header.php'); ?>

<?php
$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM destinations ORDER BY id DESC";
$result = $conn->query($sql);
?>



<main>

    <!-- banner start  -->
    <section class="banner">
        <div class="swiper swiperbanner">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="bg-image">
                        <img src="./assets/image/banner-image1_1.jpg" alt="Kathmandu">
                    </div>
                    <div class="banner-wrapper">
                        <div class="container">
                            <div class="banner-content">
                                <h1>Explore Beautiful Destinations</h1>
                                <p>Discover amazing tours and adventures crafted just for your perfect trip.</p>
                                <a href="/cart.php" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="bg-image">
                        <img src="./assets/image/banner-image1_2.jpg" alt="Kathmandu">
                    </div>
                    <div class="banner-wrapper">
                        <div class="container">
                            <div class="banner-content">
                                <h1>Adventure Awaits</h1>
                                <p>Plan your next trip and experience unforgettable moments along the way.</p>
                                <a href="/cart.php" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="bg-image">
                        <img src="./assets/image/banner-image1_3.jpg" alt="Kathmandu">

                    </div>
                    <div class="banner-wrapper">
                        <div class="container">
                            <div class="banner-content">
                                <h1>Relax and Unwind</h1>
                                <p>Enjoy luxurious stays at top destinations around the world with complete comfort.</p>
                                <a href="/cart.php" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </section>
    <!-- banner end  -->


    <!-- popular destination start -->
    <section class="popular-destination block">
        <div class="container">
            <div class="popular-destination-content title-wrapper">
                <span class="subtitle">Popular Destination</span>
                <h2>Popular <strong>Destinations</strong></h2>
            </div>
            <div class="destinations-card-wrapper">
                <?php if ($result->num_rows > 0) { ?>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <div class="destinations-card">
                            <div class="card-image">
                                <a href="destination-single.php?id=<?php echo $row['id']; ?>">
                                    <img src="./admin/uploads/<?php echo htmlspecialchars($row['image']); ?>"
                                        alt="<?php echo htmlspecialchars($row['title']); ?>">

                                    <img src="./admin/" alt="">
                                </a>
                            </div>
                            <div class="card-body">
                                <h3>
                                    <a href="destination-single.php?id=<?php echo $row['id']; ?>">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </a>
                                </h3>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>No destinations found.</p>
                <?php } ?>
            </div>

            <div class="view-btn-wrap">
                <a class="btn btn-primary" href="/destinations-page.php">View All</a>
            </div>

        </div>
    </section>
    <!-- popular destination end  -->


    <!-- package start  -->
    <section class="package block">
        <div class="container">
            <div class="package-contents title-wrapper">
                <span class="subtitle">Explore our Tours</span>
                <h2>Popular <strong>Tour Packages</strong></h2>
            </div>

            <div class="package-card-wrapper">
                <?php
                $sql = "SELECT * FROM packages ORDER BY id DESC";
                $result = $conn->query($sql);
                ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <?php
                    // Check login (user OR admin)
                    if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
                        $link = "single-package.php?id=" . $row['id'];
                    } else {
                        // redirect to login page
                        $link = "login-page.php";
                    }
                    ?>

                    <div class="package-card">
                        <div class="card-image">
                            <a href="<?php echo $link; ?>">
                                <img src="/admin/uploads/<?php echo $row['image']; ?>" alt="Package Image">
                            </a>
                        </div>

                        <div class="card-body">
                            <h3>
                                <a href="<?php echo $link; ?>">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </a>
                            </h3>

                            <div class="meta">
                                <span class="price">
                                    <?php echo $row['base-currency']; ?>
                                    <?php echo $row['price']; ?>
                                </span>
                                <span class="day"><?php echo $row['days']; ?> days</span>
                            </div>

                            <!-- BOOK NOW BUTTON -->
                            <a href="<?php echo $link; ?>" class="btn btn-primary">
                                Book now
                            </a>
                        </div>
                    </div>

                <?php endwhile; ?>
            </div>

        </div>
    </section>
    <!-- package end -->







</main>
<?php include('footer.php'); ?>