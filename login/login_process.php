<?php

session_start();

include("../db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';


// ==========================
// BASIC VALIDATION
// ==========================

if ($email === '' || $password === '') {
    header("Location: login.php?error=invalid");
    exit();
}


// ==========================
// FIND USER
// ==========================

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        name,
        email,
        phone,
        password,
        role,
        status,
        email_verified
     FROM users
     WHERE email = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $email);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


// ==========================
// USER NOT FOUND
// ==========================

if (mysqli_num_rows($result) !== 1) {
    header("Location: login.php?error=invalid");
    exit();
}

$user = mysqli_fetch_assoc($result);


// ==========================
// CHECK PASSWORD
// ==========================

if (!password_verify($password, $user['password'])) {
    header("Location: login.php?error=invalid");
    exit();
}


// ==========================
// CHECK ACCOUNT STATUS
// ==========================

if (
    isset($user['status']) &&
    strtolower($user['status']) !== 'active'
) {
    header("Location: login.php?error=inactive");
    exit();
}


// ==========================
// CHECK EMAIL VERIFICATION
// ==========================

if ((int)$user['email_verified'] !== 1) {
    header("Location: login.php?error=email_not_verified");
    exit();
}


// ==========================
// LOGIN
// ==========================

session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['phone'] = $user['phone'];
$_SESSION['role'] = $user['role'];


// ==========================
// REDIRECT
// ==========================

header("Location: ../homepage/homepage.php");
exit();

?>