<footer class="site-footer">
    <div class="container">
        <div class="footer-wrapper">
            <div class="footer-content">
                <div class="footer-logo">
                    <a href="/index.php">
                        <img src="./assets/image/tour-travels-booking-footer-logo.svg" alt="Footer Logo">
                    </a>
                </div>
                <p>
                    Explore amazing destinations like Kathmandu, Pokhara, Chitwan, and Lumbini with our Tour and Travels Booking System.
                </p>
                <ul class="social-media">
                    <li>
                        <a href="https://www.facebook.com/chetkumari.shrestha.96">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.twitter.com/">
                            <i class="fa-brands fa-twitter"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.instagram.com/manojshrestha401/">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="footer-link ">
                <h6 class="footer-title h3">Quick Link</h6>
                <ul class="footer-list">
                    <li>
                        <a href="/index.php">Home</a>
                    </li>
                    <li>
                        <a href="/about.php">About Us</a>
                    </li>
                    <li>
                        <a href="/destinations-page.php">Destinations</a>
                    </li>
                    <li>
                        <a href="<?php echo isset($_SESSION['user_id']) ? './cart.php' : './login-page.php?redirect=cart.php'; ?>">Shop</a>
                    </li>
                    <li>
                        <a href="/contact-page.php">Contact</a>
                    </li>
                </ul>
            </div>
            <div class="footer-destinations">
                <h6 class="footer-title h3">Top Destinations</h6>
                <ul class="footer-list">
                    <li>
                        <a href="destination-single.php?id=12">Kathmandu</a>
                    </li>
                    <li>
                        <a href="destination-single.php?id=17">Pokhara</a>
                    </li>
                    <li>
                        <a href="destination-single.php?id=14">Lumbini</a>
                    </li>
                    <li>
                        <a href="destination-single.php?id=15">Sindhupalchock</a>
                    </li>
                </ul>
            </div>
            <div class="footer-contact">
                <h6 class="footer-title h3">Contact Us</h6>
                <ul class="footer-list">
                    <li>
                        <p>Nepal, Kathmandu, Ashon</p>
                    </li>
                    <li>
                        <a href="tel:9861888292">+977 9861888292</a>
                    </li>
                    <li>
                        <a href="mailto:manoj98618@gmail.com">manoj98618@gmail.com</a>
                    </li>
                </ul>
            </div>

        </div>
        <div class="copy-right">
            <p>© 2026 Tour and Travels Booking System. All rights reserved.</p>
        </div>
    </div>
</footer>

<div id="scroll-top-bottom">
    <a href="#" id="scroll-top"><i class="fa-solid fa-arrow-up"></i></a>
</div>

<script src="./assets/vendors/swiper/js/swiper-bundle.min.js"></script>
<script src="./assets/js/script.js"></script>
</body>

</html>