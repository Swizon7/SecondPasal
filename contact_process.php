<?php

session_start();

include("includes/send_mail.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact.php");
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (
    $name === '' ||
    $email === '' ||
    $subject === '' ||
    $message === ''
) {
    header("Location: contact.php?error=empty");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: contact.php?error=email");
    exit();
}

/*
 * We'll use PHPMailer directly here because this is a contact
 * message, not a password reset email.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();

    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;

    // Use the same Gmail SMTP account
    $mail->Username = "mhrznsuiizon@gmail.com";

    // Use your NEW App Password here.

    $mail->Password = "vualjvbhrportdag";

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom(
        "mhrznsuiizon@gmail.com",
        "SecondPasal Contact Form"
    );

    $mail->addAddress(
        "mhrznsuiizon@gmail.com",
        "SecondPasal Support"
    );

    $mail->addReplyTo(
        $email,
        $name
    );

    $mail->isHTML(true);

    $mail->Subject = "SecondPasal Contact: " . $subject;

    $safeName = htmlspecialchars($name);
    $safeEmail = htmlspecialchars($email);
    $safeSubject = htmlspecialchars($subject);
    $safeMessage = nl2br(htmlspecialchars($message));

    $mail->Body = "
        <h2>New Contact Message</h2>

        <p><strong>Name:</strong> {$safeName}</p>

        <p><strong>Email:</strong> {$safeEmail}</p>

        <p><strong>Subject:</strong> {$safeSubject}</p>

        <p><strong>Message:</strong></p>

        <div style='padding:15px;
                    background:#f5f7f5;
                    border-radius:8px;'>
            {$safeMessage}
        </div>

        <br>

        <p>
            You can reply directly to this email.
        </p>

        <strong>SecondPasal</strong>
    ";

    $mail->AltBody =
        "Name: $name\n" .
        "Email: $email\n" .
        "Subject: $subject\n\n" .
        $message;

    $mail->send();

    header("Location: contact.php?success=1");
    exit();

} catch (Exception $e) {

    error_log("Contact Mail Error: " . $mail->ErrorInfo);

    header("Location: contact.php?error=mail");
    exit();

}