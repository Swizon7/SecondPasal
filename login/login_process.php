<?php
session_start();
include("../db.php");

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    header("Location: login.php?error=empty");
    exit();
}

// Find user by email
$stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email = ?");

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 1) {

    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        header("Location: ../homepage/homepage.php");
        exit();

    } else {

        header("Location: login.php?error=invalid");
        exit();

    }

} else {

    header("Location: login.php?error=invalid");
    exit();

}
?>