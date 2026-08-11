<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
?>

<?php

if(isset($_GET['logout']))
{
    echo '
    <div class="success-message">
        ✅ Logged out successfully.
    </div>';
}

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="/SecondPasal/assets/css/header.css">

<header>

    <!-- Top Header -->
    <div class="top-header">

        <div class="logo">
            <a href="/SecondPasal/homepage/homepage.php">
                <i class="fa-solid fa-store"></i>
                Second<span>Pasal</span>
            </a>
        </div>

        <div class="header-right">

            <?php if(isset($_SESSION['user_id'])) { ?>

                <a href="/SecondPasal/products/sell_item.php" class="sell-btn">
                    <i class="fa-solid fa-plus"></i>
                    Sell Item
                </a>

                <div class="dropdown">

                    <button class="drop-btn">

                        <i class="fa-solid fa-circle-user"></i>

                        <?php echo htmlspecialchars($_SESSION['name']); ?>

                        <i class="fa-solid fa-chevron-down"></i>

                    </button>

                    <div class="dropdown-menu">

                        <a href="/SecondPasal/profile/profile.php">
                            <i class="fa-solid fa-user"></i>
                            My Profile
                        </a>

                        <a href="/SecondPasal/products/my_listings.php">
                            <i class="fa-solid fa-box"></i>
                            My Listings
                        </a>

                        <a href="/SecondPasal/dashboard/dashboard.php">
                            <i class="fa-solid fa-chart-line"></i>
                            Dashboard
                        </a>

                        <a href="/SecondPasal/chat/chat.php">
                            <i class="fa-solid fa-comments"></i>
                            Messages
                        </a>

                        <hr>

                        <a href="/SecondPasal/login/logout.php">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Logout
                        </a>

                    </div>

                </div>

            <?php } else { ?>

                <button id="loginBtn" class="login-btn">Login</button>

                <button id="registerBtn" class="register-btn">Register</button>

            <?php } ?>

        </div>

    </div>

    <!-- Navigation -->

    <div class="nav-container">

        <nav>

            <a href="/SecondPasal/homepage/homepage.php"
               class="<?= $current=='homepage.php' ? 'active' : '' ?>">
               Home
            </a>

            <a href="/SecondPasal/products/products.php"
               class="<?= $current=='products.php' ? 'active' : '' ?>">
               Products
            </a>

            <a href="#">
                Categories
            </a>

            <a href="#">
                Contact
            </a>

        </nav>

        <form action="/SecondPasal/products/products.php" method="GET" class="search-box">

            <input
                type="text"
                name="search"
                placeholder="Search products...">

            <button type="submit">

                <i class="fa-solid fa-magnifying-glass"></i>

            </button>

        </form>

    </div>

</header>