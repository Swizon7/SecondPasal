<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

function sendResetEmail($toEmail, $toName, $resetLink)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;

        $mail->SMTPOptions = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
];

        // Replace these with your Gmail details
        $mail->Username = "mhrznsuiizon@gmail.com";
        $mail->Password = "vualjvbhrportdag";

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("mhrznsuiizon@gmail.com", "SecondPasal");

        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);

        $mail->Subject = "Reset Your SecondPasal Password";

        $mail->Body = "
        <h2>Hello $toName,</h2>

        <p>We received a request to reset your password.</p>

        <p>
            <a href='$resetLink'
            style='background:#355E3B;
            color:white;
            padding:12px 20px;
            text-decoration:none;
            border-radius:6px;'>
            Reset Password
            </a>
        </p>

        <p>This link expires in <b>15 minutes</b>.</p>

        <br>

        <p>SecondPasal Team</p>
        ";

        $mail->AltBody = "Reset your password: $resetLink";


     
        $mail->send();

        return true;

    } 

    
  catch (Exception $e) {
    error_log("Mail Error: " . $mail->ErrorInfo);
    return false;
}

}