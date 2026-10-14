<?php
include('header.php');

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$q = "";
$packages = [];

if (isset($_GET['q'])) {
    $q = $conn->real_escape_string($_GET['q']);

    $sql = "SELECT * FROM packages 
            WHERE (title LIKE '%$q%' 
            OR description LIKE '%$q%')
            AND deleted_at IS NULL";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }
    }
}
?>

<section class="page-header">
    <div class="container">
        <div class="page-title">
            <h1>Search Packages</h1>
        </div>
    </div>
</section>

<main>
    <section class="search-result block">
        <div class="container">

            <form class="search-form-box" action="" method="GET" autocomplete="off">
                <input type="text" name="q" placeholder="Search packages..."
                    value="<?php echo htmlspecialchars($q); ?>" required>
                <button class="btn btn-primary" type="submit">Search</button>
            </form>

            <?php if ($q != ''): ?>
                <h3 class="result-title">Results for: "<?php echo htmlspecialchars($q); ?>"</h3>

                <div class="package-card-wrapper">
                    <?php if (!empty($packages)): ?>

                        <?php foreach ($packages as $p): ?>
                            <div class="package-card">

                                <div class="card-image">
                                    <a href="single-package.php?id=<?php echo $p['id']; ?>">
                                        <img src="/admin/uploads/<?php echo $p['image']; ?>"
                                            alt="<?php echo $p['title']; ?>">
                                    </a>
                                </div>

                                <div class="card-body">
                                    <h3>
                                        <a href="single-package.php?id=<?php echo $p['id']; ?>">
                                            <?php echo $p['title']; ?>
                                        </a>
                                    </h3>

                                    <div class="meta">
                                        <span class="price">
                                            <?php echo format_price($p['price'], $p['base-currency'], 0); ?>
                                        </span>
                                        <span class="day">
                                            <?php echo $p['days']; ?> days
                                        </span>
                                    </div>

                                    <a href="single-package.php?id=<?php echo $p['id']; ?>"
                                        class="btn btn-primary">
                                        View Details
                                    </a>
                                </div>

                            </div>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <h3>No results found!</h3>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

        </div>
    </section>
</main>

<?php include('footer.php'); ?>