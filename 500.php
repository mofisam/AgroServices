<?php
http_response_code(500);
include 'includes/header.php';
?>

<div class="container py-5 text-center">
    <h1 class="display-4 text-danger">500</h1>
    <p class="lead">💥 Internal Server Error</p>
    <p>Something went wrong on our end. Please try again later or contact support.</p>

    <a href="index" class="btn btn-primary mt-4">
        🔁 Back to Home
    </a>

    <a href="contact" class="btn btn-outline-secondary mt-4 ms-2">
        🛠 Report This Issue
    </a>

    <div class="mt-5">
        <img src="assets/images/500.svg" alt="Server Error" style="max-width: 320px;">
    </div>
</div>

<?php include 'includes/footer.php'; ?>
