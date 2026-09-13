    <?php

    session_start();
    include("../db.php");

    // Protect admin page
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: admin_login.php?error=unauthorized");
        exit();
    }

    // Total users
    $userQuery = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM users WHERE role != 'admin'"
    );

    $totalUsers = (int) mysqli_fetch_assoc($userQuery)['total'];

    // Total products
    $productQuery = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM products"
    );

    $totalProducts = (int) mysqli_fetch_assoc($productQuery)['total'];

    // Available products
    $availableQuery = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
        FROM products
        WHERE status = 'available'"
    );

    $totalAvailable = (int) mysqli_fetch_assoc($availableQuery)['total'];

    // Sold products
    $soldQuery = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
        FROM products
        WHERE status = 'sold'"
    );

    $totalSold = (int) mysqli_fetch_assoc($soldQuery)['total'];

    // Total categories
    $categoryQuery = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM categories"
    );

    $totalCategories = (int) mysqli_fetch_assoc($categoryQuery)['total'];

    // Total views
    $viewsQuery = mysqli_query(
        $conn,
        "SELECT COALESCE(SUM(views),0) AS total
        FROM products"
    );

    $totalViews = (int) mysqli_fetch_assoc($viewsQuery)['total'];

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <title>Admin Dashboard | SecondPasal</title>
        <link rel="stylesheet" href="../assets/css/admin_sidebar.css">
        <link rel="stylesheet" href="../assets/css/admin_dashboard.css">

        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
            <!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    </head>

    <body>

    <div class="admin-layout">

        <!-- Sidebar -->

        <aside class="sidebar">

            <div class="admin-brand">

                <i class="fa-solid fa-user-shield"></i>

                <span>SecondPasal</span>

            </div>

            <p class="admin-label">
                ADMIN PANEL
            </p>

            <nav>

                <a href="dashboard.php" class="active">
                    <i class="fa-solid fa-chart-line"></i>
                    Dashboard
                </a>

                <a href="users.php">
                    <i class="fa-solid fa-users"></i>
                    Users
                </a>

                <a href="products.php">
                    <i class="fa-solid fa-box"></i>
                    Products
                </a>

                <a href="categories.php">
                    <i class="fa-solid fa-list"></i>
                    Categories
                </a>

                <a href="reports.php">
                    <i class="fa-solid fa-flag"></i>
                    Reports
                </a>

                <a href="../homepage/homepage.php">
                    <i class="fa-solid fa-globe"></i>
                    Visit Website
                </a>

                <a href="logout.php" class="logout-link">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout
                </a>

            </nav>

        </aside>

        <!-- Main Content -->

        <main class="admin-main">

            <div class="topbar">

                <div>

                    <h1>Admin Dashboard</h1>

                    <p>
                        Welcome back,
                        <strong>
                            <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </strong>
                    </p>

                </div>

                <div class="admin-account">

                    <i class="fa-solid fa-circle-user"></i>

                    <?php echo htmlspecialchars($_SESSION['name']); ?>

                </div>

            </div>

            <!-- Statistics -->

            <div class="stats-grid">

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div>
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total Users</p>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-box"></i>
                    </div>

                    <div>
                        <h3><?php echo $totalProducts; ?></h3>
                        <p>Total Products</p>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div>
                        <h3><?php echo $totalAvailable; ?></h3>
                        <p>Available Products</p>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>

                    <div>
                        <h3><?php echo $totalSold; ?></h3>
                        <p>Sold Products</p>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-list"></i>
                    </div>

                    <div>
                        <h3><?php echo $totalCategories; ?></h3>
                        <p>Categories</p>
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-eye"></i>
                    </div>

                    <div>
                        <h3><?php echo $totalViews; ?></h3>
                        <p>Total Views</p>
                    </div>

                </div>

            </div>


                    <!-- =========================================
             GRAPHS
        ========================================== -->

        <div class="charts-grid">

            <!-- Product Status Chart -->

            <div class="chart-card">

                <div class="chart-header">

                    <div>
                        <h2>Product Status</h2>
                        <p>Available and sold products</p>
                    </div>

                    <div class="chart-icon">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>

                </div>

                <div class="chart-container">
                    <canvas id="productStatusChart"></canvas>
                </div>

            </div>


            <!-- Marketplace Overview -->

            <div class="chart-card">

                <div class="chart-header">

                    <div>
                        <h2>Marketplace Overview</h2>
                        <p>Overall marketplace statistics</p>
                    </div>

                    <div class="chart-icon">
                        <i class="fa-solid fa-chart-column"></i>
                    </div>

                </div>

                <div class="chart-container">
                    <canvas id="marketplaceChart"></canvas>
                </div>

            </div>

        </div>

            <!-- Quick Actions -->

            <section class="dashboard-section">

                <div class="section-heading">

                    <h2>Quick Actions</h2>

                </div>

                <div class="quick-actions">

                    <a href="users.php">

                        <i class="fa-solid fa-users"></i>

                        <span>Manage Users</span>

                    </a>

                    <a href="products.php">

                        <i class="fa-solid fa-box"></i>

                        <span>Manage Products</span>

                    </a>

                    <a href="categories.php">

                        <i class="fa-solid fa-list"></i>

                        <span>Manage Categories</span>

                    </a>

                    <a href="reports.php">

                        <i class="fa-solid fa-flag"></i>

                        <span>View Reports</span>

                    </a>

                </div>

            </section>

            <!-- Recent Products -->

            <?php

            $recentQuery = mysqli_query(
                $conn,
                "SELECT
                    products.id,
                    products.title,
                    products.price,
                    products.status,
                    products.created_at,
                    users.name AS seller_name
                FROM products
                INNER JOIN users
                    ON users.id = products.user_id
                ORDER BY products.id DESC
                LIMIT 5"
            );

            ?>

            <section class="dashboard-section">

                <div class="section-heading">

                    <h2>Recent Products</h2>

                    <a href="products.php">
                        View All
                    </a>

                </div>

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>Product</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if (mysqli_num_rows($recentQuery) > 0) { ?>

                            <?php while ($product = mysqli_fetch_assoc($recentQuery)) { ?>

                                <tr>

                                    <td>
                                        #<?php echo $product['id']; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($product['title']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($product['seller_name']); ?>
                                    </td>

                                    <td>
                                        Rs. <?php echo number_format($product['price'], 2); ?>
                                    </td>

                                    <td>

                                        <span class="status <?php echo strtolower($product['status']); ?>">

                                            <?php echo htmlspecialchars(ucfirst($product['status'])); ?>

                                        </span>

                                    </td>

                                    <td>
                                        <?php echo date("Y-m-d", strtotime($product['created_at'])); ?>
                                    </td>

                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>

                                <td colspan="6" class="no-data">
                                    No products found.
                                </td>

                            </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>

            </section>

        </main>

    </div>

    <script>

    /* =========================================
       PRODUCT STATUS CHART
    ========================================== */

    const productStatus = document.getElementById('productStatusChart');

    new Chart(productStatus, {

        type: 'doughnut',

        data: {

            labels: [
                'Available',
                'Sold'
            ],

            datasets: [{

                data: [
                    <?php echo $totalAvailable; ?>,
                    <?php echo $totalSold; ?>
                ]

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        font: {
                            family: 'Poppins',
                            size: 12
                        },

                        padding: 20

                    }

                }

            },

            cutout: '65%'

        }

    });


    /* =========================================
       MARKETPLACE OVERVIEW CHART
    ========================================== */

    const marketplace = document.getElementById('marketplaceChart');

    new Chart(marketplace, {

        type: 'bar',

        data: {

            labels: [
                'Users',
                'Products',
                'Categories',
                'Views'
            ],

            datasets: [{

                label: 'Total',

                data: [

                    <?php echo $totalUsers; ?>,
                    <?php echo $totalProducts; ?>,
                    <?php echo $totalCategories; ?>,
                    <?php echo $totalViews; ?>

                ]

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

                }

            }

        }

    });

</script>
   </body>

    </html>