<?php
/* ===== DB CONNECTION ===== */
$conn = new mysqli("localhost", "root", "", "booking-system");
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Package ID");
}
$package_id = (int)$_GET['id'];

if (isset($_POST['submit_comment'])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        print_r($_SESSION);
        die("Please login to comment.");
    }

    $comment_text = trim($_POST['comment']);
    $parent_id    = (int)($_POST['parent_id'] ?? 0);
    $user_id      = (int)$_SESSION['user_id'];

    if ($comment_text !== '') {
        $stmtUser = $conn->prepare("SELECT fullname, email FROM users WHERE id = ?");
        $stmtUser->bind_param("i", $user_id);
        $stmtUser->execute();
        $user = $stmtUser->get_result()->fetch_assoc();
        $stmtUser->close();

        $stmt = $conn->prepare("
            INSERT INTO comments
            (comment_content, comment_parent, comment_date, user_id, comment_package_id, user_fullname, user_email)
            VALUES (?, ?, NOW(), ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "siiiss",
            $comment_text,
            $parent_id,
            $user_id,
            $package_id,
            $user['fullname'],
            $user['email']
        );
        $stmt->execute();
        $stmt->close();
        session_abort();
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

$stmt = $conn->prepare("SELECT * FROM packages WHERE id = ? AND deleted_at IS NULL");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();
$stmt->close();

require_once 'recommend-functions.php';
$recommendations = recommend_similar_packages($conn, $package_id, 4);

include('header.php');
?>

<section class="page-header">
    <div class="container">
        <div class="page-title">
            <h1><?php echo htmlspecialchars($package['title']); ?></h1>
        </div>
    </div>
</section>

<main>
    <section class="info block">
        <div class="container">
            <div class="info-holder">

                <div class="info-left">
                    <img src="/admin/uploads/<?php echo htmlspecialchars($package['image']); ?>" width="400">
                </div>

                <div class="info-right">
                    <h1><?php echo htmlspecialchars($package['title']); ?></h1>

                    <p><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>

                    <p><strong>Price:</strong> <?php echo format_price($package['price'], null, 0); ?></p>

                    <p><strong>Days:</strong> <?php echo (int)$package['days']; ?> Days</p>

                    <a href="add-to-cart.php?package_id=<?php echo $package['id']; ?>" class="btn btn-primary">
                        Add to Cart
                    </a>
                </div>

            </div>
        </div>
    </section>

    <?php if (!empty($recommendations)): ?>
        <section class="block recommendation-section">
            <div class="container">
                <div class="title-wrapper">
                    <span class="subtitle">Recommendation</span>
                    <h2>You May <strong>Also Like</strong></h2>
                    <p>Recommended using collaborative filtering based on what other customers booked together.</p>
                </div>
                <?= render_recommendation_row($recommendations) ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="comment-section block">
        <div class="container">
            <h3>Leave a Comment</h3>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="post">
                    <textarea name="comment" required placeholder="Write your comment..."></textarea>
                    <input type="hidden" name="parent_id" value="0">
                    <button class="btn btn-primary" type="submit" name="submit_comment">Submit</button>
                </form>
            <?php else: ?>
                <p><a href="login.php">Login</a> to comment.</p>
            <?php endif; ?>

            <h3>Comments</h3>
            <div class="comment-wrapper">


                <?php
                $stmt = $conn->prepare("SELECT * FROM comments WHERE comment_package_id = ? AND comment_parent = 0 ORDER BY comment_date DESC");
                $stmt->bind_param("i", $package_id);
                $stmt->execute();
                $comments = $stmt->get_result();

                function displayReplies($conn, $parent_id)
                {
                    $stmtReply = $conn->prepare("SELECT * FROM comments WHERE comment_parent = ? ORDER BY comment_date ASC");
                    $stmtReply->bind_param("i", $parent_id);
                    $stmtReply->execute();
                    $replies = $stmtReply->get_result();
                    while ($reply = $replies->fetch_assoc()):
                        $reply_id = $reply['comment_id'];
                ?>
                        <div class="comment-box">
                            <strong><?php echo htmlspecialchars($reply['user_fullname']); ?></strong><br>
                            <small><?php echo $reply['comment_date']; ?></small>
                            <p><?php echo nl2br(htmlspecialchars($reply['comment_content'])); ?></p>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="post" class="reply-form">
                                    <textarea name="comment" required placeholder="Write reply..."></textarea>
                                    <input type="hidden" name="parent_id" value="<?php echo $reply_id; ?>">
                                    <button type="submit" name="submit_comment" class="btn btn-primary"> Reply</button>
                                </form>
                            <?php endif; ?>

                            <?php displayReplies($conn, $reply_id); ?>
                        </div>
                    <?php
                    endwhile;
                    $stmtReply->close();
                }

                while ($comment = $comments->fetch_assoc()):
                    $comment_id = $comment['comment_id'];
                    ?>
                    <div class="comment-box">
                        <strong><?php echo htmlspecialchars($comment['user_fullname']); ?></strong><br>
                        <small><?php echo $comment['comment_date']; ?></small>
                        <p><?php echo nl2br(htmlspecialchars($comment['comment_content'])); ?></p>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="post" class="reply-form">
                                <textarea name="comment" required placeholder="Write reply..."></textarea>
                                <input type="hidden" name="parent_id" value="<?php echo $comment_id; ?>">
                                <button type="submit" name="submit_comment" class="btn btn-primary"> Reply</button>
                            </form>
                        <?php endif; ?>

                        <?php displayReplies($conn, $comment_id); ?>
                    </div>
                <?php endwhile;
                $stmt->close(); ?>
            </div>
        </div>
    </section>
</main>

<?php include('footer.php'); ?>