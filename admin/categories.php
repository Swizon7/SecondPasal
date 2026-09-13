<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}

$editCategory = null;

if (isset($_GET['edit'])) {

    $editId = (int)$_GET['edit'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, category_name, icon
         FROM categories
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $editCategory = mysqli_fetch_assoc($result);
    }
}

$categories = mysqli_query(
    $conn,
    "SELECT
        c.id,
        c.category_name,
        COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p
        ON p.category_id = c.id
     GROUP BY c.id, c.category_name
     ORDER BY c.id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Manage Categories | SecondPasal</title>
    <link rel="stylesheet" href="../assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin_categories.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="admin-layout">

    <aside class="sidebar">

        <div class="admin-brand">
            <i class="fa-solid fa-user-shield"></i>
            <span>SecondPasal</span>
        </div>

        <p class="admin-label">ADMIN PANEL</p>

        <nav>

            <a href="dashboard.php">
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

            <a href="categories.php" class="active">
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

    <main class="admin-main">

        <div class="topbar">

            <div>

                <h1>Manage Categories</h1>

                <p>
                    Add and organize marketplace categories.
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
                if ($_GET['success'] === 'added') {
                    echo "Category added successfully.";
                } elseif ($_GET['success'] === 'updated') {
                    echo "Category updated successfully.";
                } elseif ($_GET['success'] === 'deleted') {
                    echo "Category deleted successfully.";
                }
                ?>

            </div>

        <?php } ?>

        <?php if (isset($_GET['error'])) { ?>

            <div class="admin-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?php
                if ($_GET['error'] === 'empty') {
                    echo "Category name cannot be empty.";
                } elseif ($_GET['error'] === 'duplicate') {
                    echo "This category already exists.";
                } elseif ($_GET['error'] === 'inuse') {
                    echo "This category cannot be deleted because products are using it.";
                } else {
                    echo "Unable to complete the requested action.";
                }
                ?>

            </div>

        <?php } ?>

        <div class="category-layout">

            <section class="category-form-card">

                <h2>
                    <?php echo $editCategory ? 'Edit Category' : 'Add Category'; ?>
                </h2>

                <form action="category_action.php" method="POST">

                    <input
                        type="hidden"
                        name="action"
                        value="<?php echo $editCategory ? 'update' : 'add'; ?>">

                    <?php if ($editCategory) { ?>

                        <input
                            type="hidden"
                            name="id"
                            value="<?php echo (int)$editCategory['id']; ?>">

                    <?php } ?>

                    <div class="category-input">

    <label>Category Name</label>

    <div class="input-wrapper">

        <i class="fa-solid fa-tag"></i>

        <input
            type="text"
            name="category_name"
            placeholder="e.g. Electronics"
            maxlength="100"
            required
            value="<?php
            echo $editCategory
                ? htmlspecialchars($editCategory['category_name'])
                : '';
            ?>">

    </div>

</div>


<div class="category-input">

    <label>Category Icon</label>

    <div class="input-wrapper">

        <i class="fa-solid fa-icons"></i>

        <select name="icon" required>

            <?php

            $icons = [
                'fa-mobile-screen' => 'Mobile / Electronics',
                'fa-laptop' => 'Computers',
                'fa-shirt' => 'Fashion',
                'fa-book' => 'Books',
                'fa-couch' => 'Furniture',
                'fa-car' => 'Vehicles',
                'fa-bicycle' => 'Bicycles',
                'fa-futbol' => 'Sports',
                'fa-gamepad' => 'Gaming',
                'fa-camera' => 'Cameras',
                'fa-headphones' => 'Accessories',
                'fa-house' => 'Home',
                'fa-wrench' => 'Tools',
                'fa-box' => 'Others'
            ];

            foreach ($icons as $iconClass => $iconName) {

                $selected = '';

                if (
                    $editCategory &&
                    isset($editCategory['icon']) &&
                    $editCategory['icon'] === $iconClass
                ) {
                    $selected = 'selected';
                }

            ?>

                <option
                    value="<?php echo htmlspecialchars($iconClass); ?>"
                    <?php echo $selected; ?>>

                    <?php echo htmlspecialchars($iconName); ?>

                </option>

            <?php } ?>

        </select>

    </div>

</div>
                    <button type="submit" class="category-submit">

                        <i class="fa-solid <?php echo $editCategory ? 'fa-floppy-disk' : 'fa-plus'; ?>"></i>

                        <?php echo $editCategory ? 'Save Changes' : 'Add Category'; ?>

                    </button>

                    <?php if ($editCategory) { ?>

                        <a href="categories.php" class="cancel-btn">
                            Cancel Edit
                        </a>

                    <?php } ?>

                </form>

            </section>

            <section class="category-list-card">

                <div class="category-list-header">

                    <h2>Categories</h2>

                    <span>
                        <?php echo mysqli_num_rows($categories); ?> categories
                    </span>

                </div>

                <div class="category-table">

                    <table>

                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Products</th>
                                <th>Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php if (mysqli_num_rows($categories) > 0) { ?>

                            <?php while ($category = mysqli_fetch_assoc($categories)) { ?>

                                <tr>

                                    <td>
                                        #<?php echo (int)$category['id']; ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <span class="product-count">
                                            <?php echo (int)$category['product_count']; ?>
                                        </span>

                                    </td>

                                    <td>

                                        <div class="category-actions">

                                            <a
                                                href="categories.php?edit=<?php echo $category['id']; ?>"
                                                class="edit-category">

                                                <i class="fa-solid fa-pen"></i>

                                            </a>

                                            <a
                                                href="category_action.php?action=delete&id=<?php echo $category['id']; ?>"
                                                class="delete-category"
                                                onclick="return confirm('Delete this category?');">

                                                <i class="fa-solid fa-trash"></i>

                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>

                                <td colspan="4" class="no-data">
                                    No categories found.
                                </td>

                            </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>

            </section>

        </div>

    </main>

</div>

</body>

</html>