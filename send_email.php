<?php
// Set the content type to application/json for AJAX requests
header('Content-Type: application/json');

// Basic security check: Only allow POST requests.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- CONFIGURATION ---
    $recipient_email = "info@sistema.co.id"; // The email address where you want to receive messages.
    $email_subject   = "New Contact Form Submission"; // The subject of the email.

    // --- FORM DATA VALIDATION & SANITIZATION ---
    
    // Function to sanitize input data
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Get and sanitize form fields.
    $firstName = isset($_POST['firstName']) ? sanitize_input($_POST['firstName']) : '';
    $lastName  = isset($_POST['lastName']) ? sanitize_input($_POST['lastName']) : '';
    $from_email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone     = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : 'Not provided';
    $subject   = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : 'No Subject';
    $message   = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

    // Basic validation: Check if required fields are empty.
    if (empty($from_email) || empty($message)) {
        // Send an error response back to the form.
        echo json_encode([
            'status' => 'error',
            'message' => 'Please fill in all required fields (Email and Message).'
        ]);
        exit; // Stop the script.
    }

    // Advanced validation: Check for a valid email format.
    if (!filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
        // Send an error response back to the form.
        echo json_encode([
            'status' => 'error',
            'message' => 'Please enter a valid email address.'
        ]);
        exit; // Stop the script.
    }

    // --- EMAIL CONSTRUCTION ---

    // Combine first and last name
    $sender_name = trim($firstName . " " . $lastName);
    if (empty($sender_name)) {
        $sender_name = "Anonymous";
    }

    // Create the email body.
    $email_body = "You have received a new message from your website contact form.\n\n";
    $email_body .= "Here are the details:\n\n";
    $email_body .= "Name: " . $sender_name . "\n";
    $email_body .= "Email: " . $from_email . "\n";
    $email_body .= "Phone: " . $phone . "\n";
    $email_body .= "Subject: " . $subject . "\n\n";
    $email_body .= "Message:\n" . $message . "\n";

    // Create the email headers.
    // This tells the email client who the email is from.
    $headers = "From: " . $sender_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // --- SEND EMAIL ---

    // Use the mail() function to send the email.
    if (mail($recipient_email, $email_subject, $email_body, $headers)) {
        // If the email is sent successfully, send a success response.
        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you! Your message has been sent successfully.'
        ]);
    } else {
        // If the email fails to send, send an error response.
        echo json_encode([
            'status' => 'error',
            'message' => 'Sorry, something went wrong and we could not send your message.'
        ]);
    }

} else {
    // If the request method is not POST, send an error.
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>
