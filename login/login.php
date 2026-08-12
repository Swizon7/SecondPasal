<?php
session_start();

if(isset($_SESSION['user_id']))
{
    header("Location: ../homepage/homepage.php");
    exit();
}
?>



<!DOCTYPE html>     
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | SecondPasal</title>

    <link rel="stylesheet" href="../assets/css/login.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="login-container">

    <!-- LEFT PANEL -->

    <div class="left-panel">

        <img src="../assets/images/logo.png" class="logo" alt="SecondPasal">

        <h1>SecondPasal</h1>

        <p class="tagline">Buy. Sell. Save.</p>

        <p class="description">
            Nepal's trusted marketplace for buying and selling second-hand products.
        </p>

        <div class="features">

            <p><i class="fa-solid fa-circle-check"></i> Secure Marketplace</p>

            <p><i class="fa-solid fa-circle-check"></i> Easy Communication</p>

            <p><i class="fa-solid fa-circle-check"></i> Fast Selling</p>

            <p><i class="fa-solid fa-circle-check"></i> Thousands of Listings</p>

        </div>

    </div>

    <!-- RIGHT PANEL -->

    <div class="right-panel">

        <h2>Welcome Back 👋</h2>

        <p>Sign in to continue</p>

        <?php
        if(isset($_GET['error']))
        {
            echo "<div class='error'>Invalid email or password.</div>";
        }
        ?>

        <?php
if(isset($_GET['registered']))
{
    echo "<div class='success'>🎉 Registration successful! Please login.</div>";
}
?>
<?php
if(isset($_GET['reset']))
{
    echo "<div class='success'>
            Password changed successfully. Please login.
          </div>";
}
?>
        <form action="login_process.php" method="POST">

            <div class="input-box">

                <label>Email</label>

                <div class="input-field">

                    <i class="fa-solid fa-envelope"></i>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required>

                </div>

            </div>

            <div class="input-box">

                <label>Password</label>

                <div class="input-field">

                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required>

                    <i
                        class="fa-solid fa-eye toggle-password"
                        id="togglePassword"></i>

                </div>

            </div>

            <div class="options">

                <label>

                    <input type="checkbox">

                    Remember Me

                </label>

                <a href="forgot_password.php">

                    Forgot Password?

                </a>

            </div>

            <button type="submit" class="login-btn">

                Login

            </button>

        </form>

        <div class="register">

            Don't have an account?

            <a href="register.php">

                Register

            </a>

        </div>

    </div>

</div>

<script src="../assets/js/login.js"></script>

</body>

</html>