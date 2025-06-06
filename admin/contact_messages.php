<?php
ob_start();  // Start output buffering

include '../config/db.php'; 
include '../includes/header.php'; 
// Admin Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login");
    exit();
}

// Pagination Setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $per_page;

// Search Setup
$search = $_GET['search'] ?? '';
$search_query = "";

if (!empty($search)) {
    $search_query = "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR message LIKE '%$search%'";
}

// Fetch messages with search + pagination
$query = "SELECT * FROM contact_messages $search_query ORDER BY created_at DESC LIMIT $start_from, $per_page";
$result = $conn->query($query);

// Count total records for pagination
$total_query = $conn->query("SELECT COUNT(id) AS total FROM contact_messages $search_query");
$total_row = $total_query->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $per_page);
?>

<div class="container py-5">
  <h2 class="mb-4">📥 Contact Messages</h2>

  <!-- Search Bar -->
  <form class="row mb-4" method="GET">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search messages..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-success w-100">Search</button>
    </div>
  </form>

  <?php if ($result->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-success">
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = $start_from + 1; while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></td>
              <td><?= nl2br(htmlspecialchars(substr($row['message'], 0, 50))) ?>...</td>
              <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
              <td>
                <!-- View Full Message Button -->
                <button class="btn btn-sm btn-primary view-btn" data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>" data-email="<?= htmlspecialchars($row['email']) ?>" data-message="<?= htmlspecialchars($row['message']) ?>" data-date="<?= $row['created_at'] ?>">View</button>
                
                <!-- Delete Button -->
                <a href="delete_contact?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <nav>
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>

  <?php else: ?>
    <div class="alert alert-info">No messages found.</div>
  <?php endif; ?>
</div>

<!-- Modal for Viewing Full Message -->
<div class="modal fade" id="viewMessageModal" tabindex="-1" aria-labelledby="viewMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewMessageModalLabel">Full Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Name:</strong> <span id="modalName"></span></p>
        <p><strong>Email:</strong> <span id="modalEmail"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <hr>
        <p id="modalMessage"></p>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
  $('.view-btn').click(function() {
    $('#modalName').text($(this).data('name'));
    $('#modalEmail').text($(this).data('email'));
    $('#modalDate').text($(this).data('date'));
    $('#modalMessage').text($(this).data('message'));
    $('#viewMessageModal').modal('show');
  });
});
</script>

<?php include '../includes/footer.php'; ?>

<?php ob_end_flush();  // Flush the output buffer ?>
