<?php
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) die("DB Connection Failed: " . $conn->connect_error);

$stmt = $conn->prepare("
    SELECT p.id, p.title, p.price, c.quantity 
    FROM cart_items c 
    JOIN packages p ON p.id = c.package_id 
    WHERE c.user_id=?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cartItems = $stmt->get_result();
$stmt->close();

if ($cartItems->num_rows == 0) {
    echo "<p>Your cart is empty. <a href='index.php'>Go back</a></p>";
    include('footer.php');
    exit;
}

$grandTotal = 0;
$items = [];
while ($row = $cartItems->fetch_assoc()) {
    $row['total'] = $row['price'] * $row['quantity'];
    $grandTotal += $row['total'];
    $items[] = $row;
}
?>

<section class="checkout block">
    <div class="container">
        <h2>Checkout</h2>

        <p><strong>Total Amount: $ <?= number_format($grandTotal, 2) ?></strong></p>

        <div id="paypal-pay"></div>
    </div>
</section>

<script src="https://www.sandbox.paypal.com/sdk/js?client-id=AakA-5sdveEaESHi04fplIZard85hWCg2SyQtqlHSeumCev08-5ZLUxAr3k2UcVNGg_ktk5Nc5G4KsMZ&currency=USD"></script>

<script>
    let amount = <?= number_format($grandTotal, 2, '.', '') ?>;

    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: amount
                    },
                    description: "Booking Payment"
                }],
                application_context: {
                    shipping_preference: "NO_SHIPPING"
                }
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                fetch('paypal-success.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            orderID: data.orderID,
                            payerID: details.payer.payer_id,
                            amount: amount
                        })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            window.location.href = "thank-you.php?order_id=" + res.order_id;
                        } else {
                            alert("Payment succeeded, but failed to create order: " + res.error);
                        }
                    });
            });
        },
        onCancel: function(data) {
            alert("Payment cancelled!");
        },
        onError: function(err) {
            console.error(err);
            alert("Something went wrong with the payment.");
        }
    }).render("#paypal-pay");
</script>

<?php
include('footer.php');
?>