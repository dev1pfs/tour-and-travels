<?php
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /login-page.php?redirect=my-account.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed");
}

$stmt_total = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$stmt_total->bind_result($total_orders);
$stmt_total->fetch();
$stmt_total->close();

$completed_orders = 0;
$pending_orders = 0;
$cancelled_orders = 0;

$sql_status = "SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? GROUP BY status";
$stmt = $conn->prepare($sql_status);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['status'] == 'Completed') $completed_orders = $row['count'];
    if ($row['status'] == 'Pending')   $pending_orders = $row['count'];
    if ($row['status'] == 'Cancelled') $cancelled_orders = $row['count'];
}
$stmt->close();
?>

<main>
    <section class="page-header">
        <div class="container">
            <div class="page-title">
                <h1>My Account</h1>
            </div>
        </div>
    </section>

    <section class="my-account block">
        <div class="container">
            <div class="my-account-holder" style="display: flex; gap: 20px;">
                <div class="my-account-left">
                    <a href="<?php echo isset($_SESSION['user_id']) ? './my-account.php' : './login-page.php?redirect=my-account.php'; ?>">Dashboard</a>
                    <a href="<?php echo isset($_SESSION['user_id']) ? './orders.php' : './login-page.php?redirect=orders.php'; ?>">Orders</a>
                    <a href="<?php echo isset($_SESSION['user_id']) ? './account.php' : './login-page.php?redirect=account.php'; ?>">Account Details</a>
                </div>

                <div class="my-account-right" style="flex: 3;">
                    <div class="order-dashboard">
                        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h2>
                        <div class="cards">
                            <div class="card">
                                <span>Total Orders</span>
                                <h3><?php echo $total_orders; ?></h3>
                            </div>
                            <div class="card">
                                <span>Completed</span>
                                <h3><?php echo $completed_orders; ?></h3>
                            </div>
                            <div class="card">
                                <span>Pending</span>
                                <h3><?php echo $pending_orders; ?></h3>
                            </div>
                            <div class="card">
                                <span>Cancelled</span>
                                <h3><?php echo $cancelled_orders; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include('footer.php'); ?>