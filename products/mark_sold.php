<?php

session_start();
include("../db.php");

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_Form/login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: my_listings.php?error=invalid");
    exit();
}

$product_id = (int) $_GET['id'];

// Make sure this product belongs to the logged-in seller
$sql = "
    UPDATE products
    SET status = 'sold'
    WHERE id = ?
    AND user_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $product_id,
    $user_id
);

mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {

    header("Location: my_listings.php?success=sold");
    exit();

} else {

    header("Location: my_listings.php?error=failed");
    exit();
}

?>