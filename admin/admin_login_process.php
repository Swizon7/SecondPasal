<?php

session_start();

include("../db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: admin_login.php");
    exit();

}

$email = trim($_POST['email']);
$password = $_POST['password'];

if ($email === '' || $password === '') {

    header("Location: admin_login.php?error=invalid");
    exit();

}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, password, role
     FROM users
     WHERE email = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $email);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {

    header("Location: admin_login.php?error=invalid");
    exit();

}

$user = mysqli_fetch_assoc($result);

if ($user['role'] !== 'admin') {

    header("Location: admin_login.php?error=unauthorized");
    exit();

}

if (!password_verify($password, $user['password'])) {

    header("Location: admin_login.php?error=invalid");
    exit();

}

// Regenerate session ID after successful login
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

header("Location: dashboard.php");
exit();

?>