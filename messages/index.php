<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login");
    exit();
}
include_once '../includes/tracking.php';

$user_id = $_SESSION['user_id'];
$selected_seller_id = $_GET['seller'] ?? null;

// Get sellers that buyer has messaged
$sellers = $conn->query("
  SELECT 
    u.id,
    u.profile_picture,
    ba.business_name,
    MAX(i.created_at) AS last_msg_time,
    SUM(CASE WHEN i.read_status = 'unread' AND i.sender_role = 'seller' THEN 1 ELSE 0 END) AS unread_count
  FROM product_inquiries i
  JOIN users u ON u.id = i.seller_id
  JOIN business_accounts ba ON ba.user_id = u.id
  WHERE i.user_id = $user_id
  GROUP BY u.id
  ORDER BY last_msg_time DESC
");
?>

<?php
$page_title = "messages - F and V Agro Services";
$page_description = "Learn more about F and V Agro Services – our mission, values, and team.";
$page_keywords = "Agro Services, About, Farming, Agriculture";
include '../includes/header.php';
?>

<div class="container py-4">
  <h3 class="mb-4">💬 My Conversations</h3>
  <div class="row g-3">

    <!-- LEFT PANEL: Sellers -->
    <div class="col-md-4">
      <div class="inbox shadow-sm">
        <h5 class="mb-3">📦 Businesses</h5>
        <ul class="list-group list-group-flush">
          <?php while ($s = $sellers->fetch_assoc()): ?>
            <?php
              $isActive = ($selected_seller_id == $s['id']) ? 'active' : '';
              $businessName = htmlspecialchars($s['business_name']);
              $avatar = $s['profile_picture'] && file_exists('../' . $s['profile_picture'])
                ? '../' . htmlspecialchars($s['profile_picture'])
                : 'https://ui-avatars.com/api/?name=' . urlencode($businessName) . '&background=0D6EFD&color=fff';
            ?>
            <li class="list-group-item border-0 <?= $isActive ?>">
              <a href="?seller=<?= $s['id'] ?>" class="text-decoration-none <?= $isActive ? 'text-white' : '' ?>">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center">
                    <img src="<?= $avatar ?>" class="avatar" alt="Seller">
                    <div>
                      <strong><?= $businessName ?></strong><br>
                      <small class="text-muted"><?= date("M j, g:i a", strtotime($s['last_msg_time'])) ?></small>
                    </div>
                  </div>
                  <?php if ($s['unread_count'] > 0): ?>
                    <span class="badge bg-danger"><?= $s['unread_count'] ?></span>
                  <?php endif; ?>
                </div>
              </a>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    </div>

    <!-- RIGHT PANEL: Conversation -->
    <div class="col-md-8">
      <div class="message-thread shadow-sm">
        <?php if ($selected_seller_id): ?>
          <h5>Conversation</h5>
          <div id="chatBox" class="mb-3">Loading...</div>

          <!-- Reply form -->
          <form method="POST" id="chatReplyForm">
            <input type="hidden" name="seller_id" value="<?= $selected_seller_id ?>">
            <div class="mb-3">
              <textarea name="message" class="form-control" rows="3" placeholder="Type your reply..." required></textarea>
            </div>
            <button class="btn btn-primary">📨 Send Reply</button>
          </form>
        <?php else: ?>
          <p class="text-muted">← Select a business to view the conversation</p>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
<?php if ($selected_seller_id): ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const sellerId = <?= json_encode($selected_seller_id) ?>;

function loadMessages() {
  $.get('load_user_conversation.php', { seller_id: sellerId }, function (html) {
    $('#chatBox').html(html);
    $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
  });
}

$('#chatReplyForm').on('submit', function(e) {
  e.preventDefault();
  $.post('reply_user_message.php', $(this).serialize(), function () {
    loadMessages();
    $('#chatReplyForm textarea').val('');
  });
});

setInterval(loadMessages, 5000);
loadMessages();
</script>
<?php endif; ?>
<?php include('../includes/footer.php'); ?>