<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: my_listings.php");
    exit();
}

$productId = (int) $_GET['id'];

$stmt = mysqli_prepare(
    $conn,
    "UPDATE products
     SET status = 'available'
     WHERE id = ? AND user_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $productId,
    $userId
);

mysqli_stmt_execute($stmt);

header("Location: my_listings.php?success=available");
exit();
?>