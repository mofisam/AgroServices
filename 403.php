<?php
http_response_code(403);
include 'includes/header.php';
?>

<div class="container py-5 text-center">
    <h1 class="display-4 text-warning">403</h1>
    <p class="lead">🚫 Access Denied</p>
    <p>You do not have permission to access this page or resource.</p>

    <a href="index" class="btn btn-primary mt-4">
        🔙 Go Back to Home
    </a>

    <a href="contact" class="btn btn-outline-danger mt-4 ms-2">
        📩 Contact Support
    </a>

    <div class="mt-5">
        <img src="assets/images/403.svg" alt="Access Denied" style="max-width: 300px;">
    </div>
</div>

<?php include 'includes/footer.php'; ?>
