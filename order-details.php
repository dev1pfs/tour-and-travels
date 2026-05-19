<?php
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /login-page.php?redirect=order-details.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$order_id = intval($_GET['order_id']);

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_sql = $conn->prepare("
    SELECT id, full_name, email, phone, address, total_amount, order_date
    FROM orders
    WHERE id = ? AND user_id = ?
");
$order_sql->bind_param("ii", $order_id, $user_id);
$order_sql->execute();
$order = $order_sql->get_result()->fetch_assoc();



$item_stmt = $conn->prepare("
    SELECT 
        p.title,
        oi.quantity,
        oi.price
    FROM order_items oi
    JOIN packages p ON oi.package_id = p.id
    WHERE oi.order_id = ?
");
$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$items = $item_stmt->get_result();

$subtotal = 0;
?>




<section class="page-header">
    <div class="container">
        <div class="page-title">
            <h1>My Account</h1>
        </div>
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
            <div class="my-account-right">
                <div class="order-details">
                    <?php
                    if (!$order) {
                        echo "<h3>No order found.</h3>";
                        exit();
                    }
                    ?>

                    <h2 class="booking-title">Order Details</h2>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                    <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>

                    <table border="1" cellpadding="10" cellspacing="0" class="booking-table">
                        <thead>
                            <tr>
                                <th>Package</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php while ($item = $items->fetch_assoc()):
                                $line_total = $item['price'] * $item['quantity'];
                                $subtotal += $line_total;
                            ?>
                                <tr>
                                    <td><?php echo $item['title']; ?> × <?php echo $item['quantity']; ?></td>
                                    <td>$ <?php echo number_format($line_total, 2); ?></td>
                                </tr>
                            <?php endwhile; ?>

                            <tr>
                                <td class="label">Subtotal:</td>
                                <td>$ <?php echo number_format($subtotal, 2); ?></td>
                            </tr>

                            <tr>
                                <td class="label">Total:</td>
                                <td><strong>$ <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>



<?php include('footer.php'); ?>