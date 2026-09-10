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

$listings = [];

while ($row = mysqli_fetch_assoc($result)) {
    $listings[] = $row;
}

$totalListings = count($listings);
$availableCount = 0;
$soldCount = 0;
$totalViews = 0;

foreach ($listings as $listing) {
    $status = strtolower($listing['status']);

    if ($status === 'available') {
        $availableCount++;
    }

    if ($status === 'sold') {
        $soldCount++;
    }

    $totalViews += (int) $listing['views'];
}

include("../includes/header.php");

?>

<link rel="stylesheet" href="../assets/css/my_listings.css">

<div class="listings-page">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->

    <div class="listings-header">

        <div class="listings-heading">

            <span class="page-label">
                <i class="fa-solid fa-store"></i>
                SELLER CENTER
            </span>

            <h1>My Listings</h1>

            <p>
                Manage and track the products you have posted on SecondPasal.
            </p>

        </div>

        <a href="upload_product.php" class="sell-btn">
            <i class="fa-solid fa-plus"></i>
            Sell New Item
        </a>

    </div>


    <!-- =========================================
         SUCCESS MESSAGES
    ========================================== -->

    <?php if (
        isset($_GET['success']) &&
        $_GET['success'] === 'uploaded'
    ) { ?>

        <div class="success-message">
            <i class="fa-solid fa-circle-check"></i>
            <span>Product uploaded successfully!</span>
        </div>

    <?php } ?>


    <?php if (
        isset($_GET['success']) &&
        $_GET['success'] === 'updated'
    ) { ?>

        <div class="success-message">
            <i class="fa-solid fa-circle-check"></i>
            <span>Product updated successfully!</span>
        </div>

    <?php } ?>


    <?php if (
        isset($_GET['deleted']) &&
        $_GET['deleted'] === 'success'
    ) { ?>

        <div class="success-message">
            <i class="fa-solid fa-circle-check"></i>
            <span>Product deleted successfully!</span>
        </div>

    <?php } ?>


    <!-- =========================================
         STATISTICS
    ========================================== -->

    <?php if ($totalListings > 0) { ?>

        <div class="listing-stats">

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-box"></i>
                </div>

                <div>
                    <span>Total Listings</span>
                    <strong><?php echo $totalListings; ?></strong>
                </div>
            </div>


            <div class="stat-card">
                <div class="stat-icon available-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div>
                    <span>Available</span>
                    <strong><?php echo $availableCount; ?></strong>
                </div>
            </div>


            <div class="stat-card">
                <div class="stat-icon sold-icon">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>

                <div>
                    <span>Sold</span>
                    <strong><?php echo $soldCount; ?></strong>
                </div>
            </div>


            <div class="stat-card">
                <div class="stat-icon views-icon">
                    <i class="fa-solid fa-eye"></i>
                </div>

                <div>
                    <span>Total Views</span>
                    <strong><?php echo $totalViews; ?></strong>
                </div>
            </div>

        </div>

    <?php } ?>


    <!-- =========================================
         LISTINGS
    ========================================== -->

    <?php if ($totalListings > 0) { ?>

        <div class="listing-grid">

            <?php foreach ($listings as $product) { ?>

                <?php

                $image = 'no-image.png';

                if (!empty($product['image'])) {

                    $imagePath = "../uploads/" . $product['image'];

                    if (file_exists($imagePath)) {
                        $image = $product['image'];
                    }
                }

                $status = strtolower(trim($product['status']));

                ?>

                <article class="listing-card">

                    <!-- IMAGE -->

                    <div class="image-container">

                        <a
                            href="product_details.php?id=<?php echo (int) $product['id']; ?>"
                            class="listing-image-link"
                        >

                            <img
                                src="../uploads/<?php echo htmlspecialchars($image); ?>"
                                alt="<?php echo htmlspecialchars($product['title']); ?>"
                            >

                        </a>


                        <span class="status-badge <?php echo htmlspecialchars($status); ?>">

                            <i class="fa-solid
                                <?php
                                echo $status === 'available'
                                    ? 'fa-circle-check'
                                    : 'fa-circle-xmark';
                                ?>">
                            </i>

                            <?php echo htmlspecialchars(ucfirst($status)); ?>

                        </span>

                    </div>


                    <!-- CONTENT -->

                    <div class="listing-content">

                        <div class="category-label">
                            <i class="fa-solid fa-tag"></i>

                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </div>


                        <h2>
                            <?php echo htmlspecialchars($product['title']); ?>
                        </h2>


                        <div class="price">
                            Rs. <?php echo number_format($product['price'], 2); ?>
                        </div>


                        <!-- PRODUCT INFO -->

                        <div class="listing-info">

                            <div>
                                <i class="fa-solid fa-location-dot"></i>
                                <span>
                                    <?php echo htmlspecialchars($product['location']); ?>
                                </span>
                            </div>

                            <div>
                                <i class="fa-solid fa-star"></i>
                                <span>
                                    <?php echo htmlspecialchars($product['condition_type']); ?>
                                </span>
                            </div>

                            <div>
                                <i class="fa-solid fa-eye"></i>
                                <span>
                                    <?php echo (int) $product['views']; ?> views
                                </span>
                            </div>

                        </div>


                        <!-- DATE -->

                        <div class="listing-date">

                            <i class="fa-regular fa-calendar"></i>

                            Listed
                            <?php
                            echo date(
                                "M d, Y",
                                strtotime($product['created_at'])
                            );
                            ?>

                        </div>


                        <!-- ACTIONS -->

                        <div class="listing-actions">

                            <a
                                href="product_details.php?id=<?php echo (int) $product['id']; ?>"
                                class="view-btn"
                            >
                                <i class="fa-solid fa-eye"></i>
                                View
                            </a>


                            <a
                                href="edit_product.php?id=<?php echo (int) $product['id']; ?>"
                                class="edit-btn"
                            >
                                <i class="fa-solid fa-pen"></i>
                                Edit
                            </a>


                            <a
                                href="delete_product.php?id=<?php echo (int) $product['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this product?');"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </a>

                        </div>

                    </div>

                </article>

            <?php } ?>

        </div>

    <?php } else { ?>

        <!-- =========================================
             EMPTY STATE
        ========================================== -->

        <div class="empty-state">

            <div class="empty-icon">
                <i class="fa-solid fa-box-open"></i>
            </div>

            <h2>No Listings Yet</h2>

            <p>
                You haven't posted any products yet.
                Start selling your unused items on SecondPasal.
            </p>

            <a href="upload_product.php" class="sell-btn">

                <i class="fa-solid fa-plus"></i>

                Sell Your First Item

            </a>

        </div>

    <?php } ?>

</div>

<?php include("../includes/footer.php"); ?>