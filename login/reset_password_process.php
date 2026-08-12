<?php
date_default_timezone_set('Asia/Kathmandu');

session_start();
include("../db.php");

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}

$token = $_POST['token'];
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];

if ($password != $confirm) {
    die("Passwords do not match.");
}

$tokenHash = hash("sha256", $token);

// Verify token
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

$user = mysqli_fetch_assoc($result);
$userId = $user['id'];

// Hash new password
$newPassword = password_hash($password, PASSWORD_DEFAULT);

// Update password and clear reset token
$update = mysqli_prepare($conn,
"UPDATE users
SET password=?,
    reset_token=NULL,
    reset_expires=NULL
WHERE id=?");

mysqli_stmt_bind_param(
    $update,
    "si",
    $newPassword,
    $userId
);

mysqli_stmt_execute($update);

if (mysqli_stmt_affected_rows($update) > 0) {
    header("Location: login.php?reset=success");
    exit();
} else {
    die("Failed to update password.");
}
?>