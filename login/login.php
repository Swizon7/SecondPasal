<?php

session_start();

if (isset($_SESSION['user_id'])) {
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

    <!-- =========================
         LEFT PANEL
    ========================== -->

    <div class="left-panel">

        <img
            src="../assets/images/logo.png"
            class="logo"
            alt="SecondPasal">

        <h1>SecondPasal</h1>

        <p class="tagline">
            Buy. Sell. Save.
        </p>

        <p class="description">
            Nepal's trusted marketplace for buying and selling
            second-hand products.
        </p>

        <div class="features">

            <p>
                <i class="fa-solid fa-circle-check"></i>
                Secure Marketplace
            </p>

            <p>
                <i class="fa-solid fa-circle-check"></i>
                Easy Communication
            </p>

            <p>
                <i class="fa-solid fa-circle-check"></i>
                Fast Selling
            </p>

            <p>
                <i class="fa-solid fa-circle-check"></i>
                Thousands of Listings
            </p>

        </div>

    </div>


    <!-- =========================
         RIGHT PANEL
    ========================== -->

    <div class="right-panel">

        <h2>Welcome Back 👋</h2>

        <p>Sign in to continue</p>


        <!-- =========================
             SUCCESS / ERROR MESSAGES
        ========================== -->

        <?php

        /*
         * Registration / Verification Email Sent
         *
         * register_process.php redirects:
         * register.php?success=verification_sent
         */

        if (
            isset($_GET['success']) &&
            $_GET['success'] === 'verification_sent'
        ) {
        ?>

            <div class="success">

                <i class="fa-solid fa-circle-check"></i>

                Registration successful!
                Please check your Gmail and verify your email
                before logging in.

            </div>

        <?php
        }


        /*
         * Registration success
         * Kept for compatibility with older registration flow.
         */

        if (
            isset($_GET['registered']) &&
            $_GET['registered'] === 'success'
        ) {
        ?>

            <div class="success">

                <i class="fa-solid fa-circle-check"></i>

                Registration successful!
                Please verify your Gmail before logging in.

            </div>

        <?php
        }


        /*
         * Password reset success
         */

        if (
            isset($_GET['reset']) &&
            $_GET['reset'] === 'success'
        ) {
        ?>

            <div class="success">

                <i class="fa-solid fa-circle-check"></i>

                Password changed successfully.
                Please login.

            </div>

        <?php
        }


        /*
         * Email verification success
         */

        if (
            isset($_GET['verified']) &&
            $_GET['verified'] === 'success'
        ) {
        ?>

            <div class="success">

                <i class="fa-solid fa-circle-check"></i>

                Email verified successfully.
                You can now log in.

            </div>

        <?php
        }


        /*
         * Error messages
         */

        if (isset($_GET['error'])) {

            $error = $_GET['error'];

            if ($error === 'email_not_verified') {
        ?>

                <div class="error">

                    <i class="fa-solid fa-envelope-circle-check"></i>

                    Please verify your Gmail address
                    before logging in.

                </div>

        <?php

            } elseif ($error === 'invalid') {
        ?>

                <div class="error">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    Invalid email or password.

                </div>

        <?php

            } elseif ($error === 'inactive') {
        ?>

                <div class="error">

                    <i class="fa-solid fa-user-slash"></i>

                    Your account has been deactivated.

                </div>

        <?php

            } else {
        ?>

                <div class="error">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?php echo htmlspecialchars($error); ?>

                </div>

        <?php
            }
        }

        ?>


        <!-- =========================
             LOGIN FORM
        ========================== -->

        <form action="login_process.php" method="POST">

            <!-- EMAIL -->

            <div class="input-box">

                <label for="email">
                    Email
                </label>

                <div class="input-field">

                    <i class="fa-solid fa-envelope"></i>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required>

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="input-box">

                <label for="password">
                    Password
                </label>

                <div class="input-field">

                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required>

                    <i
                        class="fa-solid fa-eye toggle-password"
                        id="togglePassword"
                        title="Show password">
                    </i>

                </div>

            </div>


            <!-- OPTIONS -->

            <div class="options">

                <label>

                    <input
                        type="checkbox"
                        name="remember">

                    Remember Me

                </label>

                <a href="forgot_password.php">
                    Forgot Password?
                </a>

            </div>


            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                class="login-btn">

                <i class="fa-solid fa-right-to-bracket"></i>

                Login

            </button>

        </form>


        <!-- REGISTER -->

        <div class="register">

            Don't have an account?

            <a href="register.php">
                Register
            </a>

        </div>

    </div>

</div>


<!-- LOGIN JAVASCRIPT -->

<script src="../assets/js/login.js"></script>

</body>

</html>