<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];


/* =========================================================
   TOTAL LISTINGS
========================================================= */

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

mysqli_stmt_close($stmt);


/* =========================================================
   AVAILABLE LISTINGS
========================================================= */

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

mysqli_stmt_close($stmt);


/* =========================================================
   SOLD LISTINGS
========================================================= */

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

mysqli_stmt_close($stmt);


/* =========================================================
   TOTAL VIEWS
========================================================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT COALESCE(SUM(views), 0) AS total
     FROM products
     WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$totalViews = (int) mysqli_fetch_assoc($result)['total'];

mysqli_stmt_close($stmt);


/* =========================================================
   RECENT LISTINGS
========================================================= */

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


/* =========================================================
   GRAPH DATA
========================================================= */

$graphLabels = [];
$graphViews = [];

$recentProducts = [];

while ($graphProduct = mysqli_fetch_assoc($recentResult)) {

    $recentProducts[] = $graphProduct;

    $graphLabels[] = $graphProduct['title'];

    $graphViews[] = (int) $graphProduct['views'];
}


/* =========================================================
   HEADER
========================================================= */

include("../includes/header.php");

?>

<!-- Dashboard CSS -->

<link rel="stylesheet"
      href="../assets/css/dashboard.css">


<!-- Chart.js -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<div class="dashboard-page">


    <!-- =====================================================
         DASHBOARD HEADER
    ====================================================== -->

    <div class="dashboard-header">

        <div>

            <h1>

                Welcome,
                <?php echo htmlspecialchars($_SESSION['name']); ?>

                👋

            </h1>

            <p>
                Manage your SecondPasal account and listings.
            </p>

        </div>


        <a
            href="../products/upload_product.php"
            class="sell-btn">

            <i class="fa-solid fa-plus"></i>

            Sell New Item

        </a>

    </div>



    <!-- =====================================================
         STATISTICS
    ====================================================== -->

    <div class="dashboard-stats">


        <!-- TOTAL LISTINGS -->

        <div class="dashboard-stat">

            <div class="stat-icon">

                <i class="fa-solid fa-box"></i>

            </div>

            <div>

                <h2>
                    <?php echo $totalListings; ?>
                </h2>

                <p>
                    My Listings
                </p>

            </div>

        </div>



        <!-- AVAILABLE -->

        <div class="dashboard-stat">

            <div class="stat-icon">

                <i class="fa-solid fa-circle-check"></i>

            </div>

            <div>

                <h2>
                    <?php echo $availableListings; ?>
                </h2>

                <p>
                    Available
                </p>

            </div>

        </div>



        <!-- SOLD -->

        <div class="dashboard-stat">

            <div class="stat-icon">

                <i class="fa-solid fa-tag"></i>

            </div>

            <div>

                <h2>
                    <?php echo $soldListings; ?>
                </h2>

                <p>
                    Sold
                </p>

            </div>

        </div>



        <!-- VIEWS -->

        <div class="dashboard-stat">

            <div class="stat-icon">

                <i class="fa-solid fa-eye"></i>

            </div>

            <div>

                <h2>
                    <?php echo $totalViews; ?>
                </h2>

                <p>
                    Total Views
                </p>

            </div>

        </div>

    </div>



    <!-- =====================================================
         GRAPHS
    ====================================================== -->

    <section class="dashboard-section">


        <div class="section-heading">

            <div>

                <h2>
                    Dashboard Overview
                </h2>

                <p>
                    View your listing performance and activity.
                </p>

            </div>

        </div>



        <div class="dashboard-charts">


            <!-- =================================================
                 LISTING STATUS GRAPH
            ================================================== -->

            <div class="chart-card">

                <div class="chart-header">

                    <div>

                        <h3>
                            Listing Status
                        </h3>

                        <p>
                            Available vs Sold products
                        </p>

                    </div>


                    <div class="chart-icon">

                        <i class="fa-solid fa-chart-pie"></i>

                    </div>

                </div>


                <div class="chart-container">

                    <?php if ($totalListings > 0) { ?>

                        <canvas
                            id="listingStatusChart">
                        </canvas>

                    <?php } else { ?>

                        <div class="no-chart-data">

                            <i class="fa-solid fa-chart-pie"></i>

                            <p>
                                No listing data available.
                            </p>

                        </div>

                    <?php } ?>

                </div>

            </div>



            <!-- =================================================
                 PRODUCT VIEWS GRAPH
            ================================================== -->

            <div class="chart-card">

                <div class="chart-header">

                    <div>

                        <h3>
                            Product Views
                        </h3>

                        <p>
                            Views of your recent listings
                        </p>

                    </div>


                    <div class="chart-icon">

                        <i class="fa-solid fa-chart-column"></i>

                    </div>

                </div>


                <div class="chart-container">

                    <?php if (count($graphLabels) > 0) { ?>

                        <canvas
                            id="viewsChart">
                        </canvas>

                    <?php } else { ?>

                        <div class="no-chart-data">

                            <i class="fa-solid fa-chart-column"></i>

                            <p>
                                No view data available.
                            </p>

                        </div>

                    <?php } ?>

                </div>

            </div>


        </div>

    </section>



    <!-- =====================================================
         QUICK ACTIONS
    ====================================================== -->

    <section class="dashboard-section">


        <div class="section-heading">

            <h2>
                Quick Actions
            </h2>

        </div>



        <div class="quick-actions">


            <!-- SELL ITEM -->

            <a href="../products/upload_product.php">

                <i class="fa-solid fa-plus"></i>

                <span>
                    Sell Item
                </span>

            </a>



            <!-- MY LISTINGS -->

            <a href="../products/my_listings.php">

                <i class="fa-solid fa-box"></i>

                <span>
                    My Listings
                </span>

            </a>



            <!-- MY PROFILE -->

            <a href="../profile/profile.php">

                <i class="fa-solid fa-user"></i>

                <span>
                    My Profile
                </span>

            </a>



            <!-- WISHLIST -->

            <a href="../products/wishlist.php">

                <i class="fa-solid fa-heart"></i>

                <span>
                    Wishlist
                </span>

            </a>



            <!-- CONTACT -->

            <a href="../contact.php">

                <i class="fa-solid fa-envelope"></i>

                <span>
                    Contact
                </span>

            </a>


        </div>

    </section>



    <!-- =====================================================
         RECENT LISTINGS
    ====================================================== -->

    <section class="dashboard-section">


        <div class="section-heading">


            <div>

                <h2>
                    Recent Listings
                </h2>

            </div>


            <a href="../products/my_listings.php">

                View All

                <i class="fa-solid fa-arrow-right"></i>

            </a>


        </div>



        <?php if (count($recentProducts) > 0) { ?>


            <div class="recent-listings">


                <?php foreach ($recentProducts as $product) { ?>


                    <div class="recent-card">


                        <!-- PRODUCT IMAGE -->

                        <?php

                        $image = !empty($product['image'])
                            ? $product['image']
                            : 'no-image.png';

                        ?>


                        <img
                            src="../uploads/<?php echo htmlspecialchars($image); ?>"
                            alt="<?php echo htmlspecialchars($product['title']); ?>">



                        <!-- PRODUCT INFORMATION -->

                        <div class="recent-info">


                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $product['title']
                                );
                                ?>

                            </h3>


                            <div class="recent-price">

                                Rs.
                                <?php
                                echo number_format(
                                    $product['price'],
                                    2
                                );
                                ?>

                            </div>


                            <p>

                                <i class="fa-solid fa-eye"></i>

                                <?php
                                echo (int)$product['views'];
                                ?>

                                views

                            </p>


                        </div>



                        <!-- STATUS + VIEW -->

                        <div class="recent-right">


                            <span
                                class="status-badge <?php echo strtolower(htmlspecialchars($product['status'])); ?>">

                                <?php
                                echo htmlspecialchars(
                                    ucfirst($product['status'])
                                );
                                ?>

                            </span>


                            <a
                                href="../products/product_details.php?id=<?php echo (int)$product['id']; ?>">

                                View

                                <i class="fa-solid fa-arrow-right"></i>

                            </a>


                        </div>


                    </div>


                <?php } ?>


            </div>


        <?php } else { ?>


            <!-- EMPTY DASHBOARD -->

            <div class="empty-dashboard">


                <div class="empty-icon">

                    <i class="fa-solid fa-box-open"></i>

                </div>


                <h3>
                    No listings yet
                </h3>


                <p>
                    Start selling your first item on SecondPasal.
                </p>


                <a
                    href="../products/upload_product.php"
                    class="sell-btn">

                    <i class="fa-solid fa-plus"></i>

                    Sell Your First Item

                </a>


            </div>


        <?php } ?>


    </section>


