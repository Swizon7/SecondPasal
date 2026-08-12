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

<title>Register | SecondPasal</title>

<link rel="stylesheet" href="../assets/css/register.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="register-container">

    <!-- Left Panel -->

    <div class="left-panel">

        <img src="../assets/images/logo.png" class="logo" alt="SecondPasal">

        <h1>SecondPasal</h1>

        <p class="tagline">Buy. Sell. Save.</p>

        <p class="description">
            Create your account and start buying or selling second-hand products across Nepal.
        </p>

        <div class="features">

            <p><i class="fa-solid fa-circle-check"></i> Free Registration</p>

            <p><i class="fa-solid fa-circle-check"></i> Secure Marketplace</p>

            <p><i class="fa-solid fa-circle-check"></i> Easy Communication</p>

            <p><i class="fa-solid fa-circle-check"></i> Fast Selling</p>

        </div>

    </div>

    <!-- Right Panel -->

    <div class="right-panel">

        <h2>Create Account</h2>

        <p>Join SecondPasal today.</p>

        <?php
        if(isset($_GET['error']))
        {
            echo "<div class='error'>".$_GET['error']."</div>";
        }

        if(isset($_GET['success']))
        {
            echo "<div class='success'>Registration Successful. Please Login.</div>";
        }
        ?>

        <form action="register_process.php" method="POST">

         <div class="form-row">

    <div class="input-box">

        <label>Full Name</label>

        <div class="input-field">

            <i class="fa-solid fa-user"></i>

            <input
                type="text"
                name="name"
                placeholder="Enter your full name"
                required>

        </div>

    </div>

    <div class="input-box">

        <label>Phone Number</label>

        <div class="input-field">

            <i class="fa-solid fa-phone"></i>

            <input
                type="text"
                name="phone"
                placeholder="98XXXXXXXX"
                required>

        </div>

    </div>

</div>
            <div class="input-box">

                <label>Email</label>

                <div class="input-field">

                    <i class="fa-solid fa-envelope"></i>

                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="Enter your email">

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
                        required
                        placeholder="Create password">

                    <i class="fa-solid fa-eye toggle-password"
                       id="togglePassword"></i>

                </div>

            </div>

            <div class="input-box">

                <label>Confirm Password</label>

                <div class="input-field">

                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        required
                        placeholder="Confirm password">

                    <i class="fa-solid fa-eye toggle-confirm"
                       id="toggleConfirm"></i>

                </div>

            </div>

            <button
                type="submit"
                class="register-btn">

                Create Account

            </button>

        </form>

        <div class="login-link">

            Already have an account?

            <a href="login.php">

                Login

            </a>

        </div>

    </div>

</div>

<script src="../assets/js/register.js"></script>

</body>

</html>