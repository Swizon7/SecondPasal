<?php

if(session_status() == PHP_SESSION_NONE)
{
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
<link rel="stylesheet" href="/SecondPasal/assets/css/upload_product.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<header>

<div class="header-container">

    <!-- Logo -->

    <a href="/SecondPasal/homepage/homepage.php" class="logo">

        <i class="fa-solid fa-store"></i>

        Second<span>Pasal</span>

    </a>

    <!-- Navigation -->

    <nav>

        <a class="<?=($current=="homepage.php")?"active":"";?>"
        href="/SecondPasal/homepage/homepage.php">

            Home

        </a>

        <a class="<?=($current=="products.php")?"active":"";?>"
        href="/SecondPasal/products/products.php">

            Products

        </a>

        <a href="#">

            Categories

        </a>

      <a href="/SecondPasal/contact.php">Contact</a>

    </nav>

    <!-- Search -->

   <form action="/SecondPasal/products/products.php" method="GET" class="search-box">

    <input
        type="text"
        name="search"
        placeholder="Search products..."
        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

    <button type="submit">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>

</form>

    <!-- Right -->

    <div class="right-side">

<?php

if(isset($_SESSION['user_id']))
{

?>

        <a href="/SecondPasal/products/upload_product.php"
        class="sell-btn">

            <i class="fa fa-plus"></i>

            Sell Item

        </a>

        <div class="dropdown">

            <button class="dropbtn">

                <i class="fa fa-user-circle"></i>

                <?=htmlspecialchars($_SESSION['name'])?>

                <i class="fa fa-angle-down"></i>

            </button>
            

            <div class="dropdown-content">

                <a href="/SecondPasal/dashboard/dashboard.php">

                    Dashboard

                </a>

                <a href="/SecondPasal/products/my_listings.php">

                    My Listings

                </a>

                <a href="/SecondPasal/profile/profile.php">

                    Profile

                </a>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>

    <a href="/SecondPasal/admin/dashboard.php" class="admin-dashboard-btn">
        <i class="fa-solid fa-user-shield"></i>
        Admin Panel
    </a>

<?php } ?>

                <a href="/SecondPasal/login/logout.php">

                    Logout

                </a>

            </div>

        </div>

<?php

}

else

{

?>

        <a href="/SecondPasal/login/login.php"
        class="login-btn">

            Login

        </a>

        <a href="/SecondPasal/login/register.php"
        class="register-btn">

            Register

        </a>

<?php

}

?>

    </div>

</div>

</header>