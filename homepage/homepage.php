<?php
session_start();
include("../db.php");
include("../includes/header.php");
// Total Users
$userQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$userData = mysqli_fetch_assoc($userQuery);

// Total Products
$productQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
$productData = mysqli_fetch_assoc($productQuery);

// Total Categories
$categoryQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories");
$categoryData = mysqli_fetch_assoc($categoryQuery);

// Get all categories
$categoryResult = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");

// Latest 8 Products
$productResult = mysqli_query($conn,"
SELECT
    products.*,
    users.name
FROM products
INNER JOIN users
ON products.user_id = users.id
WHERE status='available'
ORDER BY created_at DESC
LIMIT 8
");
?>

<!DOCTYPE html>
<html>
<head>

<title>SecondPasal</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link rel="stylesheet" href="/SecondPasal/assets/css/header.css">
<link rel="stylesheet" href="/SecondPasal/assets/css/homepage.css">
<link rel="stylesheet" href="../assets/css/footer.css">

<!-- <link rel="stylesheet" href="homepage.css"> -->
</head>

<body>
<!-- ================= HERO ================= -->

<section class="hero">

    <div class="hero-left">

        <span class="badge">
            ♻ Sustainable Marketplace
        </span>

       <h1>
            Buy & Sell <span>Second-Hand</span> Products
        </h1>

        <p>
            Discover quality pre-owned items across Nepal.
            Save money, reduce waste, and give products a second life with SecondPasal.
        </p>

        <div class="hero-buttons">

            <a href="../products/products.php" class="browse-btn">
                Browse Products
            </a>

            <?php if(isset($_SESSION['user_id'])) { ?>

                <a href="../products/upload_product.php" class="sell-btn">
                    Sell Item
                </a>

            <?php } else { ?>

                <a href="../login/register.php" class="sell-btn">
                    Get Started
                </a>

            <?php } ?>

        </div>

    </div>

    <div class="hero-right">

        <img src="../assets/images/logo.png" alt="SecondPasal">

    </div>

</section>
<!-- Statistics Section -->
 <!-- ================= STATISTICS ================= -->

<section class="stats">

    <div class="stat-card">

        <i class="fa-solid fa-users"></i>

        <h2><?php echo $userData['total']; ?></h2>

        <p>Registered Users</p>

    </div>

    <div class="stat-card">

        <i class="fa-solid fa-box"></i>

        <h2><?php echo $productData['total']; ?></h2>

        <p>Products Listed</p>

    </div>

    <div class="stat-card">

        <i class="fa-solid fa-list"></i>

        <h2><?php echo $categoryData['total']; ?></h2>

        <p>Categories</p>

    </div>

</section>

<!-- ================= CATEGORIES ================= -->

<section class="categories">

    <div class="section-title">

        <h2>Browse Categories</h2>

        <p>Choose a category to explore products.</p>

    </div>

    <div class="category-grid">

        <?php

        while($category = mysqli_fetch_assoc($categoryResult))
        {

        ?>

        <a href="../products/products.php?category=<?php echo $category['id']; ?>" class="category-card">

            <i class="fa-solid <?php echo $category['icon']; ?>"></i>

            <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>

        </a>

        <?php

        }

        ?>

    </div>

</section>
<section class="latest-products">

    <div class="section-title">

        <h2>Latest Products</h2>

        <p>Recently added products on SecondPasal</p>

    </div>

    <div class="product-grid">

<?php

if(mysqli_num_rows($productResult)>0)
{

while($product=mysqli_fetch_assoc($productResult))
{

?>

        <div class="product-card">

         <?php
$image = !empty($product['image']) ? $product['image'] : 'no-image.png';
?>

<img src="../uploads/<?php echo htmlspecialchars($image); ?>" alt="Product" placeholder="SecondPasal/uploads/no-image.png">

            <div class="product-info">

                <h3>
                    <?php echo htmlspecialchars($product['title']); ?>
                </h3>

                <div class="price">
                    Rs. <?php echo number_format($product['price']); ?>
                </div>

                <p class="location">
                    📍 <?php echo htmlspecialchars($product['location']); ?>
                </p>

                <p class="condition">
                    Condition:
                    <strong><?php echo htmlspecialchars($product['condition_type']); ?></strong>
                </p>

                <div class="product-buttons">

                    <a href="../products/product_details.php?id=<?php echo $product['id']; ?>" class="details-btn">

                        View Details

                    </a>

<?php if(isset($_SESSION['user_id']) && $_SESSION['user_id']!=$product['user_id']){ ?>

                    <a href="../chat/chat.php?user=<?php echo $product['user_id']; ?>&product=<?php echo $product['id']; ?>" class="message-btn">

                        Message

                    </a>

<?php } ?>

                </div>

            </div>

        </div>

<?php

}

}
else
{

?>

<p>No products available.</p>

<?php

}

?>

    </div>

</section>

<!-- Why Choose Us -->

<section class="why-choose">

    <div class="section-title">

        <h2>Why Choose SecondPasal?</h2>

        <p>
            A simple and reliable way to buy and sell second-hand products.
        </p>

    </div>

    <div class="why-grid">

        <div class="why-card">

            <div class="why-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <h3>Safe Marketplace</h3>

            <p>
                Connect with buyers and sellers through a secure and trusted marketplace.
            </p>

        </div>


        <div class="why-card">

            <div class="why-icon">
                <i class="fa-solid fa-comments"></i>
            </div>

            <h3>Easy Communication</h3>

            <p>
                Chat directly with sellers and buyers to discuss products before making a deal.
            </p>

        </div>


        <div class="why-card">

            <div class="why-icon">
                <i class="fa-solid fa-tags"></i>
            </div>

            <h3>Affordable Prices</h3>

            <p>
                Find quality second-hand products at prices that fit your budget.
            </p>

        </div>


        <div class="why-card">

            <div class="why-icon">
                <i class="fa-solid fa-recycle"></i>
            </div>

            <h3>Give Items a Second Life</h3>

            <p>
                Reuse products, reduce waste, and help useful items find new owners.
            </p>

        </div>

    </div>

</section>

<?php include("../includes/footer.php"); ?>
</body>
</html>