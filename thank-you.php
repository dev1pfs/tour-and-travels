<?php
include('header.php');

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id == 0) {
    echo "<div class='container'><h2>Invalid Order</h2></div>";
    include('footer.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "booking-system");

$sql = "SELECT oi.quantity, oi.price, p.title 
        FROM order_items oi 
        JOIN packages p ON oi.package_id = p.id 
        WHERE oi.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<section class="thank-you block">
    <div class="container">
        <h2 class="successful">✔ Payment Successful!</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Package Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grandTotal = 0;
                while ($item = $result->fetch_assoc()):
                    $subtotal = $item['price'] * $item['quantity'];
                    $grandTotal += $subtotal;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= format_price($item['price']) ?></td>
                        <td><?= format_price($subtotal) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-right">Grand Total</th>
                    <th><?= format_price($grandTotal) ?></th>
                </tr>
            </tfoot>
        </table>

        <div class="btn-thank-you-wrap">
            <a href="index.php" class="btn btn-primary">Book Another Package</a>
            <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
        </div>
    </div>

</section>

<?php
$stmt->close();
$conn->close();
include('footer.php');
?>