</div>



<!-- =========================================================
     CHART JAVASCRIPT
========================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {


    /* =====================================================
       LISTING STATUS DOUGHNUT CHART
    ====================================================== */

    const listingCanvas =
        document.getElementById("listingStatusChart");


    if (listingCanvas) {

        new Chart(listingCanvas, {

            type: "doughnut",

            data: {

                labels: [
                    "Available",
                    "Sold"
                ],

                datasets: [{

                    data: [

                        <?php
                        echo $availableListings;
                        ?>,

                        <?php
                        echo $soldListings;
                        ?>

                    ],

                    borderWidth: 3

                }]

            },


            options: {

                responsive: true,

                maintainAspectRatio: false,

                cutout: "65%",

                plugins: {

                    legend: {

                        position: "bottom",

                        labels: {

                            padding: 20,

                            font: {
                                size: 13
                            }

                        }

                    }

                }

            }

        });

    }



    /* =====================================================
       PRODUCT VIEWS BAR CHART
    ====================================================== */

    const viewsCanvas =
        document.getElementById("viewsChart");


    if (viewsCanvas) {

        new Chart(viewsCanvas, {

            type: "bar",

            data: {

                labels:
                    <?php
                    echo json_encode($graphLabels);
                    ?>,

                datasets: [{

                    label: "Views",

                    data:
                        <?php
                        echo json_encode($graphViews);
                        ?>,

                    borderWidth: 1,

                    borderRadius: 8,

                    maxBarThickness: 55

                }]

            },


            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        display: false

                    }

                },


                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {

                            precision: 0

                        }

                    },

                    x: {

                        ticks: {

                            maxRotation: 35,

                            minRotation: 0

                        }

                    }

                }

            }

        });

    }

});

</script>



<?php

mysqli_stmt_close($stmt);

include("../includes/footer.php");

?>