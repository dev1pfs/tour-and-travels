<?php include('header.php'); ?>
<?php
$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM destinations ORDER BY id DESC";
$result = $conn->query($sql);
?>

<section class="page-header">
    <div class="container">
        <div class="page-title">
            <h1>Destinations</h1>
        </div>
    </div>
</section>

<section class="block">
    <div class="container">
        <div class="destinations-card-wrapper">
            <?php if ($result->num_rows > 0) { ?>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="destinations-card">
                        <div class="card-image">
                            <a href="destination-single.php?id=<?php echo $row['id']; ?>">
                                <img src="./admin/uploads/<?php echo htmlspecialchars($row['image']); ?>"
                                    alt="<?php echo htmlspecialchars($row['title']); ?>">
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
    </div>
</section>

<?php
$conn->close();
?>

<?php include('footer.php'); ?>