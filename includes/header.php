<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/SecondPasal/assets/css/header.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<header class="site-header">

    <div class="header-container">

        <!-- =========================
             LOGO
        ========================== -->

        <a
            href="/SecondPasal/homepage/homepage.php"
            class="logo-link">

            <img
                src="/SecondPasal/assets/images/logo.png"
                alt="SecondPasal">

            <div class="logo-text">

                <strong>SecondPasal</strong>

                <span>Buy. Sell. Save.</span>

            </div>

        </a>


        <!-- =========================
             NAVIGATION
        ========================== -->

        <nav class="nav-links">

            <a
                href="/SecondPasal/homepage/homepage.php"
                class="<?php echo $current === 'homepage.php' ? 'active' : ''; ?>">

                <i class="fa-solid fa-house"></i>
                Home

            </a>


            <a
                href="/SecondPasal/products/products.php"
                class="<?php echo $current === 'products.php' ? 'active' : ''; ?>">

                <i class="fa-solid fa-store"></i>
                Products

            </a>


            <a
                href="/SecondPasal/categories.php"
                class="<?php echo $current === 'categories.php' ? 'active' : ''; ?>">

                <i class="fa-solid fa-layer-group"></i>
                Categories

            </a>


            <a
                href="/SecondPasal/contact.php"
                class="<?php echo $current === 'contact.php' ? 'active' : ''; ?>">

                <i class="fa-solid fa-envelope"></i>
                Contact

            </a>

        </nav>


        <!-- =========================
             SEARCH
        ========================== -->

        <form
            action="/SecondPasal/products/products.php"
            method="GET"
            class="search-box">

            <i class="fa-solid fa-magnifying-glass search-icon"></i>

            <input
                type="text"
                name="search"
                placeholder="Search products..."
                value="<?php
                    echo isset($_GET['search'])
                        ? htmlspecialchars($_GET['search'])
                        : '';
                ?>">

            <button type="submit">

                <i class="fa-solid fa-arrow-right"></i>

            </button>

        </form>


        <!-- =========================
             RIGHT SIDE
        ========================== -->

        <div class="right-side">

            <?php if (isset($_SESSION['user_id'])) { ?>

                <!-- Sell -->

                <a
                    href="/SecondPasal/products/upload_product.php"
                    class="sell-btn">

                    <i class="fa-solid fa-plus"></i>

                    Sell Item

                </a>


                <!-- User Dropdown -->

                <div class="dropdown">

                    <button
                        type="button"
                        class="dropbtn">

                        <span class="user-avatar">

                            <i class="fa-solid fa-user"></i>

                        </span>

                        <span class="user-name">

                            <?php
                            echo htmlspecialchars($_SESSION['name']);
                            ?>

                        </span>

                        <i class="fa-solid fa-chevron-down arrow"></i>

                    </button>


                    <div class="dropdown-content">

                        <a href="/SecondPasal/dashboard/dashboard.php">

                            <i class="fa-solid fa-gauge"></i>

                            Dashboard

                        </a>


                        <a href="/SecondPasal/products/my_listings.php">

                            <i class="fa-solid fa-box"></i>

                            My Listings

                        </a>


                        <a href="/SecondPasal/products/wishlist.php">

                            <i class="fa-solid fa-heart"></i>

                            Wishlist

                        </a>


                        <a href="/SecondPasal/profile/profile.php">

                            <i class="fa-solid fa-user"></i>

                            Profile

                        </a>


                        <?php
                        if (
                            isset($_SESSION['role']) &&
                            $_SESSION['role'] === 'admin'
                        ) {
                        ?>

                            <div class="dropdown-divider"></div>

                            <a
                                href="/SecondPasal/admin/dashboard.php"
                                class="admin-dashboard-btn">

                                <i class="fa-solid fa-user-shield"></i>

                                Admin Panel

                            </a>

                        <?php } ?>


                        <div class="dropdown-divider"></div>


                        <a
                            href="/SecondPasal/login/logout.php"
                            class="logout-link">

                            <i class="fa-solid fa-right-from-bracket"></i>

                            Logout

                        </a>

                    </div>

                </div>


            <?php } else { ?>


                <!-- Login -->

                <a
                    href="/SecondPasal/login/login.php"
                    class="login-btn">

                    <i class="fa-solid fa-right-to-bracket"></i>

                    Login

                </a>


                <!-- Register -->

                <a
                    href="/SecondPasal/login/register.php"
                    class="register-btn">

                    Register

                </a>


            <?php } ?>

        </div>

    </div>

</header>