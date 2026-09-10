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
// GET PRODUCT
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

if (!$stmt) {
    die("Database error.");
}

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
// UNIQUE VIEW COUNT
// ==========================================
//
// - Only logged-in users count
// - Seller's own views do not count
// - Same user viewing repeatedly does not increase views
//

if (
    isset($_SESSION['user_id']) &&
    (int) $_SESSION['user_id'] !== (int) $product['user_id']
) {

    $viewerId = (int) $_SESSION['user_id'];

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

        if (mysqli_stmt_execute($insertView)) {

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
// GET UPDATED VIEWS
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
        products.location,
        products.views
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
// IMAGE
// ==========================================

$productImage = "no-image.png";

if (!empty($product['image'])) {

    $imagePath = "../uploads/" . $product['image'];

    if (file_exists($imagePath)) {
        $productImage = $product['image'];
    }
}


// ==========================================
// PRODUCT STATUS
// ==========================================

$productStatus = strtolower(trim($product['status'] ?? 'available'));


// ==========================================
// HEADER
// ==========================================

include("../includes/header.php");

?>

<link rel="stylesheet" href="../assets/css/product_details.css">


<div class="product-page">


    <!-- ==========================================
         BACK LINK
    =========================================== -->

    <div class="product-back">

        <a href="products.php">

            <i class="fa-solid fa-arrow-left"></i>

            Back to Products

        </a>

    </div>


    <!-- ==========================================
         MAIN PRODUCT
    =========================================== -->

    <section class="product-details">


        <!-- IMAGE SIDE -->

<?php
$images = [];

// Get multiple images from product_images table
$imageStmt = mysqli_prepare(
    $conn,
    "SELECT image FROM product_images WHERE product_id = ? ORDER BY id ASC"
);

mysqli_stmt_bind_param($imageStmt, "i", $id);
mysqli_stmt_execute($imageStmt);

$imageResult = mysqli_stmt_get_result($imageStmt);

while ($imageRow = mysqli_fetch_assoc($imageResult)) {
    if (!empty($imageRow['image'])) {
        $images[] = $imageRow['image'];
    }
}

// Fallback to old products.image
if (empty($images) && !empty($product['image'])) {
    $images[] = $product['image'];
}

// Final fallback
if (empty($images)) {
    $images[] = 'no-image.png';
}
?>

<div class="product-gallery">

    <div class="main-image-container">
        <img
            id="mainProductImage"
            src="../uploads/<?php echo htmlspecialchars($images[0]); ?>"
            alt="<?php echo htmlspecialchars($product['title']); ?>"
            class="main-product-image"
        >
    </div>

    <?php if (count($images) > 1) { ?>
        <div class="thumbnail-container">
            <?php foreach ($images as $index => $image) { ?>
                <img
                    src="../uploads/<?php echo htmlspecialchars($image); ?>"
                    alt="Product Image <?php echo $index + 1; ?>"
                    class="product-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                    onclick="changeProductImage(this)"
                >
            <?php } ?>
        </div>
    <?php } ?>

</div>

<script>
function changeProductImage(thumbnail) {
    document.getElementById('mainProductImage').src = thumbnail.src;

    document.querySelectorAll('.product-thumbnail').forEach(function(img) {
        img.classList.remove('active');
    });

    thumbnail.classList.add('active');
}
</script>

        <!-- INFORMATION SIDE -->

        <div class="product-information">


            <div class="product-category">

                <i class="fa-solid fa-tag"></i>

                <?php echo htmlspecialchars($product['category_name']); ?>

            </div>


            <h1>

                <?php echo htmlspecialchars($product['title']); ?>

            </h1>


            <div class="product-price">

                Rs. <?php echo number_format($product['price'], 2); ?>

            </div>


            <!-- Status -->

            <div class="availability">

                <span
                    class="<?php echo $productStatus === 'available'
                        ? 'available'
                        : 'sold'; ?>">

                    <i class="fa-solid fa-circle"></i>

                    <?php echo htmlspecialchars(ucfirst($productStatus)); ?>

                </span>

            </div>


            <!-- Product Information -->

            <div class="product-meta">


                <div class="meta-item">

                    <div class="meta-icon">

                        <i class="fa-solid fa-star"></i>

                    </div>

                    <div>

                        <span>Condition</span>

                        <strong>
                            <?php echo htmlspecialchars($product['condition_type']); ?>
                        </strong>

                    </div>

                </div>


                <div class="meta-item">

                    <div class="meta-icon">

                        <i class="fa-solid fa-location-dot"></i>

                    </div>

                    <div>

                        <span>Location</span>

                        <strong>
                            <?php echo htmlspecialchars($product['location']); ?>
                        </strong>

                    </div>

                </div>


                <div class="meta-item">

                    <div class="meta-icon">

                        <i class="fa-solid fa-eye"></i>

                    </div>

                    <div>

                        <span>Views</span>

                        <strong>
                            <?php echo (int) $product['views']; ?>
                        </strong>

                    </div>

                </div>


                <div class="meta-item">

                    <div class="meta-icon">

                        <i class="fa-solid fa-calendar"></i>

                    </div>

                    <div>

                        <span>Listed</span>

                        <strong>
                            <?php echo date(
                                "M d, Y",
                                strtotime($product['created_at'])
                            ); ?>
                        </strong>

                    </div>

                </div>


            </div>


            <!-- Seller -->

            <div class="seller-card">

                <div class="seller-avatar">

                    <i class="fa-solid fa-user"></i>

                </div>

                <div class="seller-details">

                    <span>Seller</span>

                    <strong>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </strong>

                    <small>

                        <i class="fa-solid fa-phone"></i>

                        <?php echo htmlspecialchars($product['phone']); ?>

                    </small>

                </div>

            </div>


            <!-- Actions -->

            <?php
            if (
                isset($_SESSION['user_id']) &&
                (int) $_SESSION['user_id'] !== (int) $product['user_id']
            ) {
            ?>

                <div class="product-actions">


                    <?php if ($productStatus === 'available') { ?>

                        <a
                            href="../chat/chat.php?user=<?php echo (int) $product['user_id']; ?>&product=<?php echo (int) $product['id']; ?>"
                            class="message-btn">

                            <i class="fa-solid fa-comment"></i>

                            Message Seller

                        </a>

                    <?php } else { ?>

                        <span class="sold-message">

                            <i class="fa-solid fa-circle-xmark"></i>

                            This item has been sold

                        </span>

                    <?php } ?>


                    <?php if ($isWishlisted) { ?>

                        <a
                            href="wishlist_action.php?action=remove&product_id=<?php echo (int) $product['id']; ?>"
                            class="wishlist-btn wishlisted">

                            <i class="fa-solid fa-heart"></i>

                            Saved

                        </a>

                    <?php } else { ?>

                        <a
                            href="wishlist_action.php?action=add&product_id=<?php echo (int) $product['id']; ?>"
                            class="wishlist-btn">

                            <i class="fa-regular fa-heart"></i>

                            Save

                        </a>

                    <?php } ?>


                    <a
                        href="report_product.php?id=<?php echo (int) $product['id']; ?>"
                        class="report-btn"
                        title="Report Product">

                        <i class="fa-solid fa-flag"></i>

                    </a>

                </div>

            <?php } ?>


        </div>

    </section>


    <!-- ==========================================
         DESCRIPTION
    =========================================== -->

    <section class="description-section">

        <div class="section-heading">

            <span>Product Information</span>

            <h2>Description</h2>

        </div>

        <div class="description-content">

            <?php if (!empty($product['description'])) { ?>

                <p>
                    <?php
                    echo nl2br(
                        htmlspecialchars($product['description'])
                    );
                    ?>
                </p>

            <?php } else { ?>

                <p class="empty-description">
                    The seller has not provided a description.
                </p>

            <?php } ?>

        </div>

    </section>


    <!-- ==========================================
         RELATED PRODUCTS
    =========================================== -->

    <section class="related-products">

        <div class="related-heading">

            <div>

                <span>YOU MAY ALSO LIKE</span>

                <h2>Related Products</h2>

            </div>

            <a href="products.php?category=<?php echo (int) $product['category_id']; ?>">

                View All

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>


        <?php if (mysqli_num_rows($relatedResult) > 0) { ?>

            <div class="related-grid">

                <?php while ($relatedProduct = mysqli_fetch_assoc($relatedResult)) { ?>


                    <?php

                    $relatedImage = "no-image.png";

                    if (!empty($relatedProduct['image'])) {

                        $relatedPath =
                            "../uploads/" . $relatedProduct['image'];

                        if (file_exists($relatedPath)) {
                            $relatedImage = $relatedProduct['image'];
                        }
                    }

                    ?>


                    <article class="related-card">


                        <a
                            href="product_details.php?id=<?php echo (int) $relatedProduct['id']; ?>"
                            class="related-image">

                            <img
                                src="../uploads/<?php echo htmlspecialchars($relatedImage); ?>"
                                alt="<?php echo htmlspecialchars($relatedProduct['title']); ?>">

                            <span>

                                <?php
                                echo htmlspecialchars(
                                    $relatedProduct['condition_type']
                                );
                                ?>

                            </span>

                        </a>


                        <div class="related-content">

                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $relatedProduct['title']
                                );
                                ?>

                            </h3>


                            <div class="related-price">

                                Rs.
                                <?php
                                echo number_format(
                                    $relatedProduct['price'],
                                    2
                                );
                                ?>

                            </div>


                            <p>

                                <i class="fa-solid fa-location-dot"></i>

                                <?php
                                echo htmlspecialchars(
                                    $relatedProduct['location']
                                );
                                ?>

                            </p>


                            <div class="related-footer">

                                <span>

                                    <i class="fa-solid fa-eye"></i>

                                    <?php
                                    echo (int) $relatedProduct['views'];
                                    ?>

                                </span>


                                <a
                                    href="product_details.php?id=<?php echo (int) $relatedProduct['id']; ?>">

                                    View

                                    <i class="fa-solid fa-arrow-right"></i>

                                </a>

                            </div>

                        </div>

                    </article>


                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="no-related">

                <i class="fa-solid fa-box-open"></i>

                <h3>No Related Products</h3>

                <p>
                    There are no other products in this category yet.
                </p>

            </div>

        <?php } ?>

    </section>


</div>


<?php include("../includes/footer.php"); ?>