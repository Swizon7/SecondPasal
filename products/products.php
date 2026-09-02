<?php

session_start();
include("../db.php");

// Search and filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$condition = isset($_GET['condition']) ? trim($_GET['condition']) : '';
$minPrice = isset($_GET['min_price']) ? (float) $_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : 0;

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Conditions allowed
$allowedConditions = ['New', 'Like New', 'Good', 'Fair'];

if (!in_array($condition, $allowedConditions, true)) {
    $condition = '';
}

// Build WHERE clause
$where = ["products.status = 'available'"];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(products.title LIKE ? OR products.description LIKE ?)";
    $searchValue = "%" . $search . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= "ss";
}

if ($category > 0) {
    $where[] = "products.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

if ($condition !== '') {
    $where[] = "products.condition_type = ?";
    $params[] = $condition;
    $types .= "s";
}

if ($minPrice > 0) {
    $where[] = "products.price >= ?";
    $params[] = $minPrice;
    $types .= "d";
}

if ($maxPrice > 0) {
    $where[] = "products.price <= ?";
    $params[] = $maxPrice;
    $types .= "d";
}

$whereSql = implode(" AND ", $where);

/*
 * Count matching products
 */
$countSql = "
    SELECT COUNT(*) AS total
    FROM products
    WHERE $whereSql
";

$countStmt = mysqli_prepare($conn, $countSql);

if ($types !== '') {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}

mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);
$totalProducts = (int) mysqli_fetch_assoc($countResult)['total'];

$totalPages = max(1, (int) ceil($totalProducts / $limit));

/*
 * Get products
 */
$productSql = "
    SELECT
        products.id,
        products.user_id,
        products.title,
        products.description,
        products.price,
        products.location,
        products.image,
        products.condition_type,
        products.views,
        products.created_at,
        categories.category_name,
        users.name AS seller_name
    FROM products
    INNER JOIN categories
        ON categories.id = products.category_id
    INNER JOIN users
        ON users.id = products.user_id
    WHERE $whereSql
    ORDER BY products.created_at DESC
    LIMIT ? OFFSET ?
";

$productStmt = mysqli_prepare($conn, $productSql);

$productParams = $params;
$productTypes = $types . "ii";
$productParams[] = $limit;
$productParams[] = $offset;

mysqli_stmt_bind_param(
    $productStmt,
    $productTypes,
    ...$productParams
);

mysqli_stmt_execute($productStmt);

$productResult = mysqli_stmt_get_result($productStmt);

/*
 * Categories
 */
