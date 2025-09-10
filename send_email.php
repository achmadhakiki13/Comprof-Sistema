<?php
// Impor kelas-kelas PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Sesuaikan path jika nama folder berbeda
require 'PHPMailer-6.10.0\src\Exception.php';
require 'PHPMailer-6.10.0\src\PHPMailer.php';
require 'PHPMailer-6.10.0\src\SMTP.php';

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

    $firstName = isset($_POST['firstName']) ? sanitize_input($_POST['firstName']) : '';
    $lastName  = isset($_POST['lastName']) ? sanitize_input($_POST['lastName']) : '';
    $from_email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone     = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : 'Not provided';
    $subject   = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : 'No Subject';
    $message   = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

    // Validasi dasar
    if (empty($from_email) || empty($message) || !filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields with valid data.']);
        exit;
    }

    // Buat instance PHPMailer
    $mail = new PHPMailer(true);

    try {
        // --- KONFIGURASI SERVER SMTP ---
        $mail->SMTPDebug = 2; // Aktifkan untuk melihat log debug jika ada masalah
        $mail->isSMTP();
        $mail->Host       = 'bkanayari@sistema.co.id'; // Ganti dengan server SMTP hosting Anda (misal: mail.sistema.co.id)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bkanayari@sistema.co.id'; // Ganti dengan alamat email untuk mengirim (misal: no-reply@sistema.co.id)
        $mail->Password   = 'YariSistema05'; // Ganti dengan password email di atas
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Gunakan 'tls' atau 'ssl'
        $mail->Port       = 465; // Port SMTP (biasanya 465 untuk SSL, 587 untuk TLS)

        // --- PENGIRIM & PENERIMA ---
        // Set email pengirim. Sebaiknya sama dengan Username SMTP untuk menghindari filter spam
        $mail->setFrom('no-reply@sistema.co.id', 'Website Contact Form');
        
        // Tambahkan alamat email penerima
        $mail->addAddress('achmad.hakiki@sistema.co.id');

        // Atur agar balasan (Reply-To) mengarah ke email pengisi form
        $sender_name = trim($firstName . " " . $lastName) ?: "Anonymous";
        $mail->addReplyTo($from_email, $sender_name);

        // --- KONTEN EMAIL ---
        $mail->isHTML(false); // Set 'true' jika Anda ingin mengirim email format HTML
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
        // Jika terjadi error, kirim pesan gagal
        echo json_encode([
            'status' => 'error',
            'message' => 'Sorry, something went wrong. Message could not be sent. Mailer Error: ' . $mail->ErrorInfo
        ]);
    }

} else {
    // Jika metode bukan POST
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>