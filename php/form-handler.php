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

if ($name === "" || $email === "" || $message === "") {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
    exit;
}

// Where the email goes (THIS is the only line you change)
$recipient = "gtracey24@gmail.com"; // change to your desired receiving email

$subject = "New Contact Form Submission";
$body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

// Additional headers
$headers = "From: form@".$_SERVER['SERVER_NAME']."\r\n";

$headers .= "Reply-To: $email\r\n";

// Send the email
$sent = mail($recipient, $subject, $body, $headers);

if ($sent) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Email failed"]);
}
?>
