<?php
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

    // --- KONFIGURASI API ---
    $api_token = '491dcea09261d1b598a330d6f7bf4ce0';
    $api_url = 'https://send.api.mailtrap.io/api/send';

    // --- Buat body email ---
    $sender_name = trim($firstName . " " . $lastName) ?: "Anonymous";
    $email_body = "You have received a new message from your website contact form.\n\n";
    $email_body .= "Name: " . $sender_name . "\n";
    $email_body .= "Email: " . $from_email . "\n";
    $email_body .= "Phone: " . $phone . "\n";
    $email_body .= "Subject: " . $subject . "\n\n";
    $email_body .= "Message:\n" . $message . "\n";

    // --- Siapkan data untuk dikirim ke API ---
    $postData = [
        'from' => ['email' => 'no-reply@sistema.co.id', 'name' => 'Website Contact Form'],
        'to' => [['email' => 'info@sistema.co.id']],
        'subject' => 'New Contact Form Sistema Website: ' . $subject,
        'text' => $email_body,
        'headers' => [
            'Reply-To' => $from_email
        ]
    ];
    
    // --- Kirim request menggunakan cURL ---
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Periksa response dari API
    if ($http_code == 200) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you! Your message has been sent successfully.'
        ]);
    } else {
        error_log("API Error: HTTP Code " . $http_code . " - Response: " . $response);
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