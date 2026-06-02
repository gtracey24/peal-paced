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

//