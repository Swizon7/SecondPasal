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

    <!-- =========================
         HEADER
    ========================== -->

    <div class="wishlist-header">

        <h1>
            <i class="fa-solid fa-heart"></i>
            My Wishlist
        </h1>

        <p>
            Products you've saved for later.
        </p>

    </div>


    <!-- =========================
         WISHLIST PRODUCTS
    ========================== -->

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <div class="wishlist-grid">

            <?php while ($product = mysqli_fetch_assoc($result)) { ?>

                <div class="wishlist-card">

                    <!-- PRODUCT IMAGE -->

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


                    <!-- PRODUCT CONTENT -->

                    <div class="wishlist-content">

                        <h2>
                            <?php echo htmlspecialchars($product['title']); ?>
                        </h2>


                        <!-- PRICE -->

                        <div class="wishlist-price">

                            Rs.
                            <?php echo number_format($product['price'], 2); ?>

                        </div>


                        <!-- CONDITION -->

                        <p>

                            <strong>
                                Condition:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $product['condition_type']
                            );
                            ?>

                        </p>


                        <!-- LOCATION -->

                        <p>

                            <i class="fa-solid fa-location-dot"></i>

                            <?php
                            echo htmlspecialchars(
                                $product['location']
                            );
                            ?>

                        </p>


                        <!-- SELLER -->

                        <p>

                            <strong>
                                Seller:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $product['seller_name']
                            );
                            ?>

                        </p>


                        <!-- STATUS -->

                        <?php if (
                            isset($product['status']) &&
                            strtolower($product['status']) !== 'available'
                        ) { ?>

                            <p class="wishlist-status">

                                <strong>
                                    Status:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    ucfirst($product['status'])
                                );
                                ?>

                            </p>

                        <?php } ?>


                        <!-- ACTIONS -->

                        <div class="wishlist-actions">

                            <?php if (
                                !isset($product['status']) ||
                                strtolower($product['status']) === 'available'
                            ) { ?>

                                <a
                                    href="product_details.php?id=<?php echo (int) $product['id']; ?>"
                                    class="view-wishlist">

                                    <i class="fa-solid fa-eye"></i>

                                    View Product

                                </a>

                            <?php } else { ?>

                                <span class="view-wishlist disabled">

                                    <i class="fa-solid fa-ban"></i>

                                    Not Available

                                </span>

                            <?php } ?>


                            <a
                                href="wishlist_action.php?action=remove&product_id=<?php echo (int) $product['id']; ?>"
                                class="remove-wishlist"
                                title="Remove from wishlist"
                                onclick="return confirm('Remove this product from your wishlist?');">

                                <i class="fa-solid fa-heart-crack"></i>

                            </a>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>


    <!-- =========================
         EMPTY WISHLIST
    ========================== -->

    <?php } else { ?>

        <div class="empty-wishlist">

            <i class="fa-regular fa-heart"></i>

            <h2>
                Your Wishlist is Empty
            </h2>

            <p>
                Save products you like and come back to them later.
            </p>

            <a href="products.php">

                <i class="fa-solid fa-magnifying-glass"></i>

                Browse Products

            </a>

        </div>

    <?php } ?>

</div>


<?php

mysqli_stmt_close($stmt);

include("../includes/footer.php");

?>