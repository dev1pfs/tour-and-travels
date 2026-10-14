<?php
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /login-page.php?redirect=cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection Failed");
}

$stmt = $conn->prepare("
    SELECT 
        c.id AS cart_id,
        c.quantity,
        p.title,
        p.price,
        p.days,
        p.image
    FROM cart_items c
    JOIN packages p ON p.id = c.package_id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cartItems = $stmt->get_result();
?>

<section class="page-header">
    <div class="container">
        <div class="page-title">
            <h1>Cart</h1>
        </div>
    </div>
</section>

<main>
    <section class="carts block">
        <div class="container">

            <?php if ($cartItems->num_rows > 0): ?>
                <?php $grandTotal = 0; ?>

                <table border="1" width="100%" cellpadding="10" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Package</th>
                            <th>Days</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($item = $cartItems->fetch_assoc()): ?>
                            <?php
                            $total = $item['price'] * $item['quantity'];
                            $grandTotal += $total;
                            ?>
                            <tr>
                                <td class="cart-tems-img"><img src="/admin/uploads/<?php echo $item['image']; ?>" width="70"></td>
                                <td><?php echo $item['title']; ?></td>
                                <td><?php echo $item['days']; ?> days</td>

                                <td>
                                    <form class="cart-quantity" action="update-cart.php" method="POST">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <button name="action" value="decrease">-</button>
                                        <?php echo $item['quantity']; ?>
                                        <button name="action" value="increase">+</button>
                                    </form>
                                </td>

                                <td><?php echo format_price($item['price'], null, 0); ?></td>
                                <td><?php echo format_price($total, null, 0); ?></td>

                                <td>
                                    <a class="remove-from-cart" href="remove-from-cart.php?id=<?php echo $item['cart_id']; ?>">Remove</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="5" align="right"><strong>Grand Total</strong></td>
                            <td colspan="2"><strong><?php echo format_price($grandTotal, null, 0); ?></strong></td>
                        </tr>
                    </tfoot>

                </table>
                <a href="checkout.php" class="btn btn-primary proceed-btn">Proceed to Checkout</a>

            <?php else: ?>

                <h2 class="cart-empty">Your cart is empty.</h2>
                <div class="package-card-wrapper">
                    <?php
                    $pkg = $conn->query("SELECT * FROM packages WHERE deleted_at IS NULL");
                    while ($row = $pkg->fetch_assoc()):
                    ?>
                        <div class="package-card">
                            <div class="card-image">
                                <a href="single-package.php?id=<?php echo $row['id']; ?>">
                                    <img src="/admin/uploads/<?php echo $row['image']; ?>" width="150"><br>
                                </a>
                            </div>
                            <div class="card-body">
                                <h3>
                                    <a href="single-package.php?id=<?php echo $row['id']; ?>">
                                        <?php echo $row['title']; ?>
                                    </a>
                                </h3>
                                <div class="meta">
                                    <span class="price">
                                        <?php echo format_price($row['price'], $row['base-currency'], 0); ?>
                                    </span>
                                    <span class="day"><?php echo $row['days']; ?> days</span>
                                </div>
                                <a class="btn btn-primary" href="add-to-cart.php?package_id=<?php echo $row['id']; ?>">Add to Cart</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include('footer.php'); ?>