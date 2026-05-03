<?php


header("Content-Type: application/json");

// Allow only POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

// Basic validation
$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$message = trim($data["message"] ?? "");
$phone = trim($data["phone"] ?? "");

if ($name === "" || $email === "" || $message === "" || $phone === "") {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit;
}

/* ---------------------------------------------------------
   1) SUBMISSION LOG
   Logs every submission with timestamp, IP, device, and data.
--------------------------------------------------------- */
$log = date("Y-m-d H:i:s") . " | "
     . "Name: $name | "
     . "Email: $email | "
     . "Phone: $phone | "
     . "Message: " . str_replace("\n", " ", $message) . " | "
     . "IP: " . $_SERVER['REMOTE_ADDR'] . " | "
     . "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

file_put_contents(__DIR__ . "/form-log.txt", $log, FILE_APPEND);


// Where the email goes (THIS is the only line you change)
$recipient = "gtracey24@gmail.com";

$subject = "New Contact Form Submission";
$body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message\n";

/* ---------------------------------------------------------
   HEADERS
   Neutral "From" header prevents Gmail/Yahoo/Outlook blocking.
   Reply-To lets the client reply directly to the sender.
--------------------------------------------------------- */
$headers = "From: form@" . $_SERVER['SERVER_NAME'] . "\r\n";
$headers .= "Reply-To: $email\r\n";


/* ---------------------------------------------------------
   MAIN EMAIL SEND
--------------------------------------------------------- */
$sent = mail($recipient, $subject, $body, $headers);


/* ---------------------------------------------------------
   2) BACKUP EMAIL
   Sends a silent copy to you so you always know the form works.
--------------------------------------------------------- */
mail("gtracey@fccwr.com", "[Backup] New Form Submission", $body, $headers);


/* ---------------------------------------------------------
   JSON RESPONSE FOR FRONTEND
--------------------------------------------------------- */
if ($sent) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Email failed"]);
}


/* ---------------------------------------------------------
   3) ERROR LOG
   Logs failed mail() attempts for debugging.
--------------------------------------------------------- */
if (!$sent) {
    file_put_contents(__DIR__ . "/form-errors.txt",
        date("Y-m-d H:i:s") . " | Failed to send from $email\n",
        FILE_APPEND
    );
}

?>
