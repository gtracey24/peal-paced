<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ------------------------------------------------------------
// Load secret config
// ------------------------------------------------------------
$config = require __DIR__ . '/secret-config.php';
$smtpUser = $config['smtp_user'];
$smtpPass = $config['smtp_pass'];

// ------------------------------------------------------------
// Read JSON input
// ------------------------------------------------------------
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(["success" => false, "error" => "Invalid JSON"]);
    exit;
}

// ------------------------------------------------------------
// Honeypot check
// ------------------------------------------------------------
if (!empty($input['website'])) {
    echo json_encode(["success" => false, "error" => "Bot detected"]);
    exit;
}

// ------------------------------------------------------------
// Required fields
// ------------------------------------------------------------
$required = ['name', 'email', 'phone', 'message', 'ts'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        echo json_encode(["success" => false, "error" => "Missing fields"]);
        exit;
    }
}

// ------------------------------------------------------------
// Timestamp anti‑bot check
// ------------------------------------------------------------
if (time() - intval($input['ts']) < 2) {
    echo json_encode(["success" => false, "error" => "Too fast"]);
    exit;
}

// ------------------------------------------------------------
// Prepare email content
// ------------------------------------------------------------
$name = htmlspecialchars($input['name']);
$email = htmlspecialchars($input['email']);
$phone = htmlspecialchars($input['phone']);
$message = nl2br(htmlspecialchars($input['message']));

$body = "
<strong>Name:</strong> $name<br>
<strong>Email:</strong> $email<br>
<strong>Phone:</strong> $phone<br><br>
<strong>Message:</strong><br>$message
";

// ------------------------------------------------------------
// Send email to client
// ------------------------------------------------------------
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp-relay.brevo.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // FIXED SENDER
$mail->setFrom('gtracey@thirtyfold.dev', 'Website Contact Form');

// CLIENT RECEIVES THE FORM
$mail->addAddress('pealpacedtc@gmail.com');

// WHEN CLIENT HITS REPLY → IT GOES TO THE VISITOR
$mail->addReplyTo($email);




    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body = $body;

    $mail->send();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Mailer error"]);
    exit;
}

// ------------------------------------------------------------
// Send receipt to user
// ------------------------------------------------------------
$receipt = new PHPMailer(true);

try {
    $receipt->isSMTP();
    $receipt->Host = 'smtp-relay.brevo.com';
    $receipt->SMTPAuth = true;
    $receipt->Username = $smtpUser;
    $receipt->Password = $smtpPass;
    $receipt->SMTPSecure = 'tls';
    $receipt->Port = 587;

    // FIXED SENDER
    $receipt->setFrom('gtracey@thirtyfold.dev', 'JPeal');

    $receipt->addAddress($email);

    $receipt->isHTML(true);
    $receipt->Subject = 'We received your message';
    $receipt->Body = "Thanks $name, we received your message and will get back to you soon.";

    $receipt->send();
} catch (Exception $e) {
    // Receipt failing should NOT block the main success
}

echo json_encode(["success" => true]);
