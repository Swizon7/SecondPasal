<?php

session_start();
include("../db.php");


// ==========================================
// CHECK PRODUCT ID
// ==========================================

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$id = (int) $_GET['id'];

if ($id <= 0) {
    header("Location: products.php");
    exit();
}


// ==========================================
// GET PRODUCT DETAILS FIRST
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        products.*,
        users.name,
        users.phone,
        categories.category_name
     FROM products
     INNER JOIN users
        ON users.id = products.user_id
     INNER JOIN categories
        ON categories.id = products.category_id
     WHERE products.id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die("Product not found.");
}

$product = mysqli_fetch_assoc($result);

// ==========================================
// WISHLIST STATUS
// ==========================================

$isWishlisted = false;

if (isset($_SESSION['user_id'])) {

    $wishlistUserId = (int) $_SESSION['user_id'];

    $wishlistCheck = mysqli_prepare(
        $conn,
        "SELECT id
         FROM wishlist
         WHERE user_id = ?
         AND product_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $wishlistCheck,
        "ii",
        $wishlistUserId,
        $id
    );

    mysqli_stmt_execute($wishlistCheck);

    $wishlistResult = mysqli_stmt_get_result($wishlistCheck);

    if (mysqli_num_rows($wishlistResult) > 0) {
        $isWishlisted = true;
    }

    mysqli_stmt_close($wishlistCheck);
}

// ==========================================
// UNIQUE VIEW COUNT
// ==========================================

// Only logged-in users can create a unique view.
// Seller's own view is NOT counted.

if (
    isset($_SESSION['user_id']) &&
    (int) $_SESSION['user_id'] !== (int) $product['user_id']
) {

    $viewerId = (int) $_SESSION['user_id'];


    // Check whether this user has already viewed this product
    $viewCheck = mysqli_prepare(
        $conn,
        "SELECT id
         FROM product_views
         WHERE product_id = ?
         AND user_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $viewCheck,
        "ii",
        $id,
        $viewerId
    );

    mysqli_stmt_execute($viewCheck);

    $viewResult = mysqli_stmt_get_result($viewCheck);


    // First view by this user
    if (mysqli_num_rows($viewResult) === 0) {

        $insertView = mysqli_prepare(
            $conn,
            "INSERT INTO product_views
            (product_id, user_id)
            VALUES (?, ?)"
        );

        mysqli_stmt_bind_param(
            $insertView,
            "ii",
            $id,
            $viewerId
        );


        // Insert the view record
        if (mysqli_stmt_execute($insertView)) {

            // Increase product view count
            $updateViews = mysqli_prepare(
                $conn,
                "UPDATE products
                 SET views = views + 1
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $updateViews,
                "i",
                $id
            );

            mysqli_stmt_execute($updateViews);

            mysqli_stmt_close($updateViews);
        }

        mysqli_stmt_close($insertView);
    }

    mysqli_stmt_close($viewCheck);
}


// ==========================================
// GET UPDATED VIEW COUNT
// ==========================================

$viewQuery = mysqli_prepare(
    $conn,
    "SELECT views
     FROM products
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $viewQuery,
    "i",
    $id
);

mysqli_stmt_execute($viewQuery);

$viewResult = mysqli_stmt_get_result($viewQuery);

$viewData = mysqli_fetch_assoc($viewResult);

$product['views'] = (int) $viewData['views'];


// ==========================================
// RELATED PRODUCTS
// ==========================================

$related = mysqli_prepare(
    $conn,
    "SELECT
        products.id,
        products.title,
        products.price,
        products.image,
        products.condition_type,
        products.location
     FROM products
     WHERE products.category_id = ?
     AND products.id != ?
     AND products.status = 'available'
     ORDER BY products.created_at DESC
     LIMIT 4"
);

mysqli_stmt_bind_param(
    $related,
    "ii",
    $product['category_id'],
    $id
);

mysqli_stmt_execute($related);

$relatedResult = mysqli_stmt_get_result($related);


// ==========================================
// HEADER
// ==========================================

include("../includes/header.php");

?>

<link rel="stylesheet" href="../assets/css/product_details.css">


<!-- ==========================================
     PRODUCT DETAILS
=========================================== -->