$categoryResult = mysqli_query(
    $conn,
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

include("../includes/header.php");

?>

<link rel="stylesheet" href="../assets/css/products.css">

<div class="products-page">

    <div class="products-heading">

        <h1>Browse Products</h1>

        <p>
            Find quality second-hand products on SecondPasal.
        </p>

    </div>

    <!-- Filters -->

    <form method="GET" class="filter-box">

        <div class="filter-group search-filter">

            <label>Search</label>

         <input
    type="text"
    name="search"
    id="productSearch"
    placeholder="Search products..."
    value="<?php echo htmlspecialchars($search); ?>">

        </div>

        <div class="filter-group">

            <label>Category</label>

            <select name="category">

                <option value="0">All Categories</option>

                <?php while ($cat = mysqli_fetch_assoc($categoryResult)) { ?>

                    <option
                        value="<?php echo $cat['id']; ?>"
                        <?php echo ($category == $cat['id']) ? 'selected' : ''; ?>>

                        <?php echo htmlspecialchars($cat['category_name']); ?>

                    </option>

                <?php } ?>

            </select>

        </div>

        <div class="filter-group">

            <label>Condition</label>

            <select name="condition">

                <option value="">All Conditions</option>

                <?php foreach ($allowedConditions as $itemCondition) { ?>

                    <option
                        value="<?php echo htmlspecialchars($itemCondition); ?>"
                        <?php echo ($condition === $itemCondition) ? 'selected' : ''; ?>>

                        <?php echo htmlspecialchars($itemCondition); ?>

                    </option>

                <?php } ?>

            </select>

        </div>

        <div class="filter-group">

            <label>Min Price</label>

            <input
                type="number"
                name="min_price"
                min="0"
                placeholder="Min"
                value="<?php echo $minPrice > 0 ? htmlspecialchars($minPrice) : ''; ?>">

        </div>

        <div class="filter-group">

            <label>Max Price</label>

            <input
                type="number"
                name="max_price"
                min="0"
                placeholder="Max"
                value="<?php echo $maxPrice > 0 ? htmlspecialchars($maxPrice) : ''; ?>">

        </div>

        <button type="submit" class="filter-btn">
            <i class="fa-solid fa-filter"></i>
            Filter
        </button>

        <a href="products.php" class="clear-btn">
            Clear
        </a>

    </form>

    <!-- Result Count -->

    <div class="results-info">

        <p>
            <?php echo $totalProducts; ?>
            product<?php echo ($totalProducts == 1) ? '' : 's'; ?>
            found
        </p>

    </div>

    <!-- Products -->

    <?php if (mysqli_num_rows($productResult) > 0) { ?>

        <div class="products-grid">

            <?php while ($product = mysqli_fetch_assoc($productResult)) { ?>

                <div class="product-card">

                    <?php
                    $image = !empty($product['image'])
                        ? $product['image']
                        : 'no-image.png';
                    ?>

                    <div class="product-image">

                        <img
                            src="../uploads/<?php echo htmlspecialchars($image); ?>"
                            alt="<?php echo htmlspecialchars($product['title']); ?>">

                        <span class="condition-badge">
                            <?php echo htmlspecialchars($product['condition_type']); ?>
                        </span>

                    </div>

                    <div class="product-content">

                        <p class="category-name">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </p>

                        <h2>
                            <?php echo htmlspecialchars($product['title']); ?>
                        </h2>

                        <div class="product-price">
                            Rs. <?php echo number_format($product['price'], 2); ?>
                        </div>

                        <p class="product-location">
                            <i class="fa-solid fa-location-dot"></i>
                            <?php echo htmlspecialchars($product['location']); ?>
                        </p>

                        <p class="product-views">
                            <i class="fa-solid fa-eye"></i>
                            <?php echo (int) $product['views']; ?> views
                        </p>

                        <div class="product-actions">

                            <a
                                href="product_details.php?id=<?php echo $product['id']; ?>"
                                class="view-btn">
                                View Details
                            </a>

                            <?php
                            if (
                                isset($_SESSION['user_id']) &&
                                (int) $_SESSION['user_id'] !== (int) $product['user_id']
                            ) {
                            ?>

                                <a
                                    href="../chat/chat.php?user=<?php echo $product['user_id']; ?>&product=<?php echo $product['id']; ?>"
                                    class="message-btn">
                                    <i class="fa-solid fa-comment"></i>
                                </a>

                            <?php } ?>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="empty-products">

            <i class="fa-solid fa-box-open"></i>

            <h2>No Products Found</h2>

            <p>
                Try a different search or filter.
            </p>

            <a href="products.php" class="clear-btn">
                View All Products
            </a>

        </div>

    <?php } ?>

    <!-- Pagination -->

    <?php if ($totalPages > 1) { ?>

        <div class="pagination">

            <?php if ($page > 1) { ?>

                <a href="?<?php
                    echo http_build_query([
                        'search' => $search,
                        'category' => $category,
                        'condition' => $condition,
                        'min_price' => $minPrice > 0 ? $minPrice : '',
                        'max_price' => $maxPrice > 0 ? $maxPrice : '',
                        'page' => $page - 1
                    ]);
                ?>">
                    Previous
                </a>

            <?php } ?>

            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>

                <a
                    class="<?php echo ($i == $page) ? 'active' : ''; ?>"
                    href="?<?php
                        echo http_build_query([
                            'search' => $search,
                            'category' => $category,
                            'condition' => $condition,
                            'min_price' => $minPrice > 0 ? $minPrice : '',
                            'max_price' => $maxPrice > 0 ? $maxPrice : '',
                            'page' => $i
                        ]);
                    ?>">

                    <?php echo $i; ?>

                </a>

            <?php } ?>

            <?php if ($page < $totalPages) { ?>

                <a href="?<?php
                    echo http_build_query([
                        'search' => $search,
                        'category' => $category,
                        'condition' => $condition,
                        'min_price' => $minPrice > 0 ? $minPrice : '',
                        'max_price' => $maxPrice > 0 ? $maxPrice : '',
                        'page' => $page + 1
                    ]);
                ?>">
                    Next
                </a>

            <?php } ?>

        </div>

    <?php } ?>
<script src="../assets/js/product_search.js"></script>
</div>