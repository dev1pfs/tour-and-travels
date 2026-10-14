<?php
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login-page.php?redirect=orders.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "booking-system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['remove_order_id'])) {
    $order_id = (int) $_POST['remove_order_id'];

    $stmt1 = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt1->bind_param("i", $order_id);
    $stmt1->execute();

    $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $stmt2->bind_param("ii", $order_id, $user_id);
    $stmt2->execute();

    echo "<script>window.location.href='orders.php';</script>";
}

$sql = "
    SELECT 
        o.id AS order_id,
        o.created_at AS order_date, 
        o.total_amount,
        (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS total_items
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<main>
    <section class="page-header">
        <div class="container">
            <h1>My Account</h1>
        </div>
    </section>

    <section class="my-account block">
        <div class="container">
            <div class="my-account-holder">
                <div class="my-account-left">
                    <a href="<?php echo isset($_SESSION['user_id']) ? './my-account.php' : './login-page.php?redirect=my-account.php'; ?>">Dashboard</a>
                    <a href="<?php echo isset($_SESSION['user_id']) ? './orders.php' : './login-page.php?redirect=orders.php'; ?>">Orders</a>
                    <a href="<?php echo isset($_SESSION['user_id']) ? './account.php' : './login-page.php?redirect=account.php'; ?>">Account Details</a>
                </div>

                <div class="my-account-right" style="flex: 3;">
                    <div class="orders">
                        <h2>Your Orders</h2>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0) {
                                    $sn = 1;
                                    while ($row = $result->fetch_assoc()) {
                                ?>
                                        <tr>
                                            <td><?php echo $sn++; ?></td>
                                            <td><?php echo date("M j, Y", strtotime($row['order_date'])); ?></td>
                                            <td>
                                                <strong><?php echo format_price($row['total_amount']); ?></strong>
                                                <br><small><?php echo $row['total_items']; ?> item(s)</small>
                                            </td>
                                            <td class="thank-you-btn-wrap">
                                                <a href="thank-you.php?order_id=<?php echo $row['order_id']; ?>" class="view-btn">View</a>

                                                <form method="POST" onsubmit="return confirm('Are you sure you want to remove this order record?');">
                                                    <input type="hidden" name="remove_order_id" value="<?php echo $row['order_id']; ?>">
                                                    <button class="order-remove-btn" type="submit">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="4" style="padding: 20px 0;">No orders found. <a href="index.php">Start booking!</a></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
$stmt->close();
$conn->close();
include('footer.php');
?>