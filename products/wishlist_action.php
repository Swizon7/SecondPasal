<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$productId = isset($_GET['product_id'])
    ? (int) $_GET['product_id']
    : 0;

if ($productId <= 0) {
    header("Location: products.php");
    exit();
}


// Make sure product exists
$checkProduct = mysqli_prepare(
    $conn,
    "SELECT id
     FROM products
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $checkProduct,
    "i",
    $productId
);

mysqli_stmt_execute($checkProduct);

$productResult = mysqli_stmt_get_result($checkProduct);

if (mysqli_num_rows($productResult) !== 1) {
    header("Location: products.php");
    exit();
}


// ==========================================
// ADD
// ==========================================

if ($action === 'add') {

    $stmt = mysqli_prepare(
        $conn,
        "INSERT IGNORE INTO wishlist
        (user_id, product_id)
        VALUES (?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $userId,
        $productId
    );

    mysqli_stmt_execute($stmt);

    header(
        "Location: product_details.php?id=" .
        $productId
    );

    exit();
}


// ==========================================
// REMOVE
// ==========================================

if ($action === 'remove') {

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM wishlist
         WHERE user_id = ?
         AND product_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $userId,
        $productId
    );

    mysqli_stmt_execute($stmt);

    header(
        "Location: product_details.php?id=" .
        $productId
    );

    exit();
}


header("Location: product_details.php?id=" . $productId);
exit();