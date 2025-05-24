<?php
require_once('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }
    
    // Prepare email content
    $to = $_settings->info('email'); // Use system email
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $email_content = "
    <html>
    <body>
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong><br>$message</p>
    </body>
    </html>
    ";
    
    // Send email
    if (mail($to, "Contact Form: $subject", $email_content, $headers)) {
        echo json_encode(['status' => 'success', 'message' => 'Thank you for your message. We will get back to you soon!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again later.']);
    }
    exit;
}
?> 