<?php

session_start();
include("../db.php");

/* =========================================================
   ADMIN LOGIN CHECK
========================================================= */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}


/* =========================================================
   SEARCH AND FILTER
========================================================= */

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$allowedStatuses = ['available', 'sold'];

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}


/* =========================================================
   BUILD QUERY
========================================================= */

$where = [];
$params = [];
$types = '';

if ($search !== '') {

    $where[] = "(products.title LIKE ? OR users.name LIKE ?)";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ss";
}


if ($status !== '') {

    $where[] = "products.status = ?";

    $params[] = $status;

    $types .= "s";
}


$whereSql = '';

if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}


/* =========================================================
   GET PRODUCTS
========================================================= */

$sql = "
    SELECT
        products.id,
        products.title,
        products.price,
        products.image,
        products.condition_type,
        products.location,
        products.status,
        products.views,
        products.created_at,
        users.name AS seller_name,
        categories.category_name

    FROM products

    INNER JOIN users
        ON users.id = products.user_id

    INNER JOIN categories
        ON categories.id = products.category_id

    $whereSql

    ORDER BY products.id DESC
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed.");
}


if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$totalProducts = mysqli_num_rows($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Products | SecondPasal</title>


    <!-- Poppins -->

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <link rel="stylesheet" href="../assets/css/admin_sidebar.css">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/admin_products.css?v=20">
</head>


<body>


<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="sidebar">


        <!-- BRAND -->

        <div class="admin-brand">

            <i class="fa-solid fa-user-shield"></i>

            <span>SecondPasal</span>

        </div>


        <!-- LABEL -->

        <p class="admin-label">
            ADMIN PANEL
        </p>


        <!-- NAVIGATION -->

        <nav>


            <!-- DASHBOARD -->

            <a href="dashboard.php">

                <i class="fa-solid fa-chart-line"></i>

                <span>Dashboard</span>

            </a>


            <!-- USERS -->

            <a href="users.php">

                <i class="fa-solid fa-users"></i>

                <span>Users</span>

            </a>


            <!-- PRODUCTS -->

            <a href="products.php" class="active">

                <i class="fa-solid fa-box"></i>

                <span>Products</span>

            </a>


            <!-- CATEGORIES -->

            <a href="categories.php">

                <i class="fa-solid fa-list"></i>

                <span>Categories</span>

            </a>


            <!-- REPORTS -->

            <a href="reports.php">

                <i class="fa-solid fa-flag"></i>

                <span>Reports</span>

            </a>


            <!-- WEBSITE -->

            <a href="../homepage/homepage.php">

                <i class="fa-solid fa-globe"></i>

                <span>Visit Website</span>

            </a>


            <!-- LOGOUT -->

            <a href="logout.php" class="logout-link">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>


        </nav>

    </aside>



    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <!-- =================================================
             TOPBAR
        ================================================== -->

        <div class="topbar">


            <div class="page-heading">

                <span class="heading-label">
                    ADMIN PANEL
                </span>

                <h1>
                    Manage Products
                </h1>

                <p>
                    View and manage marketplace listings.
                </p>

            </div>


            <!-- ADMIN ACCOUNT -->

            <div class="admin-account">


                <i class="fa-solid fa-circle-user account-icon"></i>


                <div class="account-info">

                    <span>Administrator</span>

                    <strong>
                        <?php
                        echo htmlspecialchars($_SESSION['name']);
                        ?>
                    </strong>

                </div>

            </div>

        </div>



        <!-- =================================================
             SUCCESS MESSAGE
        ================================================== -->

        <?php if (isset($_GET['success'])) { ?>

            <div class="admin-message success-message">

                <i class="fa-solid fa-circle-check"></i>

                <span>

                    <?php

                    if ($_GET['success'] === 'deleted') {

                        echo "Product deleted successfully.";

                    } elseif ($_GET['success'] === 'status') {

                        echo "Product status updated successfully.";

                    } else {

                        echo "Action completed successfully.";

                    }

                    ?>

                </span>


                <button
                    type="button"
                    class="message-close"
                    onclick="this.parentElement.remove();"
                >

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

        <?php } ?>



        <!-- =================================================
             ERROR MESSAGE
        ================================================== -->

        <?php if (isset($_GET['error'])) { ?>

            <div class="admin-message error-message">

                <i class="fa-solid fa-circle-exclamation"></i>

                <span>
                    Unable to complete the requested action.
                </span>


                <button
                    type="button"
                    class="message-close"
                    onclick="this.parentElement.remove();"
                >

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

        <?php } ?>



        <!-- =================================================
             SEARCH / FILTER
        ================================================== -->

        <div class="products-toolbar">


            <form
                method="GET"
                id="productFilterForm"
            >


                <!-- SEARCH -->

                <div
                    class="admin-search"
                    id="searchBox"
                >

                    <i class="fa-solid fa-magnifying-glass"></i>


                    <input
                        type="text"
                        name="search"
                        id="productSearch"
                        placeholder="Search product or seller..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        autocomplete="off"
                    >


                    <!-- LOADING -->

                    <span
                        class="search-loading"
                        id="searchLoading"
                    >

                        <i class="fa-solid fa-spinner"></i>

                    </span>

                </div>



                <!-- STATUS -->

                <select
                    name="status"
                    id="statusFilter"
                >

                    <option value="">
                        All Status
                    </option>


                    <option
                        value="available"
                        <?php
                        echo ($status === 'available')
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Available
                    </option>


                    <option
                        value="sold"
                        <?php
                        echo ($status === 'sold')
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Sold
                    </option>

                </select>



                <!-- FILTER -->

                <button
                    type="submit"
                    class="filter-btn"
                >

                    <i class="fa-solid fa-filter"></i>

                    Filter

                </button>



                <!-- CLEAR -->

                <a
                    href="products.php"
                    class="clear-btn"
                >

                    <i class="fa-solid fa-rotate-left"></i>

                    Clear

                </a>


            </form>

        </div>



        <!-- =================================================
             PRODUCTS CARD
        ================================================== -->

        <div class="products-card">


            <!-- CARD HEADER -->

            <div class="products-card-header">


                <div>

                    <h2>
                        All Products
                    </h2>

                    <p>

                        <?php echo $totalProducts; ?>

                        product<?php
                        echo ($totalProducts != 1)
                            ? 's'
                            : '';
                        ?>

                        found

                    </p>

                </div>


                <!-- COUNT -->

                <div class="product-count">

                    <i class="fa-solid fa-box"></i>

                    <?php echo $totalProducts; ?>

                </div>

            </div>



            <!-- =================================================
                 TABLE
            ================================================== -->

            <div class="table-container">

                <table>


                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Product</th>

                            <th>Seller</th>

                            <th>Category</th>

                            <th>Price</th>

                            <th>Condition</th>

                            <th>Views</th>

                            <th>Status</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if ($totalProducts > 0) { ?>


                        <?php while ($product = mysqli_fetch_assoc($result)) { ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <span class="product-id">

                                        #<?php
                                        echo (int)$product['id'];
                                        ?>

                                    </span>

                                </td>



                                <!-- PRODUCT -->

                                <td>

                                    <div class="product-info">


                                        <?php if (!empty($product['image'])) { ?>


                                            <img
                                                src="../uploads/<?php
                                                echo htmlspecialchars(
                                                    $product['image']
                                                );
                                                ?>"
                                                alt="<?php
                                                echo htmlspecialchars(
                                                    $product['title']
                                                );
                                                ?>"
                                            >


                                        <?php } else { ?>


                                            <div class="no-product-image">

                                                <i class="fa-solid fa-image"></i>

                                            </div>


                                        <?php } ?>


                                        <div>

                                            <strong
                                                title="<?php
                                                echo htmlspecialchars(
                                                    $product['title']
                                                );
                                                ?>"
                                            >

                                                <?php
                                                echo htmlspecialchars(
                                                    $product['title']
                                                );
                                                ?>

                                            </strong>


                                            <small>

                                                <i class="fa-solid fa-location-dot"></i>

                                                <?php
                                                echo htmlspecialchars(
                                                    $product['location']
                                                );
                                                ?>

                                            </small>

                                        </div>


                                    </div>

                                </td>



                                <!-- SELLER -->

                                <td>

                                    <span class="seller-name">

                                        <i class="fa-solid fa-user"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $product['seller_name']
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- CATEGORY -->

                                <td>

                                    <span class="category-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $product['category_name']
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- PRICE -->

                                <td>

                                    <span class="product-price">

                                        Rs.
                                        <?php
                                        echo number_format(
                                            $product['price'],
                                            2
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- CONDITION -->

                                <td>

                                    <span class="condition-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $product['condition_type']
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- VIEWS -->

                                <td>

                                    <span class="views-count">

                                        <i class="fa-solid fa-eye"></i>

                                        <?php
                                        echo (int)$product['views'];
                                        ?>

                                    </span>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="product-status
                                        <?php
                                        echo strtolower(
                                            $product['status']
                                        );
                                        ?>"
                                    >

                                        <i class="fa-solid fa-circle"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            ucfirst(
                                                $product['status']
                                            )
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- ACTIONS -->

                                <td>

                                    <div class="product-actions">


                                        <!-- VIEW -->

                                        <a
                                            href="../products/product_details.php?id=<?php
                                            echo (int)$product['id'];
                                            ?>"
                                            class="action-btn view-action"
                                            target="_blank"
                                            title="View Product"
                                        >

                                            <i class="fa-solid fa-eye"></i>

                                        </a>



                                        <!-- STATUS -->

                                        <a
                                            href="product_action.php?action=toggle_status&id=<?php
                                            echo (int)$product['id'];
                                            ?>"
                                            class="action-btn status-action"
                                            title="<?php
                                            echo (
                                                $product['status']
                                                === 'available'
                                            )
                                                ? 'Mark as Sold'
                                                : 'Mark as Available';
                                            ?>"
                                        >

                                            <?php
                                            if (
                                                $product['status']
                                                === 'available'
                                            ) {
                                            ?>

                                                <i class="fa-solid fa-check"></i>

                                            <?php
                                            } else {
                                            ?>

                                                <i class="fa-solid fa-rotate-left"></i>

                                            <?php
                                            }
                                            ?>

                                        </a>



                                        <!-- DELETE -->

                                        <a
                                            href="product_action.php?action=delete&id=<?php
                                            echo (int)$product['id'];
                                            ?>"
                                            class="action-btn delete-action"
                                            title="Delete Product"
                                            onclick="return confirm('Are you sure you want to delete this product?');"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php } ?>


                    <?php } else { ?>


                        <!-- NO PRODUCTS -->

                        <tr>

                            <td
                                colspan="9"
                                class="no-data"
                            >

                                <div class="empty-icon">

                                    <i class="fa-solid fa-box-open"></i>

                                </div>


                                <h3>
                                    No Products Found
                                </h3>


                                <p>
                                    No products match your current search
                                    or filter.
                                </p>


                                <a
                                    href="products.php"
                                    class="empty-clear-btn"
                                >

                                    <i class="fa-solid fa-rotate-left"></i>

                                    Clear Filters

                                </a>

                            </td>

                        </tr>


                    <?php } ?>


                    </tbody>

                </table>

            </div>

        </div>


    </main>

</div>



<!-- =========================================================
     SEARCH DEBOUNCE
========================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const searchInput =
        document.getElementById("productSearch");

    const searchForm =
        document.getElementById("productFilterForm");

    const searchBox =
        document.getElementById("searchBox");

    const searchLoading =
        document.getElementById("searchLoading");

    const statusFilter =
        document.getElementById("statusFilter");


    let debounceTimer;


    /* =========================================
       SEARCH DEBOUNCE
    ========================================= */

    searchInput.addEventListener("input", function () {

        clearTimeout(debounceTimer);


        if (searchInput.value.trim() !== "") {

            searchBox.classList.add("searching");

            searchLoading.style.display = "block";

        } else {

            searchBox.classList.remove("searching");

            searchLoading.style.display = "none";

        }


        debounceTimer = setTimeout(function () {

            searchBox.classList.remove("searching");

            searchLoading.style.display = "none";

            searchForm.submit();

        }, 600);

    });


    /* =========================================
       STATUS FILTER
    ========================================= */

    statusFilter.addEventListener("change", function () {

        searchForm.submit();

    });

});



/* =========================================
   AUTO HIDE MESSAGE
========================================= */

setTimeout(function () {

    const messages =
        document.querySelectorAll(".admin-message");

    messages.forEach(function (message) {

        message.style.opacity = "0";

        message.style.transform = "translateY(-5px)";

        setTimeout(function () {

            message.remove();

        }, 300);

    });

}, 4000);

</script>


</body>

</html>