<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my_listings.php");
    exit();
}

$productId = (int) $_GET['id'];
$userId = $_SESSION['user_id'];

// Get the product only if it belongs to the logged-in user
$stmt = mysqli_prepare(
    $conn,
    "SELECT image
     FROM products
     WHERE id = ? AND user_id = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $productId, $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Product not found or you do not have permission to delete it.");
}

$product = mysqli_fetch_assoc($result);

// Delete product from database
$delete = mysqli_prepare(
    $conn,
    "DELETE FROM products
     WHERE id = ? AND user_id = ?"
);

mysqli_stmt_bind_param($delete, "ii", $productId, $userId);

if (mysqli_stmt_execute($delete)) {

    // Delete the product image
    if (!empty($product['image'])) {

        $imagePath = "../uploads/" . $product['image'];

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    header("Location: my_listings.php?deleted=success");
    exit();

} else {

    die("Failed to delete product.");
}
?>