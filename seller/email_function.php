<?php
function sendEmail($to, $subject, $message) {
    $headers = "From: admin@agroservices.com\r\n";
    $headers .= "Content-Type: text/html\r\n";
    mail($to, $subject, $message, $headers);
}
?>