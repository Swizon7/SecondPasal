<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$allowedStatuses = ['available', 'sold'];

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

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

if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Manage Products | SecondPasal</title>

    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin_products.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

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

            <a href="dashboard.php">
                <i class="fa-solid fa-chart-line"></i>
                Dashboard
            </a>

            <a href="users.php">
                <i class="fa-solid fa-users"></i>
                Users
            </a>

            <a href="products.php" class="active">
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

    <!-- Main -->

    <main class="admin-main">

        <div class="topbar">

            <div>

                <h1>Manage Products</h1>

                <p>
                    View and manage marketplace listings.
                </p>

            </div>

            <div class="admin-account">

                <i class="fa-solid fa-circle-user"></i>

                <?php echo htmlspecialchars($_SESSION['name']); ?>

            </div>

        </div>

        <?php if (isset($_GET['success'])) { ?>

            <div class="admin-success">

                <i class="fa-solid fa-circle-check"></i>

                <?php

                if ($_GET['success'] === 'deleted') {
                    echo "Product deleted successfully.";
                }

                if ($_GET['success'] === 'status') {
                    echo "Product status updated successfully.";
                }

                ?>

            </div>

        <?php } ?>

        <?php if (isset($_GET['error'])) { ?>

            <div class="admin-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                Unable to complete the requested action.

            </div>

        <?php } ?>

        <!-- Filters -->

        <div class="products-toolbar">

            <form method="GET">

                <div class="admin-search">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        name="search"
                        placeholder="Search product or seller..."
                        value="<?php echo htmlspecialchars($search); ?>">

                </div>

                <select name="status">

                    <option value="">
                        All Status
                    </option>

                    <option
                        value="available"
                        <?php echo $status === 'available' ? 'selected' : ''; ?>>
                        Available
                    </option>

                    <option
                        value="sold"
                        <?php echo $status === 'sold' ? 'selected' : ''; ?>>
                        Sold
                    </option>

                </select>

                <button type="submit" class="filter-btn">
                    <i class="fa-solid fa-filter"></i>
                    Filter
                </button>

                <a href="products.php" class="clear-btn">
                    Clear
                </a>

            </form>

        </div>

        <!-- Product Table -->

        <div class="products-card">

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

                    <?php if (mysqli_num_rows($result) > 0) { ?>

                        <?php while ($product = mysqli_fetch_assoc($result)) { ?>

                            <tr>

                                <td>
                                    #<?php echo (int)$product['id']; ?>
                                </td>

                                <td>

                                    <div class="product-info">

                                        <?php if (!empty($product['image'])) { ?>

                                            <img
                                                src="../uploads/<?php echo htmlspecialchars($product['image']); ?>"
                                                alt="Product">

                                        <?php } ?>

                                        <div>

                                            <strong>
                                                <?php echo htmlspecialchars($product['title']); ?>
                                            </strong>

                                            <small>
                                                <?php echo htmlspecialchars($product['location']); ?>
                                            </small>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    <?php echo htmlspecialchars($product['seller_name']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </td>

                                <td>
                                    Rs. <?php echo number_format($product['price'], 2); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($product['condition_type']); ?>
                                </td>

                                <td>
                                    <?php echo (int)$product['views']; ?>
                                </td>

                                <td>

                                    <span class="product-status <?php echo strtolower($product['status']); ?>">

                                        <?php echo htmlspecialchars(ucfirst($product['status'])); ?>

                                    </span>

                                </td>

                                <td>

                                    <div class="product-actions">

                                        <a
                                            href="../products/product_details.php?id=<?php echo $product['id']; ?>"
                                            class="view-action"
                                            target="_blank">

                                            <i class="fa-solid fa-eye"></i>

                                        </a>

                                        <a
                                            href="product_action.php?action=toggle_status&id=<?php echo $product['id']; ?>"
                                            class="status-action">

                                            <?php if ($product['status'] === 'available') { ?>

                                                <i class="fa-solid fa-check"></i>

                                            <?php } else { ?>

                                                <i class="fa-solid fa-rotate-left"></i>

                                            <?php } ?>

                                        </a>

                                        <a
                                            href="product_action.php?action=delete&id=<?php echo $product['id']; ?>"
                                            class="delete-action"
                                            onclick="return confirm('Are you sure you want to delete this product?');">

                                            <i class="fa-solid fa-trash"></i>

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="9" class="no-data">

                                No products found.

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

</body>

</html> 