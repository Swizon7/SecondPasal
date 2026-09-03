<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];

/* Total listings */
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$totalListings = (int) mysqli_fetch_assoc($result)['total'];


/* Available listings */
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE user_id = ?
     AND status = 'available'"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$availableListings = (int) mysqli_fetch_assoc($result)['total'];


/* Sold listings */
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE user_id = ?
     AND status = 'sold'"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$soldListings = (int) mysqli_fetch_assoc($result)['total'];


/* Total views */
$stmt = mysqli_prepare(
    $conn,
    "SELECT COALESCE(SUM(views),0) AS total
     FROM products
     WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$totalViews = (int) mysqli_fetch_assoc($result)['total'];


/* Recent listings */
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        products.id,
        products.title,
        products.price,
        products.image,
        products.status,
        products.views,
        products.created_at
     FROM products
     WHERE products.user_id = ?
     ORDER BY products.id DESC
     LIMIT 5"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$recentResult = mysqli_stmt_get_result($stmt);

?>

<?php include("../includes/header.php"); ?>

<link rel="stylesheet" href="../assets/css/dashboard.css">

<div class="dashboard-page">

    <div class="dashboard-header">

        <div>

            <h1>
                Welcome,
                <?php echo htmlspecialchars($_SESSION['name']); ?> 👋
            </h1>

            <p>
                Manage your SecondPasal account and listings.
            </p>

        </div>

        <a href="../products/upload_product.php" class="sell-btn">
            <i class="fa-solid fa-plus"></i>
            Sell New Item
        </a>

    </div>


    <!-- Statistics -->

    <div class="dashboard-stats">

        <div class="dashboard-stat">

            <div class="stat-icon">
                <i class="fa-solid fa-box"></i>
            </div>

            <div>
                <h2><?php echo $totalListings; ?></h2>
                <p>My Listings</p>
            </div>

        </div>


        <div class="dashboard-stat">

            <div class="stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div>
                <h2><?php echo $availableListings; ?></h2>
                <p>Available</p>
            </div>

        </div>


        <div class="dashboard-stat">

            <div class="stat-icon">
                <i class="fa-solid fa-tag"></i>
            </div>

            <div>
                <h2><?php echo $soldListings; ?></h2>
                <p>Sold</p>
            </div>

        </div>


        <div class="dashboard-stat">

            <div class="stat-icon">
                <i class="fa-solid fa-eye"></i>
            </div>

            <div>
                <h2><?php echo $totalViews; ?></h2>
                <p>Total Views</p>
            </div>

        </div>

    </div>


    <!-- Quick Actions -->

    <section class="dashboard-section">

        <div class="section-heading">

            <h2>Quick Actions</h2>

        </div>

        <div class="quick-actions">

            <a href="../products/upload_product.php">

                <i class="fa-solid fa-plus"></i>

                <span>Sell Item</span>

            </a>


            <a href="../products/my_listings.php">

                <i class="fa-solid fa-box"></i>

                <span>My Listings</span>

            </a>


            <a href="../profile/profile.php">

                <i class="fa-solid fa-user"></i>

                <span>My Profile</span>

            </a>


            <a href="../contact.php">

                <i class="fa-solid fa-envelope"></i>

                <span>Contact</span>

            </a>

        </div>

    </section>


    <!-- Recent Listings -->

    <section class="dashboard-section">

        <div class="section-heading">

            <h2>Recent Listings</h2>

            <a href="../products/my_listings.php">
                View All
            </a>

        </div>


        <?php if (mysqli_num_rows($recentResult) > 0) { ?>

            <div class="recent-listings">

                <?php while ($product = mysqli_fetch_assoc($recentResult)) { ?>

                    <div class="recent-card">

                        <?php
                        $image = !empty($product['image'])
                            ? $product['image']
                            : 'no-image.png';
                        ?>

                        <img
                            src="../uploads/<?php echo htmlspecialchars($image); ?>"
                            alt="<?php echo htmlspecialchars($product['title']); ?>">


                        <div class="recent-info">

                            <h3>
                                <?php echo htmlspecialchars($product['title']); ?>
                            </h3>

                            <div class="recent-price">
                                Rs. <?php echo number_format($product['price'], 2); ?>
                            </div>

                            <p>
                                <i class="fa-solid fa-eye"></i>
                                <?php echo (int)$product['views']; ?> views
                            </p>

                        </div>


                        <div class="recent-right">

                            <span class="status-badge <?php echo strtolower($product['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($product['status'])); ?>
                            </span>

                            <a
                                href="../products/product_details.php?id=<?php echo $product['id']; ?>">

                                View

                            </a>

                        </div>

                    </div>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="empty-dashboard">

                <i class="fa-solid fa-box-open"></i>

                <h3>No listings yet</h3>

                <p>
                    Start selling your first item on SecondPasal.
                </p>

                <a href="../products/upload_product.php" class="sell-btn">
                    Sell Your First Item
                </a>

            </div>

        <?php } ?>

    </section>

</div>

<?php include("../includes/footer.php"); ?>