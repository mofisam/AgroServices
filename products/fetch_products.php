<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';

// Filters
$search = $_GET['search'] ?? '';
$min = (float)($_GET['min_price'] ?? 0);
$max = (float)($_GET['max_price'] ?? 999999);
$category = $_GET['category'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 8;
$offset = ($page - 1) * $limit;

// Base WHERE clause
$where = "p.status='active' AND p.price BETWEEN ? AND ?";
$params = [$min, $max];
$types = "dd";

if (!empty($search)) {
    $where .= " AND (p.name LIKE ? OR ba.business_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if (!empty($category)) {
    $where .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

// Product query
$query = "
    SELECT p.*, ba.business_name,
        (SELECT ROUND(AVG(r.rating),1) FROM product_reviews r WHERE r.product_id = p.id) AS avg_rating
    FROM products p
    JOIN users u ON u.id = p.seller_id
    JOIN business_accounts ba ON ba.user_id = u.id
    LEFT JOIN product_categories c ON c.id = p.category_id
    WHERE $where
    ORDER BY p.created_at DESC
    LIMIT $limit OFFSET $offset";

// Total count query for pagination
$totalQuery = "
    SELECT COUNT(*) as total
    FROM products p
    JOIN users u ON u.id = p.seller_id
    JOIN business_accounts ba ON ba.user_id = u.id
    LEFT JOIN product_categories c ON c.id = p.category_id
    WHERE $where";

// Execute product query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

// Render product grid
ob_start();
while ($p = $products->fetch_assoc()) {
    echo '<div class="col-md-3 mb-3">';
    include 'components/product_card.php';
    echo '</div>';
}
$grid = ob_get_clean();

// Execute total count query
$totalStmt = $conn->prepare($totalQuery);
$totalStmt->bind_param($types, ...$params); // Reuse same params
$totalStmt->execute();
$totalResult = $totalStmt->get_result()->fetch_assoc();
$total = $totalResult['total'] ?? 0;
$pages = ceil($total / $limit);

// Build pagination
$pagination = '';
if ($pages > 1) {
    $pagination .= '<ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $pages; $i++) {
        $pagination .= "<li class='page-item'><a class='page-link' href='#' data-page='$i'>$i</a></li>";
    }
    $pagination .= '</ul>';
}

// Return JSON
echo json_encode([
    'grid' => $grid,
    'pagination' => $pagination
]);
