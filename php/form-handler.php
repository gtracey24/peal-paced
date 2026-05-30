<?php
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ------------------------------------------------------------
// LOAD SECRET CONFIG HERE
// ------------------------------------------------------------
$config = require __DIR__ . '/secret-config.php';
$smtpUser = $config['smtp_user'];
$smtpPass = $config['smtp_pass'];

// ------------------------------------------------------------
// 1. Read JSON input
// ------------------------------------------------------------
$input = json_decode(file_get_contents('php://input'), true);

$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');
$phone   = trim($input['phone'] ?? '');
$message = trim($input['message'] ?? '');
$website = trim($input['website'] ?? '');
$ts      = intval($input['ts'] ?? 0);

// ------------------------------------------------------------
// 2. Basic validation
// ------------------------------------------------------------
if ($website !== '') {
    echo json_encode(['success' => false, 'error' => 'Bot detected']);
    exit;
}

if (!$name || !$email || !$message) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

if (time() - $ts < 3) {
    echo json_encode(['success' => false, 'error' => 'Too fast']);
    exit;
}

// ------------------------------------------------------------
// 3. Prepare PHPMailer
// ------------------------------------------------------------
$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'a9bac6001@smtp-brevo.com'; // Your Brevo SMTP login
    $mail->Password   = '2R4MKd0GfD09WICj';         // Your Brevo SMTP key
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender
    $mail->setFrom('gtracey@thirtyfold.dev', 'Website Contact Form');

    // ------------------------------------------------------------
    // 4. Primary email to JPeal
    // ------------------------------------------------------------
    $mail->addAddress('jpeal@bethalto.org');

    $mail->Subject = "New Contact Form Submission from $name";
    $mail->Body    =
        "Name: $name\n" .
        "Email: $email\n" .
        "Phone: $phone\n\n" .
        "Message:\n$message\n\n" .
        "Timestamp: " . date('Y-m-d H:i:s');

    $mail->send();

    // ------------------------------------------------------------
    // 5. Send receipt to Grant (no message body)
    // ------------------------------------------------------------
    $receipt = new PHPMailer(true);
    $receipt->isSMTP();
    $receipt->Host       = 'smtp-relay.brevo.com';
    $receipt->SMTPAuth   = true;

    $receipt->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $receipt->Port       = 587;

    $receipt->setFrom('gtracey@thirtyfold.dev', 'Website Contact Form');
    $receipt->addAddress('gtracey@thirtyfold.dev');

    $receipt->Subject = "New Submission Received";
    $receipt->Body    = "A new form submission was received from $name ($email).";

    $receipt->send();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/form-errors.txt', $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Email failed']);
}
