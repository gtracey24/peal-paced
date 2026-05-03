<?php
header("Content-Type: application/json");

// Allow only POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);
file_put_contents(__DIR__ . "/debug.txt", print_r($data, true));


// DEBUG (optional): write raw data
// file_put_contents(__DIR__ . "/debug.txt", print_r($data, true));

// Basic validation
$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$phone = trim($data["phone"] ?? "");
$message = trim($data["message"] ?? "");

// Honeypot check
if (!empty($data["website"])) {
    exit; // bot detected
}

// Timestamp check (must be > 1 second)
if (isset($data["ts"]) && time() - intval($data["ts"]) < 1) {
    exit; // bot detected
}

if ($name === "" || $email === "" || $phone === "" || $message === "") {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit;
}

/* ---------------------------------------------------------
   1) SUBMISSION LOG
--------------------------------------------------------- */
$log = date("Y-m-d H:i:s") . " | "
     . "Name: $name | "
     . "Email: $email | "
     . "Phone: $phone | "
     . "Message: " . str_replace("\n", " ", $message) . " | "
     . "IP: " . $_SERVER['REMOTE_ADDR'] . " | "
     . "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

file_put_contents(__DIR__ . "/form-log.txt", $log, FILE_APPEND);


// Where the email goes
$recipient = "gtracey24@gmail.com";

$subject = "New Contact Form Submission";
$body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message\n";

/* ---------------------------------------------------------
   HEADERS
--------------------------------------------------------- */
$headers = "From: form@" . $_SERVER['SERVER_NAME'] . "\r\n";
$headers .= "Reply-To: $email\r\n";

/* ---------------------------------------------------------
   MAIN EMAIL SEND
--------------------------------------------------------- */
$sent = mail($recipient, $subject, $body, $headers);

/* ---------------------------------------------------------
   2) BACKUP EMAIL
--------------------------------------------------------- */
mail("gtracey@fccwr.com", "[Backup] New Form Submission", $body, $headers);

/* ---------------------------------------------------------
   JSON RESPONSE
--------------------------------------------------------- */
if ($sent) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Email failed"]);
}

/* ---------------------------------------------------------
   3) ERROR LOG
--------------------------------------------------------- */
if (!$sent) {
    file_put_contents(__DIR__ . "/form-errors.txt",
        date("Y-m-d H:i:s") . " | Failed to send from $email\n",
        FILE_APPEND
    );
}

?>
