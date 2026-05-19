<?php include('header.php'); ?>

<?php
$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("Destination ID not specified.");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM destinations WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Destination not found.");
}

$destination = $result->fetch_assoc();
?>

<section class="page-header">
    <div class="container">
        <div class="page-title">
            <h1><?php echo htmlspecialchars($destination['title']); ?></h1>
        </div>
    </div>
</section>

<main>

    <section class="destination-detail block">
        <div class="container">
            <div class="destination-detail-content">
                <img src="./admin/uploads/<?php echo htmlspecialchars($destination['image']); ?>" alt="<?php echo htmlspecialchars($destination['title']); ?>">
                <p><?php echo nl2br(htmlspecialchars($destination['description'])); ?></p>
            </div>
        </div>
    </section>
</main>

<?php include('footer.php'); ?>