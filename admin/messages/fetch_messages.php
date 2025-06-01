<?php
include '../../config/db.php';

$user_id = intval($_GET['user_id']);
$messages = $conn->prepare("
    SELECT * FROM admin_chats 
    WHERE user_id = ? 
    ORDER BY created_at ASC
");
$messages->bind_param("i", $user_id);
$messages->execute();
$res = $messages->get_result();

while ($msg = $res->fetch_assoc()):
?>
    <div class="mb-2">
        <strong class="<?= $msg['sender'] == 'user' ? 'text-primary' : 'text-success' ?>">
            <?= ucfirst($msg['sender']) ?>:
        </strong> <?= nl2br(htmlspecialchars($msg['message'])) ?>
        <div class="small text-muted"><?= date('M d, Y h:i A', strtotime($msg['created_at'])) ?></div>
    </div>
<?php endwhile; ?>