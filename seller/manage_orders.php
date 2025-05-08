<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// 🔍 Filter Inputs
$keyword = $_GET['keyword'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

// 🔎 Build Dynamic Query
$conditions = ["p.seller_id = ?"];
$params = [$seller_id];
$types = "i";

if ($keyword) {
    $conditions[] = "(p.name LIKE ? OR o.payment_reference LIKE ? OR CONCAT(o.first_name, ' ', o.last_name) LIKE ?)";
    $kw = "%$keyword%";
    array_push($params, $kw, $kw, $kw);
    $types .= "sss";
}

if ($status_filter) {
    $conditions[] = "o.delivery_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_from && $date_to) {
    $conditions[] = "DATE(o.created_at) BETWEEN ? AND ?";
    array_push($params, $date_from, $date_to);
    $types .= "ss";
}

$sql = "
SELECT 
    oi.*, o.payment_reference, o.created_at AS order_date, o.delivery_status,
    o.first_name, o.last_name, o.email, o.phone, o.shipping_address, o.state,
    p.name AS product_name, o.estimated_delivery_date
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
JOIN products p ON oi.product_id = p.id
WHERE " . implode(" AND ", $conditions) . "
ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();
?>

<div class="container py-5">
  <h2 class="mb-4">📦 Manage Orders</h2>

  <!-- 🔍 Filter Form -->
  <form class="row g-3 mb-4" method="GET">
    <div class="col-md-3">
      <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" class="form-control" placeholder="Search buyer, product, ref">
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select">
        <option value="">All Status</option>
        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="in-transit" <?= $status_filter === 'in-transit' ? 'selected' : '' ?>>In Transit</option>
        <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
      </select>
    </div>
    <div class="col-md-2">
      <input type="date" name="from" value="<?= htmlspecialchars($date_from) ?>" class="form-control">
    </div>
    <div class="col-md-2">
      <input type="date" name="to" value="<?= htmlspecialchars($date_to) ?>" class="form-control">
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary w-100">Apply Filters</button>
    </div>
  </form>

  <?php if ($orders->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-success">
          <tr>
            <th>Ref</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Buyer</th>
            <th>Address</th>
            <th>Order Date</th>
            <th>Delivery Date</th>
            <th>Delivery Status</th>
            <th>Update</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $orders->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['payment_reference']) ?></td>
              <td><?= htmlspecialchars($row['product_name']) ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>₦<?= number_format($row['subtotal']) ?></td>
              <td>
                <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?><br>
                <small><?= htmlspecialchars($row['email']) ?></small><br>
                <small><?= htmlspecialchars($row['phone']) ?></small>
              </td>
              <td>
                <?= htmlspecialchars($row['shipping_address']) ?><br>
                <?= htmlspecialchars($row['state']) ?>
              </td>
              <td><?= date('M d, Y h:i A', strtotime($row['order_date'])) ?></td>
              <td>
                <input type="date" class="form-control form-control-sm" id="delivery-date-<?= $row['order_id'] ?>" 
                       value="<?= $row['estimated_delivery_date'] ?>">
              </td>
              <td><span id="status-<?= $row['id'] ?>" class="badge bg-secondary"><?= ucfirst($row['delivery_status']) ?></span></td>
              <td>
                <select class="form-select form-select-sm mb-2" onchange="updateStatus(<?= $row['order_id'] ?>, this.value)">
                  <option value="">Change</option>
                  <option value="pending">Pending</option>
                  <option value="in-transit">In Transit</option>
                  <option value="delivered">Delivered</option>
                </select>
                <button class="btn btn-primary btn-sm" onclick="setDeliveryDate(<?= $row['order_id'] ?>)">Set Date</button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No orders found for your products.</div>
  <?php endif; ?>
</div>

<script>
function updateStatus(order_id, new_status) {
  if (!new_status) return;
  fetch('update_delivery_status.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'order_id=' + order_id + '&status=' + new_status
  }).then(res => res.text()).then(alert);
}

function setDeliveryDate(order_id) {
  const date = document.getElementById('delivery-date-' + order_id).value;
  fetch('update_delivery_status.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'order_id=' + order_id + '&delivery_date=' + date
  }).then(res => res.text()).then(alert);
}
</script>

<?php include '../includes/footer.php'; ?>
