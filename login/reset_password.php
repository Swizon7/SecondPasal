<?php
date_default_timezone_set('Asia/Kathmandu');
session_start();
include("../db.php");

if(!isset($_GET['token']))
{
    die("Invalid password reset link.");
}

$token = $_GET['token'];
$tokenHash = hash("sha256", $token);

$stmt = mysqli_prepare($conn,
"SELECT id
FROM users
WHERE reset_token=?
AND reset_expires > NOW()");

mysqli_stmt_bind_param($stmt, "s", $tokenHash);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("This password reset link is invalid.");
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Reset Password</title>

<link rel="stylesheet" href="../assets/css/reset_password.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="reset-container">

<div class="reset-box">

<h2>Create New Password</h2>

<p>Please enter your new password.</p>

<form action="reset_password_process.php" method="POST">

<input
type="hidden"
name="token"
value="<?php echo htmlspecialchars($token); ?>">

<div class="input-box">

<label>New Password</label>

<div class="input-field">

<i class="fa-solid fa-lock"></i>

<input
type="password"
id="password"
name="password"
required>

<i class="fa-solid fa-eye toggle-password"
id="togglePassword"></i>

</div>

</div>

<div class="input-box">

<label>Confirm Password</label>

<div class="input-field">

<i class="fa-solid fa-lock"></i>

<input
type="password"
id="confirm_password"
name="confirm_password"
required>

<i class="fa-solid fa-eye toggle-confirm"
id="toggleConfirm"></i>

</div>

</div>

<button class="reset-btn">

Reset Password

</button>

</form>

</div>

</div>

<script src="../assets/js/reset_password.js"></script>

</body>

</html>