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
function sendVerificationEmail($toEmail, $toName, $verificationLink)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;

        $mail->Username = "mhrznsuiizon@gmail.com";
        $mail->Password = "vualjvbhrportdag";

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;

        $mail->setFrom(
            "mhrznsuiizon@gmail.com",
            "SecondPasal"
        );

        $mail->addAddress(
            $toEmail,
            $toName
        );

        $mail->isHTML(true);

        $mail->Subject =
            "Verify Your SecondPasal Account";

        $safeName =
            htmlspecialchars($toName);

        $safeLink =
            htmlspecialchars($verificationLink);

        $mail->Body = "
            <div style='font-family:Arial,sans-serif;
                        max-width:600px;
                        margin:auto;
                        padding:30px;'>

                <h2 style='color:#355E3B;'>
                    Welcome to SecondPasal!
                </h2>

                <p>
                    Hello <strong>{$safeName}</strong>,
                </p>

                <p>
                    Thank you for creating your SecondPasal account.
                    Please verify your email address to activate your account.
                </p>

                <p style='margin:30px 0;'>

                    <a href='{$safeLink}'
                       style='background:#355E3B;
                              color:#ffffff;
                              padding:14px 22px;
                              text-decoration:none;
                              border-radius:8px;
                              font-weight:bold;'>
                        Verify Email
                    </a>

                </p>

                <p>
                    This verification link will expire in
                    <strong>30 minutes</strong>.
                </p>

                <p>
                    If you did not create this account,
                    you can safely ignore this email.
                </p>

                <br>

                <strong>
                    SecondPasal Team
                </strong>

            </div>
        ";

        $mail->AltBody =
            "Verify your SecondPasal account here:\n"
            . $verificationLink;

        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log(
            "Verification Mail Error: "
            . $mail->ErrorInfo
        );

        return false;
    }
}