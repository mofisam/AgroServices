<?php
http_response_code(404);
include 'includes/header.php';
?>

<div class="container py-5 text-center">
    <h1 class="display-4 text-danger">404</h1>
    <p class="lead">🚫 Oops! The page you're looking for doesn't exist.</p>
    <p>It might have been removed, renamed, or did not exist in the first place.</p>
    
    <a href="index" class="btn btn-primary mt-4">
        ⬅️ Back to Home
    </a>

    <a href="contact" class="btn btn-outline-secondary mt-4 ms-2">
        ✉️ Report a Problem
    </a>

    <div class="mt-5">
        <img src="assets/images/404.svg" alt="Not Found" style="max-width: 320px;">
    </div>
</div>

<?php include 'includes/footer.php'; ?>