<section class="product-details">

    <!-- LEFT: IMAGE -->

    <div class="left">

        <?php if (!empty($product['image'])) { ?>

            <img
                src="../uploads/<?php echo htmlspecialchars($product['image']); ?>"
                alt="<?php echo htmlspecialchars($product['title']); ?>">

        <?php } else { ?>

            <img
                src="../uploads/no-image.png"
                alt="No Image Available">

        <?php } ?>

    </div>


    <!-- RIGHT: PRODUCT INFORMATION -->

    <div class="right">

        <h1>
            <?php echo htmlspecialchars($product['title']); ?>
        </h1>


        <div class="price">
            Rs. <?php echo number_format($product['price'], 2); ?>
        </div>


        <p>
            <strong>Condition:</strong>
            <?php echo htmlspecialchars($product['condition_type']); ?>
        </p>


        <p>
            <strong>Category:</strong>
            <?php echo htmlspecialchars($product['category_name']); ?>
        </p>


        <p>
            <strong>Location:</strong>
            <?php echo htmlspecialchars($product['location']); ?>
        </p>


        <p>
            <strong>Views:</strong>
            <?php echo (int) $product['views']; ?>
        </p>


        <p>
            <strong>Seller:</strong>
            <?php echo htmlspecialchars($product['name']); ?>
        </p>


        <p>
            <strong>Phone:</strong>
            <?php echo htmlspecialchars($product['phone']); ?>
        </p>


        <!-- =====================================
             MESSAGE / REPORT BUTTONS
        ====================================== -->

        <?php

        // Show buttons only when:
        // 1. User is logged in
        // 2. User is NOT the seller

        if (
            isset($_SESSION['user_id']) &&
            (int) $_SESSION['user_id'] !== (int) $product['user_id']
        ) {

        ?>

            <a
                href="../chat/chat.php?user=<?php echo (int) $product['user_id']; ?>&product=<?php echo (int) $product['id']; ?>"
                class="message-btn">

                <i class="fa-solid fa-comment"></i>
                Message Seller

            </a>
            <?php if (isset($_SESSION['user_id'])) { ?>

    <?php if ($isWishlisted) { ?>

        <a
            href="wishlist_action.php?action=remove&product_id=<?php echo (int)$product['id']; ?>"
            class="wishlist-btn wishlisted">

            <i class="fa-solid fa-heart"></i>
            Remove from Wishlist

        </a>

    <?php } else { ?>

        <a
            href="wishlist_action.php?action=add&product_id=<?php echo (int)$product['id']; ?>"
            class="wishlist-btn">

            <i class="fa-regular fa-heart"></i>
            Add to Wishlist

        </a>

    <?php } ?>

<?php } ?>


            <a
                href="report_product.php?id=<?php echo (int) $product['id']; ?>"
                class="report-btn">

                <i class="fa-solid fa-flag"></i>
                Report Product

            </a>

        <?php } ?>


    </div>

</section>


<!-- ==========================================
     DESCRIPTION
=========================================== -->

<section class="description">

    <h2>Description</h2>

    <p>
        <?php
        echo nl2br(
            htmlspecialchars($product['description'])
        );
        ?>
    </p>

</section>


<!-- ==========================================
     RELATED PRODUCTS
=========================================== -->

<section class="related-products">

    <h2>Related Products</h2>

    <?php if (mysqli_num_rows($relatedResult) > 0) { ?>

        <div class="related-grid">

            <?php while ($relatedProduct = mysqli_fetch_assoc($relatedResult)) { ?>

                <div class="related-card">

                    <?php if (!empty($relatedProduct['image'])) { ?>

                        <img
                            src="../uploads/<?php echo htmlspecialchars($relatedProduct['image']); ?>"
                            alt="<?php echo htmlspecialchars($relatedProduct['title']); ?>">

                    <?php } else { ?>

                        <img
                            src="../uploads/no-image.png"
                            alt="No Image Available">

                    <?php } ?>


                    <div class="related-content">

                        <h3>
                            <?php echo htmlspecialchars($relatedProduct['title']); ?>
                        </h3>


                        <div class="related-price">
                            Rs. <?php echo number_format($relatedProduct['price'], 2); ?>
                        </div>


                        <p class="related-condition">
                            <?php echo htmlspecialchars($relatedProduct['condition_type']); ?>
                        </p>


                        <p class="related-location">

                            <i class="fa-solid fa-location-dot"></i>

                            <?php echo htmlspecialchars($relatedProduct['location']); ?>

                        </p>


                        <a
                            href="product_details.php?id=<?php echo (int) $relatedProduct['id']; ?>"
                            class="related-btn">

                            View Details

                        </a>

                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="no-related">

            <i class="fa-solid fa-box-open"></i>

            <p>
                No related products available.
            </p>

        </div>

    <?php } ?>

</section>


<?php include("../includes/footer.php"); ?>