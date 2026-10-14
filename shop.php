<?php include('header.php'); ?>

<?php
$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$min = (float) ($_GET['min'] ?? 0);
$max = (float) ($_GET['max'] ?? 0);

$where = [];
$params = [];
$types = '';
$where[] = "deleted_at IS NULL";

if ($q !== '') {
    $where[] = "(title LIKE ? OR description LIKE ?)";
    $like = "%" . $q . "%";
    $params[] = $like;
    $types .= 's';
    $params[] = $like;
    $types .= 's';
}

if ($min > 0) {
    $where[] = "price >= ?";
    $params[] = $min;
    $types .= 'd';
}

if ($max > 0) {
    $where[] = "price <= ?";
    $params[] = $max;
    $types .= 'd';
}

$order = 'id DESC';
switch ($sort) {
    case 'price_asc':
        $order = 'price ASC';
        break;
    case 'price_desc':
        $order = 'price DESC';
        break;
    case 'days_asc':
        $order = 'days ASC';
        break;
    case 'days_desc':
        $order = 'days DESC';
        break;
    case 'title':
        $order = 'title ASC';
        break;
}

$sql = "SELECT id, title, price, days, image, `base-currency` FROM packages";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY " . $order;

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query($sql);
}

$packages = [];
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}

$isLoggedIn = isset($_SESSION['user_id']);
?>

<section class="page-header">
    <div class="container">
        <div class="page-title">
            <h1>Shop</h1>
        </div>
    </div>
</section>

<main>
    <section class="search-result block">
        <div class="container">

            <div class="package-card-wrapper">
                <?php if (!empty($packages)): ?>
                    <?php foreach ($packages as $p): ?>
                        <div class="package-card">
                            <div class="card-image">
                                <a href="single-package.php?id=<?php echo (int) $p['id']; ?>">
                                    <img src="/admin/uploads/<?php echo htmlspecialchars($p['image']); ?>"
                                        alt="<?php echo htmlspecialchars($p['title']); ?>">
                                </a>
                            </div>

                            <div class="card-body">
                                <h3>
                                    <a href="single-package.php?id=<?php echo (int) $p['id']; ?>">
                                        <?php echo htmlspecialchars($p['title']); ?>
                                    </a>
                                </h3>

                                <div class="meta">
                                    <span class="price">
                                        <?php echo format_price($p['price'], $p['base-currency'], 0); ?>
                                    </span>
                                    <span class="day">
                                        <?php echo (int) $p['days']; ?> days
                                    </span>
                                </div>

                                <a href="single-package.php?id=<?php echo (int) $p['id']; ?>"
                                    class="btn btn-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <h3>No packages found. Try a different search or filter.</h3>
                <?php endif; ?>
            </div>

        </div>
    </section>
</main>

<?php
$conn->close();
?>

<?php include('footer.php'); ?>