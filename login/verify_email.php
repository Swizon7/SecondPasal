<?php

session_start();

include("../db.php");

if (!isset($_GET['token'])) {
    die("Invalid verification link.");
}

$token = $_GET['token'];

$tokenHash = hash(
    "sha256",
    $token
);

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM users
     WHERE verification_token=?
     AND verification_expires > NOW()
     AND email_verified=0
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $tokenHash
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    die("This verification link is invalid or has expired.");
}

$user = mysqli_fetch_assoc($result);

$userId = $user['id'];

$update = mysqli_prepare(
    $conn,
    "UPDATE users
     SET email_verified=1,
         verification_token=NULL,
         verification_expires=NULL
     WHERE id=?"
);

mysqli_stmt_bind_param(
    $update,
    "i",
    $userId
);

mysqli_stmt_execute($update);

header("Location: login.php?verified=success");
exit();

?>