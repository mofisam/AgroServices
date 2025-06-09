<?php 
include 'includes/header.php'; 
include 'config/db.php'; // Database connection
?>

<?php
// 🧠 Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'config/.env.php'; // Load environment variables

?>

<!-- Hero Section -->
<section class="hero-image position-relative" style="background: url('assets/images/contact_us.jpg') center/cover no-repeat; height: 350px;">
  <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center">
    <h1 class="text-white display-4 fw-bold text-center">Get In Touch</h1>
  </div>
</section>

<!-- Main Contact Section -->
<section class="container py-5">
  <div class="row g-5">
    <!-- Contact Form -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0 p-4">
        <h3 class="mb-4 text-success">Send a Message</h3>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = trim(htmlspecialchars($_POST["name"]));
            $email = trim(htmlspecialchars($_POST["email"]));
            $message = trim(htmlspecialchars($_POST["message"]));

            if (empty($name) || empty($email) || empty($message)) {
                echo '<div class="alert alert-danger">All fields are required.</div>';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo '<div class="alert alert-danger">Please enter a valid email address.</div>';
            } else {
                // Save to database
                $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sss", $name, $email, $message);
                    $stmt->execute();
                    $stmt->close();

                    // Send email to Admin
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = SMTP_HOST; // 🔥 Use your SMTP server (example for Gmail)
                        $mail->SMTPAuth = true;
                        $mail->Username = SMTP_USERNAME; // 🔥 YOUR Gmail
                        $mail->Password = SMTP_PASSWORD; // 🔥 YOUR Gmail App Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = SMTP_PORT;

                        //Recipients
                        $mail->setFrom('info@fandvagroservices.com.ng', 'F and V Agro Services System');
                        $mail->addAddress('info@fandvagroservices.com.ng'); // Admin email

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'New Contact Form Submission';
                        $mail->Body = "
                            <h2>New Message from F and V Agro Services</h2>
                            <p><strong>Name:</strong> {$name}</p>
                            <p><strong>Email:</strong> {$email}</p>
                            <p><strong>Message:</strong><br>{$message}</p>
                            <br>
                            <p>Regards,<br>AgriLink Hub Contact System</p>
                        ";

                        $mail->send();
                        echo '<div class="alert alert-success">Thank you, ' . htmlspecialchars($name) . '! Your message has been sent successfully.</div>';
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">Failed to send your message. Please try again later.</div>';
                }
            }
        }
        ?>

        <form method="POST" action="contact" class="needs-validation" novalidate>
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            <div class="invalid-feedback">Please enter your name.</div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
            <div class="invalid-feedback">Please enter a valid email address.</div>
          </div>

          <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            <div class="invalid-feedback">Please enter your message.</div>
          </div>

          <button type="submit" class="btn btn-success w-100">Send Message</button>
        </form>
      </div>
    </div>

    <!-- Contact Info -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0 p-4 bg-light">
        <h3 class="mb-4 text-success">Contact Info</h3>

        <div class="mb-4">
          <strong>Our Address</strong>
          <p class="mb-0">Ibadan, Nigeria</p>
        </div>

        <div class="mb-4">
          <strong>Email</strong>
          <p class="mb-0"><a href="mailto:info@fandvagroservices.com.ng">info@fandvagroservices.com.ng</a></p>
        </div>

        <div class="mb-4">
          <strong>Phone</strong>
          <p class="mb-0">07037997601</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Google Map -->
<section class="container mb-5">
  <h4 class="text-center mb-3">Find Us</h4>
  <div class="ratio ratio-16x9">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
  </div>
</section>

<!-- Form Validation Script -->
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet"/>

<?php include 'includes/footer.php'; ?>