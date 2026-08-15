<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$action = $_GET['action'];
$productId = (int) $_GET['id'];

if ($action === 'toggle_status') {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT status
         FROM products
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) !== 1) {
        header("Location: products.php?error=1");
        exit();
    }

    $product = mysqli_fetch_assoc($result);

    $newStatus = ($product['status'] === 'available')
        ? 'sold'
        : 'available';

    $update = mysqli_prepare(
        $conn,
        "UPDATE products
         SET status = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $update,
        "si",
        $newStatus,
        $productId
    );

    if (mysqli_stmt_execute($update)) {

        header("Location: products.php?success=status");

    } else {

        header("Location: products.php?error=1");
    }

    exit();
}

if ($action === 'delete') {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT image
         FROM products
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) !== 1) {
        header("Location: products.php?error=1");
        exit();
    }

    $product = mysqli_fetch_assoc($result);

    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM products
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($delete, "i", $productId);

    if (mysqli_stmt_execute($delete)) {

        if (!empty($product['image'])) {

            $imagePath = "../uploads/" . $product['image'];

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        header("Location: products.php?success=deleted");

    } else {

        header("Location: products.php?error=1");
    }

    exit();
}

header("Location: products.php");
exit();