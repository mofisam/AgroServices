<?php 
session_start();
include '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$selected_user_id = $_GET['user'] ?? null;

// 🧠 Fetch all unique buyers who messaged the seller
$users = $conn->query("
  SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.profile_picture,
    MAX(i.created_at) as last_msg_time,
    SUM(CASE 
        WHEN i.read_status = 'unread' AND i.sender_role = 'buyer' THEN 1 
        ELSE 0 
    END) AS unread_count
  FROM product_inquiries i
  JOIN users u ON u.id = i.user_id
  WHERE i.seller_id = $seller_id
  GROUP BY u.id
  ORDER BY last_msg_time DESC
");


// Business Info
$biz = $conn->query("SELECT ba.business_name FROM business_accounts ba WHERE ba.user_id = $seller_id")->fetch_assoc();
$business_name = $biz['business_name'] ?? "My Business";
?>
<?php include '../../includes/header.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📬 <?= $business_name ?> | Message Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f5f7fa; }
    .inbox { max-height: 75vh; overflow-y: auto; background: #fff; border-radius: 8px; padding: 15px; }
    .message-thread { background: #fff; border-radius: 8px; padding: 15px; min-height: 75vh; }
    .message { padding: 10px 15px; margin: 8px 0; border-radius: 12px; max-width: 80%; word-wrap: break-word; }
    .msg-buyer { background-color: #f1f1f1; }
    .msg-seller { background-color: #d1e7dd; margin-left: auto; text-align: right; }
    .buyer-item a { text-decoration: none; display: block; }
    .buyer-item:hover { background-color: #f0f0f0; cursor: pointer; }
    .buyer-item.active { background-color: #0d6efd; color: #fff; }
    .buyer-item.active a { color: #fff; }
    .avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 10px;
      border: 1px solid #ddd;
    }
    #chatBox { height: 55vh; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 8px; background: #fff; }
    textarea.form-control { resize: none; }
  </style>
</head>
<body >
  <div class="container py-4">
  <h3 class="mb-4">💬 <?= $business_name ?> Inbox</h3>
  <div class="row g-3">

    <!-- LEFT PANEL -->
    <div class="col-md-4">
      <div class="inbox shadow-sm">
        <h5 class="mb-3">🧑 Buyers</h5>
        <ul class="list-group list-group-flush">
          <?php while ($u = $users->fetch_assoc()): ?>
            <?php
              $isActive = ($selected_user_id == $u['id']) ? 'active' : '';
              $fullName = htmlspecialchars($u['first_name'] . ' ' . $u['last_name']);
              $avatar = $u['profile_picture'] && file_exists('../../' . $u['profile_picture'])
                        ? '../../' . htmlspecialchars($u['profile_picture'])
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=0D6EFD&color=fff';
            ?>
            <li class="list-group-item border-0 buyer-item <?= $isActive ?>">
              <a href="?user=<?= $u['id'] ?>">
                <div class="d-flex align-items-center">
                  <img src="<?= $avatar ?>" alt="avatar" class="avatar">
                <div>
                <div class="d-flex justify-content-between align-items-center">
                    <strong><?= $fullName ?></strong>
                    <?php if (!empty($u['unread_count']) && $u['unread_count'] > 0): ?>
                        <span class="badge bg-danger"><?= $u['unread_count'] ?></span>
                    <?php endif; ?>
                </div>

                    <small class="text-muted"><?= date("M j, g:i a", strtotime($u['last_msg_time'])) ?></small>
                  </div>
                </div>
              </a>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="col-md-8">
      <div class="message-thread shadow-sm">
        <?php if ($selected_user_id): ?>
          <h5>Conversation</h5>
          <div id="chatBox" class="mb-3">
            Loading...
          </div>
          <form method="POST" id="chatReplyForm">
            <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">
            <div class="mb-3">
              <textarea name="message" class="form-control" rows="3" placeholder="Type your reply..." required></textarea>
            </div>
            <button class="btn btn-primary">📨 Send Reply</button>
          </form>
        <?php else: ?>
          <p class="text-muted">← Select a user to view the conversation</p>
        <?php endif; ?>
      </div>
    </div>

  </div>
  </div>
  <?php include '../../includes/footer.php'?>

  <?php if ($selected_user_id): ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      const buyerId = <?= json_encode($selected_user_id) ?>;

      function loadMessages() {
        $.get('load_conversation.php', { user_id: buyerId }, function (html) {
          $('#chatBox').html(html);
          $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
        });
      }

      $('#chatReplyForm').on('submit', function(e) {
        e.preventDefault();
        $.post('reply_message.php', $(this).serialize(), function () {
          loadMessages();
          $('#chatReplyForm textarea').val('');
        });
      });

      setInterval(loadMessages, 5000);
      loadMessages();
    </script>
  <?php endif; ?>
</body>
</html>
