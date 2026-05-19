<?php


include('header.php');

$host = "localhost";
$db   = "booking-system";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $email = $message = "";
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            $to = "your_email@example.com";
            $subject = "New Contact Message from $name";
            $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
            $headers = "From: $email";

            if (mail($to, $subject, $body, $headers)) {
                $success = "Your message has been sent successfully!";
                $name = $email = $message = "";
            } else {
                $error = "Failed to send email.";
            }
        } else {
            $error = "Failed to save message. Please try again.";
        }

        $stmt->close();
    }
}

$conn->close();
?>




<main>
    <section class="page-header">
        <div class="container">
            <div class="page-title">
                <h1>Contact</h1>
            </div>
        </div>
    </section>

    <section class="contact-form-section block">
        <div class="container">
            <div class="contact-holder">
                <div class="contact-left">
                    <div class="contact-content">
                        <h2>Need Any Help? Contact us</h2>
                        <?php if ($error) echo "<p class='error'>$error</p>"; ?>

                        <form id="contactForm" method="post" action="">
                            <div class="form-group">
                                <label>Name:</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
                            </div>
                            <div class="form-group">

                                <label>Email:</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                            </div>

                            <div class="form-group">

                                <label>Message:</label>
                                <textarea name="message" rows="6" required><?= htmlspecialchars($message) ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>

                    </div>
                </div>
                <div class="contact-right">
                    <div class="contact-info-wrapper">
                        <div class="contact-info">
                            <div class="info-icon">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div class="info">
                                <h5>Address</h5>
                                <p class="mb-0">Kathmandu, Nepal, Ashon</p>
                            </div>
                        </div>

                        <div class="contact-info">
                            <div class="info-icon">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div class="info">
                                <h5>Phone</h5>
                                <a href="tel:9861888292">+977 9861888292</a>
                            </div>
                        </div>

                        <div class="contact-info">
                            <div class="info-icon">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div class="info">
                                <h5>Email</h5>
                                <a href="mailto:manoj98618@gmail.com">manoj98618@gmail.com</a>
                            </div>
                        </div>
                    </div>
                    <div class="map">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d69541.5136606874!2d85.28493324095915!3d27.709030241454187!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb198a307baabf%3A0xb5137c1bf18db1ea!2sKathmandu%2044600!5e1!3m2!1sen!2snp!4v1748435433497!5m2!1sen!2snp"
                            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>


</main>

<?php include('footer.php'); ?>