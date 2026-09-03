<?php

session_start();

include("../db.php");
include("../includes/send_mail.php");

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: register.php");
    exit();
}

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];


// ==========================
// BASIC VALIDATION
// ==========================

if (
    empty($name) ||
    empty($email) ||
    empty($phone) ||
    empty($password) ||
    empty($confirm)
) {
    header("Location: register.php?error=Please fill all fields.");
    exit();
}


// ==========================
// EMAIL VALIDATION
// ==========================

// Check valid email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header("Location: register.php?error=Invalid email address.");
    exit();
}

// Allow Gmail only
if (!preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/i', $email)) {

    header("Location: register.php?error=Please use a valid Gmail address.");
    exit();
}


// ==========================
// PASSWORD MATCH
// ==========================

if ($password !== $confirm) {

    header("Location: register.php?error=Passwords do not match.");
    exit();
}


// ==========================
// PASSWORD VALIDATION
// ==========================

if (strlen($password) < 8) {

    header("Location: register.php?error=Password must be at least 8 characters.");
    exit();
}


// ==========================
// CHECK EMAIL EXISTS
// ==========================

$check = mysqli_prepare(
    $conn,
    "SELECT id
     FROM users
     WHERE email=?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $check,
    "s",
    $email
);

mysqli_stmt_execute($check);

$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {

    header("Location: register.php?error=Email already registered.");
    exit();
}


// ==========================
// HASH PASSWORD
// ==========================

$hashed = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// ==========================
// DEFAULT ROLE
// ==========================

$role = "user";


// ==========================
// GENERATE VERIFICATION TOKEN
// ==========================

$verificationToken = bin2hex(
    random_bytes(32)
);


// Store only the hash in database
$verificationHash = hash(
    "sha256",
    $verificationToken
);


// ==========================
// INSERT USER
// ==========================

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users
    (
        name,
        email,
        phone,
        password,
        role,
        email_verified,
        verification_token,
        verification_expires
    )
    VALUES
    (
        ?, ?, ?, ?, ?, 0, ?,
        DATE_ADD(NOW(), INTERVAL 30 MINUTE)
    )"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssssss",
    $name,
    $email,
    $phone,
    $hashed,
    $role,
    $verificationHash
);


if (!mysqli_stmt_execute($stmt)) {

    header("Location: register.php?error=Registration failed.");
    exit();
}


// ==========================
// BUILD VERIFICATION LINK
// ==========================

$verificationLink =
    "http://localhost/SecondPasal/login/verify_email.php?token="
    . urlencode($verificationToken);


// ==========================
// SEND VERIFICATION EMAIL
// ==========================

if (
    sendVerificationEmail(
        $email,
        $name,
        $verificationLink
    )
) {

    header("Location: register.php?success=verification_sent");
    exit();

} else {

    // Remove account if email could not be sent
    $userId = mysqli_insert_id($conn);

    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM users
         WHERE id=?"
    );

    mysqli_stmt_bind_param(
        $delete,
        "i",
        $userId
    );

    mysqli_stmt_execute($delete);

    header("Location: register.php?error=verification_email_failed");
    exit();
}

?>