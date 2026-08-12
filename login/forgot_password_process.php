<?php
date_default_timezone_set('Asia/Kathmandu');
session_start();
include("../db.php");
include("../includes/send_mail.php");

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: forgot_password.php");
    exit();
}

$email = trim($_POST['email']);

$stmt = mysqli_prepare($conn,
"SELECT id, name, email
FROM users
WHERE email=?");

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: forgot_password.php?error=1");
    exit();
}

$user = mysqli_fetch_assoc($result);

$name = $user['name'];

// Generate secure token
$token = bin2hex(random_bytes(32));

// Store only hashed token
$tokenHash = hash("sha256", $token);

// Token expires in 15 minutes
$update = mysqli_prepare($conn,
"UPDATE users
SET
    reset_token=?,
    reset_expires=DATE_ADD(NOW(), INTERVAL 15 MINUTE)
WHERE email=?");

mysqli_stmt_bind_param(
    $update,
    "ss",
    $tokenHash,
    $email
);

mysqli_stmt_execute($update);



// Build reset link
$resetLink = "http://localhost/SecondPasal/login/reset_password.php?token=" . urlencode($token);


// Send email
if (sendResetEmail($email, $name, $resetLink)) {

    header("Location: forgot_password.php?success=1");

} else {

    header("Location: forgot_password.php?error=mail");

}

exit();
?>