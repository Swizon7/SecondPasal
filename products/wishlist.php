<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        wishlist.id AS wishlist_id,
        products.id,
        products.title,
        products.price,
        products.image,
        products.condition_type,
        products.location,
        products.status,
        users.name AS seller_name
     FROM wishlist
     INNER JOIN products
        ON products.id = wishlist.product_id
     INNER JOIN users
        ON users.id = products.user_id
     WHERE wishlist.user_id = ?
     ORDER BY wishlist.created_at DESC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

include("../includes/header.php");

?>

<link rel="stylesheet" href="../assets/css/wishlist.css">

<div class="wishlist-page">

    <div class="wishlist-header">

        <h1>My Wishlist</h1>

        <p>
            Products you've saved for later.
        </p>

    </div>

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <div class="wishlist-grid">

            <?php while ($product = mysqli_fetch_assoc($result)) { ?>

                <div class="wishlist-card">

                    <div class="wishlist-image">

                        <?php if (!empty($product['image'])) { ?>

                            <img
                                src="../uploads/<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['title']); ?>">

                        <?php } else { ?>

                            <img
                                src="../uploads/no-image.png"
                                alt="No Image">

                        <?php } ?>

                    </div>


                    <div class="wishlist-content">

                        <h2>
                            <?php echo htmlspecialchars($product['title']); ?>
                        </h2>

                        <div class="wishlist-price">
                            Rs. <?php echo number_format($product['price'], 2); ?>
                        </div>

                        <p>
                            <strong>Condition:</strong>
                            <?php echo htmlspecialchars($product['condition_type']); ?>
                        </p>

                        <p>
                            <i class="fa-solid fa-location-dot"></i>
                            <?php echo htmlspecialchars($product['location']); ?>
                        </p>

                        <p>
                            <strong>Seller:</strong>
                            <?php echo htmlspecialchars($product['seller_name']); ?>
                        </p>

                        <div class="wishlist-actions">

                            <a
                                href="product_details.php?id=<?php echo (int)$product['id']; ?>"
                                class="view-wishlist">

                                View Product

                            </a>

                            <a
                                href="wishlist_action.php?action=remove&product_id=<?php echo (int)$product['id']; ?>"
                                class="remove-wishlist"
                                onclick="return confirm('Remove this product from your wishlist?');">

                                <i class="fa-solid fa-heart-crack"></i>

                            </a>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="empty-wishlist">

            <i class="fa-regular fa-heart"></i>

            <h2>Your Wishlist is Empty</h2>

            <p>
                Save products you like and come back to them later.
            </p>

            <a href="products.php">
                Browse Products
            </a>

        </div>

    <?php } ?>

</div>

<?php include("../includes/footer.php"); ?>