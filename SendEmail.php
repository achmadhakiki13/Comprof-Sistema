<?php
// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- CONFIGURATION ---
    // Set the recipient email address
    $recipient_email = "achmad.hakiki@sistema.co.id";
    
    // --- FORM DATA COLLECTION ---
    // Sanitize and get the form data. Sanitizing helps prevent security issues.
    $first_name = filter_var(trim($_POST["first_name"]), FILTER_SANITIZE_STRING);
    $last_name = filter_var(trim($_POST["last_name"]), FILTER_SANITIZE_STRING);
    $visitor_email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone = filter_var(trim($_POST["phone"]), FILTER_SANITIZE_STRING);
    $subject = filter_var(trim($_POST["subject"]), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING);
    
    // --- VALIDATION ---
    // Check if required fields are empty or if the email is invalid
    if (empty($first_name) || empty($visitor_email) || empty($message) || !filter_var($visitor_email, FILTER_VALIDATE_EMAIL)) {
        // If validation fails, send a bad request response and stop the script.
        http_response_code(400);
        echo "Please fill out all required fields and provide a valid email address.";
        exit;
    }
    
    // --- EMAIL CONSTRUCTION ---
    // Combine first and last name for the full name
    $full_name = $first_name . " " . $last_name;
    
    // Create the email subject line
    $email_subject = "New Contact Form Submission: " . $subject;
    
    // Create the email body content
    $email_body = "You have received a new message from your website contact form.\n\n";
    $email_body .= "Here are the details:\n\n";
    $email_body .= "Full Name: $full_name\n";
    $email_body .= "Email: $visitor_email\n";
    $email_body .= "Phone: $phone\n\n";
    $email_body .= "Message:\n$message\n";
    
    // --- EMAIL HEADERS ---
    // Set the "From" header to show where the email originated
    $headers = "From: " . $full_name . " <" . $visitor_email . ">\r\n";
    // Set the "Reply-To" header so you can reply directly to the visitor
    $headers .= "Reply-To: " . $visitor_email . "\r\n";
    
    // --- SEND EMAIL ---
    // Use PHP's mail() function to send the email
    if (mail($recipient_email, $email_subject, $email_body, $headers)) {
        // If the email is sent successfully, send a success response.
        http_response_code(200);
        echo "Thank You! Your message has been sent.";
        // Optional: Redirect to a 'thank you' page
        // header("Location: thank_you.html");
    } else {
        // If the email fails to send, send a server error response.
        http_response_code(500);
        echo "Oops! Something went wrong and we couldn't send your message.";
    }

} else {
    // If the script is accessed directly without a POST request, send a method not allowed response.
    http_response_code(403);
    echo "There was a problem with your submission, please try again.";
}
?>
