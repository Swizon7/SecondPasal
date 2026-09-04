<?php

session_start();
include("db.php");

$categoryResult = mysqli_query(
    $conn,
    "SELECT id, category_name, icon
     FROM categories
     ORDER BY category_name ASC"
);

?>

<?php include("includes/header.php"); ?>

<link rel="stylesheet" href="assets/css/categories.css">

<section class="categories-page">

    <div class="categories-header">

        <h1>Browse Categories</h1>

        <p>
            Explore second-hand products by category.
        </p>

    </div>

    <div class="categories-grid">

        <?php if (mysqli_num_rows($categoryResult) > 0) { ?>

            <?php while ($category = mysqli_fetch_assoc($categoryResult)) { ?>

                <a
                    href="products/products.php?category=<?php echo (int)$category['id']; ?>"
                    class="category-card">

                    <div class="category-icon">

                        <i class="fa-solid <?php echo htmlspecialchars($category['icon']); ?>"></i>

                    </div>

                    <h2>
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </h2>

                    <span>
                        View Products
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>

                </a>

            <?php } ?>

        <?php } else { ?>

            <div class="no-categories">
                <i class="fa-solid fa-folder-open"></i>

                <h2>No Categories Available</h2>

                <p>
                    Categories will appear here when they are added.
                </p>
            </div>

        <?php } ?>

    </div>

</section>

<?php include("includes/footer.php"); ?>