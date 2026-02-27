<?php

// 1. Collect and sanitize
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

// 2. Required fields
if ($name === '') $errors[] = "Name is required.";
if ($email === '') $errors[] = "Email is required.";
if ($message === '') $errors[] = "Message is required.";

// 3. Email format
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

// 4. Redirect if validation fails
if (!empty($errors)) {
    header("Location: /error.html");
    exit;
}

// 5. Send email
$to = "your-email@example.com";
$subject = "New Contact Form Submission";
$body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
$headers = "From: $email";

mail($to, $subject, $body, $headers);

// 6. Redirect on success
header("Location: /thank-you.html");
exit;