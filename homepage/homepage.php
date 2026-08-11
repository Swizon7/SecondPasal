<?php
session_start();
include("../includes/header.php");
?>

<!DOCTYPE html>
<html>
<head>

<title>SecondPasal</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link rel="stylesheet" href="/SecondPasal/assets/css/header.css">

<link rel="stylesheet" href="homepage.css">
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
            Discover amazing deals on quality pre-owned items or sell products
            you no longer use. Fast, secure and affordable.
        </p>

        <div class="hero-buttons">

            <a href="../products/products.php" class="browse-btn">
                Browse Products
            </a>

            <?php if(isset($_SESSION['user_id'])) { ?>

                <a href="../products/sell_item.php" class="sell-now-btn">
                    Sell Item
                </a>

            <?php } else { ?>

                <button id="heroLoginBtn" class="sell-now-btn">
                    Sell Item
                </button>

            <?php } ?>

        </div>

    </div>

    <div class="hero-right">

        <img src="../assets/images/hero.png" alt="Marketplace">

    </div>

</section>

<!-- Statistics Section -->
 <!-- ================= STATISTICS ================= -->

<section class="stats">

    <div class="stat-card">

        <i class="fa-solid fa-box-open"></i>

        <h2>500+</h2>

        <p>Products Listed</p>

    </div>

    <div class="stat-card">

        <i class="fa-solid fa-users"></i>

        <h2>200+</h2>

        <p>Active Users</p>

    </div>

    <div class="stat-card">

        <i class="fa-solid fa-handshake"></i>

        <h2>150+</h2>

        <p>Successful Deals</p>

    </div>

    <div class="stat-card">

        <i class="fa-solid fa-headset"></i>

        <h2>24/7</h2>

        <p>Customer Support</p>

    </div>

</section>

<!-- ================= CATEGORIES ================= -->

<section class="categories">

    <div class="section-title">

        <h2>Browse Categories</h2>

        <p>Choose a category to find what you're looking for.</p>

    </div>

    <div class="category-grid">

        <a href="../products/products.php?category=Electronics" class="category-card">

            <i class="fa-solid fa-mobile-screen-button"></i>

            <h3>Electronics</h3>

            <span>Products</span>

        </a>

        <a href="../products/products.php?category=Computers" class="category-card">

            <i class="fa-solid fa-laptop"></i>

            <h3>Computers</h3>

            <span>Products</span>

        </a>

        <a href="../products/products.php?category=Books" class="category-card">

            <i class="fa-solid fa-book"></i>

            <h3>Books</h3>

            <span>Products</span>

        </a>

        <a href="../products/products.php?category=Clothes" class="category-card">

            <i class="fa-solid fa-shirt"></i>

            <h3>Clothes</h3>

            <span>Products</span>

        </a>

        <a href="../products/products.php?category=Furniture" class="category-card">

            <i class="fa-solid fa-couch"></i>

            <h3>Furniture</h3>

            <span>Products</span>

        </a>

        <a href="../products/products.php?category=Vehicles" class="category-card">

            <i class="fa-solid fa-car"></i>

            <h3>Vehicles</h3>

            <span>Products</span>

        </a>

    </div>

</section>

<?php
include("../db.php");
?>
<section class="latest-products">

    <div class="section-title">

        <h2>Latest Products</h2>

        <p>Recently added products from our marketplace.</p>

    </div>

    <div class="product-grid">

<?php

$sql = "SELECT * FROM products
        ORDER BY id DESC
        LIMIT 6";

$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result)>0)
{

while($row=mysqli_fetch_assoc($result))
{

?>

<div class="product-card">

    <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>">

    <div class="product-info">

        <h3>

            <?php echo htmlspecialchars($row['title']); ?>

        </h3>

        <h4>

            Rs. <?php echo number_format($row['price']); ?>

        </h4>

        <p>

            📍 <?php echo htmlspecialchars($row['location']); ?>

        </p>

        <div class="product-buttons">

            <a href="../products/product_details.php?id=<?php echo $row['id']; ?>">

                View Details

            </a>

            <a href="../chat/chat.php?user=<?php echo $row['user_id']; ?>">

                Message

            </a>

        </div>

    </div>

</div>

<?php

}

}
else
{

echo "<h3>No products available.</h3>";

}

?>

    </div>

</section>

</body>
</html>