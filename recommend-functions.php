<?php

function popular_packages($conn, $limit = 4)
{
    $count = 0;
    $r = $conn->query("SELECT COUNT(*) AS c FROM order_items");
    if ($r) {
        $count = (int) $r->fetch_assoc()['c'];
    }

    if ($count === 0) {
        $stmt = $conn->prepare("SELECT id, title, image, price, days, `base-currency` AS currency FROM packages WHERE deleted_at IS NULL ORDER BY id DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $row['score'] = 0;
            $rows[] = $row;
        }
        $result->free();
        $stmt->close();
        return $rows;
    }

    $stmt = $conn->prepare("
        SELECT p.id, p.title, p.image, p.price, p.days, p.`base-currency` AS currency, COUNT(oi.id) AS score
        FROM order_items oi
        INNER JOIN packages p ON p.id = oi.package_id
        GROUP BY p.id, p.title, p.image, p.price, p.days, p.`base-currency`
        ORDER BY score DESC, p.price DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $result->free();
    $stmt->close();

    return $rows;
}

function recommend_similar_packages($conn, $package_id, $limit = 4)
{
    $stmt = $conn->prepare("
        SELECT oi2.package_id AS id, p.title, p.image, p.price, p.days, p.`base-currency` AS currency, COUNT(*) AS score
        FROM order_items oi1
        INNER JOIN order_items oi2 ON oi1.order_id = oi2.order_id AND oi1.package_id <> oi2.package_id
        INNER JOIN packages p ON p.id = oi2.package_id
        WHERE oi1.package_id = ?
        GROUP BY oi2.package_id, p.title, p.image, p.price, p.days, p.`base-currency`
        ORDER BY score DESC, p.price DESC
        LIMIT ?
    ");
    $stmt->bind_param("ii", $package_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $result->free();
    $stmt->close();

    if (count($rows) === 0) {
        $rows = popular_packages($conn, $limit + 2);
    }

    $rows = array_values(array_filter($rows, function ($row) use ($package_id) {
        return (int) $row['id'] !== $package_id;
    }));

    return array_slice($rows, 0, $limit);
}

function render_recommendation_row($rows)
{
    if (empty($rows)) {
        return '';
    }

    $html = '<div class="package-card-wrapper">';
    foreach ($rows as $row) {
        $title = htmlspecialchars($row['title']);
        $img = '/admin/uploads/' . htmlspecialchars($row['image']);
        $link = 'single-package.php?id=' . (int) $row['id'];
        $price = format_price($row['price'], $row['currency'], 0);
        $days = (int) $row['days'];

        $html .= '<div class="package-card">
            <div class="card-image">
                <a href="' . $link . '"><img src="' . $img . '" alt="' . $title . '"></a>
            </div>
            <div class="card-body">
                <h3><a href="' . $link . '">' . $title . '</a></h3>
                <div class="meta">
                    <span class="price">' . $price . '</span>
                    <span class="day">' . $days . ' days</span>
                </div>
                <a href="' . $link . '" class="btn btn-primary">Book now</a>
            </div>
        </div>';
    }
    $html .= '</div>';

    return $html;
}