<?php
// ✉️ **email_template.php** - Handles sending emails securely via SMTP (PHPMailer)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 
require_once 'config/.env.php'; // Load environment variables

/**
 * Generates a styled HTML email template.
 *
 * @param string $content The main body content of the email.
 * @return string Formatted HTML for the email.
 */
function emailTemplate($messageBody) {
    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <style>
            .email-container {
                width: 600px;
                margin: 0 auto;
                font-family: Arial, sans-serif;
                color: #333;
                background-color: #f9f9f9;
                padding: 20px;
                border-radius: 8px;
                border: 1px solid #ddd;
            }
            .email-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .email-header img {
                max-width: 150px;
            }
            .email-content {
                margin-bottom: 20px;
            }
            .email-footer {
                text-align: center;
                font-size: 12px;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='email-header'>
                <img src='https://fandvagroservices.com.ng/assets/images/logo.png' alt='F and V Agro Services'>
                <h2>F and V Agro Services</h2>
            </div>
            <div class='email-content'>
                $messageBody
            </div>
            <div class='email-footer'>
                &copy; " . date('Y') . " F and V Agro Services. All rights reserved.<br>
                Need help? Contact us at <a href='mailto:support@fandvagroservices.com.ng'>support@fandvagroservices.com.ng</a>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Send an email using PHPMailer.
 *
 * @param string $recipientEmail The recipient's email address.
 * @param string $subject The subject of the email.
 * @param string $messageBody The HTML content of the email.
 * @param bool $isHTML Indicates if the email should be sent as HTML.
 * @return bool Returns true if sent, otherwise false.
 */
function sendEmail($recipientEmail, $subject, $messageBody, $isHTML = true) {
    // ✅ **SMTP Configuration**
    $mail = new PHPMailer(true);

    try {
        // 📡 **SMTP Server Settings**
        $mail->isSMTP();
        $mail->Host = SMTP_HOST; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;  // Replace with your email
        $mail->Password = SMTP_PASSWORD;   // Replace with your password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        // 🏷️ **Sender & Recipient Info**
        $mail->setFrom('your-email@gmail.com', 'F and V Agro Services');
        $mail->addAddress($recipientEmail);

        // ✏️ **Email Content**
        $mail->isHTML($isHTML);
        $mail->Subject = $subject;
        $mail->Body = emailTemplate($messageBody);

        // ✅ **Send the Email**
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
