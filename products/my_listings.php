<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        products.id,
        products.title,
        products.price,
        products.location,
        products.image,
        products.condition_type,
        products.status,
        products.views,
        products.created_at,
        categories.category_name
     FROM products
     JOIN categories
       ON categories.id = products.category_id
     WHERE products.user_id = ?
     ORDER BY products.created_at DESC"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

include("../includes/header.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>My Listings | SecondPasal</title>

    <link rel="stylesheet" href="../assets/css/my_listings.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="listings-page">

    <div class="listings-header">

        <div>
            <h1>My Listings</h1>
            <p>Manage the products you have posted on SecondPasal.</p>
        </div>

        <a href="upload_product.php" class="sell-btn">
            <i class="fa-solid fa-plus"></i>
            Sell New Item
        </a>

    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'uploaded') { ?>

        <div class="success-message">
            <i class="fa-solid fa-circle-check"></i>
            Product uploaded successfully!
        </div>

    <?php } ?>
    <?php if (isset($_GET['success']) && $_GET['success'] === 'updated') { ?>

    <div class="success-message">
        <i class="fa-solid fa-circle-check"></i>
        Product updated successfully!
    </div>

<?php } ?>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success') { ?>

        <div class="success-message">
            <i class="fa-solid fa-circle-check"></i>
            Product deleted successfully!
        </div>

    <?php } ?>

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <div class="listing-grid">

            <?php while ($product = mysqli_fetch_assoc($result)) { ?>

                <div class="listing-card">

                    <div class="image-container">

                        <?php
                        $image = !empty($product['image'])
                            ? $product['image']
                            : 'no-image.png';
                        ?>

                        <img
                            src="../uploads/<?php echo htmlspecialchars($image); ?>"
                            alt="<?php echo htmlspecialchars($product['title']); ?>">

                        <span class="status-badge <?php echo strtolower($product['status']); ?>">
                            <?php echo htmlspecialchars(ucfirst($product['status'])); ?>
                        </span>

                    </div>

                    <div class="listing-content">

                        <h2>
                            <?php echo htmlspecialchars($product['title']); ?>
                        </h2>

                        <div class="price">
                            Rs. <?php echo number_format($product['price'], 2); ?>
                        </div>

                        <p>
                            <i class="fa-solid fa-location-dot"></i>
                            <?php echo htmlspecialchars($product['location']); ?>
                        </p>

                        <p>
                            <i class="fa-solid fa-tag"></i>
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </p>

                        <p>
                            <i class="fa-solid fa-star"></i>
                            <?php echo htmlspecialchars($product['condition_type']); ?>
                        </p>

                        <p>
                            <i class="fa-solid fa-eye"></i>
                            <?php echo (int)$product['views']; ?> views
                        </p>

                        <div class="listing-actions">

                            <a
                                href="product_details.php?id=<?php echo $product['id']; ?>"
                                class="view-btn">
                                View
                            </a>

                            <a
                                href="edit_product.php?id=<?php echo $product['id']; ?>"
                                class="edit-btn">
                                Edit
                            </a>

                            <a
                                href="delete_product.php?id=<?php echo $product['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this product?');">
                                Delete
                            </a>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="empty-state">

            <i class="fa-solid fa-box-open"></i>

            <h2>No Listings Yet</h2>

            <p>
                You haven't posted any products yet.
            </p>

            <a href="upload_product.php" class="sell-btn">
                <i class="fa-solid fa-plus"></i>
                Sell Your First Item
            </a>

        </div>

    <?php } ?>

</div>

</body>

</html>