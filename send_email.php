<?php
// Impor kelas-kelas PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Use forward slashes for better server compatibility
require 'PHPMailer-6.10.0/src/Exception.php';
require 'PHPMailer-6.10.0/src/PHPMailer.php';
require 'PHPMailer-6.10.0/src/SMTP.php';

// Set header konten ke JSON
header('Content-Type: application/json');

// Hanya izinkan request metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Ambil dan sanitasi data dari form ---
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $firstName  = isset($_POST['firstName']) ? sanitize_input($_POST['firstName']) : '';
    $lastName   = isset($_POST['lastName']) ? sanitize_input($_POST['lastName']) : '';
    $from_email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone      = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : 'Not provided';
    $subject    = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : 'No Subject';
    $message    = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

    // Validasi dasar
    if (empty($from_email) || empty($message) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields with valid data.']);
        exit;
    }

    // Buat instance PHPMailer
    $mail = new PHPMailer(true);

    try {
        // --- KONFIGURASI SERVER SMTP (UPDATED WITH NEW MAILTRAP INFO) ---
        $mail->SMTPDebug = 0; // Set to 0 for production. Use 2 for temporary debugging.
        $mail->isSMTP();
        $mail->Host       = 'live.smtp.mailtrap.io'; //
        $mail->SMTPAuth   = true;
        $mail->Username   = 'api'; //
        
        // !!! SECURITY WARNING: This is a secret API key. Do not leave it in a public file.
        $mail->Password   = '491dcea0926fd1b598a330d6f7bf4ce0'; //
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use STARTTLS for Port 587
        $mail->Port       = 587; //

        // --- PENGIRIM & PENERIMA ---
        
        // With Mailtrap, you often need to use a verified "From" address.
        // Confirm with your IT admin what email address should be used here.
        $mail->setFrom('no-reply@sistema.co.id', 'Website Contact Form');
        
        // The recipient email address you requested
        $mail->addAddress('info@sistema.co.id');

        // Atur agar balasan (Reply-To) mengarah ke email pengisi form
        $sender_name = trim($firstName . " " . $lastName) ?: "Anonymous";
        $mail->addReplyTo($from_email, $sender_name);

        // --- KONTEN EMAIL ---
        $mail->isHTML(false); 
        $mail->Subject = 'New Contact Form: ' . $subject;

        // Buat body email
        $email_body = "You have received a new message from your website contact form.\n\n";
        $email_body .= "Name: " . $sender_name . "\n";
        $email_body .= "Email: " . $from_email . "\n";
        $email_body .= "Phone: " . $phone . "\n";
        $email_body .= "Subject: " . $subject . "\n\n";
        $email_body .= "Message:\n" . $message . "\n";
        $mail->Body = $email_body;

        // Kirim email
        $mail->send();
        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you! Your message has been sent successfully.'
        ]);

    } catch (Exception $e) {
        // Log the detailed error on the server instead of showing it to the user
        error_log("Mailer Error: " . $mail->ErrorInfo);
        
        // Send a generic, user-friendly error message
        echo json_encode([
            'status' => 'error',
            'message' => 'Sorry, something went wrong. Your message could not be sent.'
        ]);
    }

} else {
    // Jika metode bukan POST
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>