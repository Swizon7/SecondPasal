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

    <title>Register | SecondPasal</title>

    <link rel="stylesheet" href="../assets/css/register.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="register-container">

    <!-- ================= LEFT PANEL ================= -->

    <div class="left-panel">

        <div class="brand">

            <img
                src="../assets/images/logo.png"
                class="logo"
                alt="SecondPasal Logo">

            <div>

                <h1>
                    Second<span>Pasal</span>
                </h1>

                <p class="tagline">
                    Buy. Sell. Save.
                </p>

            </div>

        </div>


        <div class="welcome-content">

            <span class="welcome-badge">
                <i class="fa-solid fa-store"></i>
                Nepal's Second-Hand Marketplace
            </span>

            <h2>
                Start Your
                <span>SecondPasal</span>
                Journey
            </h2>

            <p class="description">
                Create your free account and discover great deals,
                sell unused products, and connect with buyers and sellers
                across Nepal.
            </p>

        </div>


        <!-- FEATURES -->

        <div class="features">

            <div class="feature-item">

                <div class="feature-icon">
                    <i class="fa-solid fa-user-plus"></i>
                </div>

                <div>
                    <strong>Free Registration</strong>
                    <span>Create your account easily</span>
                </div>

            </div>


            <div class="feature-item">

                <div class="feature-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <div>
                    <strong>Secure Marketplace</strong>
                    <span>Your account stays protected</span>
                </div>

            </div>


            <div class="feature-item">

                <div class="feature-icon">
                    <i class="fa-solid fa-comments"></i>
                </div>

                <div>
                    <strong>Easy Communication</strong>
                    <span>Chat with buyers and sellers</span>
                </div>

            </div>


            <div class="feature-item">

                <div class="feature-icon">
                    <i class="fa-solid fa-bolt"></i>
                </div>

                <div>
                    <strong>Fast Selling</strong>
                    <span>List your products quickly</span>
                </div>

            </div>

        </div>


        <div class="left-footer">

            <i class="fa-solid fa-location-dot"></i>

            Made for buyers and sellers across Nepal

        </div>

    </div>


    <!-- ================= RIGHT PANEL ================= -->

    <div class="right-panel">

        <div class="form-header">

            <div class="mobile-logo">
                <i class="fa-solid fa-store"></i>
            </div>

            <h2>Create Account</h2>

            <p>
                Join SecondPasal and start buying or selling today.
            </p>

        </div>


        <!-- ================= PHP MESSAGES ================= -->

        <?php

        if (isset($_GET['error'])) {

            echo "
            <div class='message error'>

                <i class='fa-solid fa-circle-exclamation'></i>

                <div>

                    <strong>Registration Failed</strong>

                    <span>"
                    . htmlspecialchars($_GET['error']) .
                    "</span>

                </div>

            </div>";
        }


        if (
            isset($_GET['success']) &&
            $_GET['success'] === 'verification_sent'
        ) {

            echo "
            <div class='message success'>

                <i class='fa-solid fa-circle-check'></i>

                <div>

                    <strong>Verification Email Sent</strong>

                    <span>
                        Please check your Gmail inbox and verify your account.
                    </span>

                </div>

            </div>";
        }

        ?>


        <!-- ================= REGISTER FORM ================= -->

        <form
            action="register_process.php"
            method="POST"
            id="registerForm"
        >


            <!-- NAME + PHONE -->

            <div class="form-row">

                <div class="input-box">

                    <label for="name">
                        Full Name
                    </label>

                    <div class="input-field">

                        <i class="fa-solid fa-user"></i>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            required>

                    </div>

                </div>


                <div class="input-box">

                    <label for="phone">
                        Phone Number
                    </label>

                    <div class="input-field">

                        <i class="fa-solid fa-phone"></i>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            placeholder="98XXXXXXXX"
                            maxlength="10"
                            minlength="10"
                            pattern="[0-9]{10}"
                            inputmode="numeric"
                            autocomplete="tel"
                            title="Phone number must contain exactly 10 digits"
                            required>

                    </div>

                    <small class="input-hint">
                        Enter exactly 10 digits
                    </small>

                </div>

            </div>


            <!-- EMAIL -->

            <div class="input-box">

                <label for="email">
                    Email Address
                </label>

                <div class="input-field">

                    <i class="fa-solid fa-envelope"></i>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your Gmail address"
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
                        placeholder="Minimum 8 characters"
                        minlength="8"
                        autocomplete="new-password"
                        required>

                    <i
                        class="fa-solid fa-eye toggle-password"
                        id="togglePassword"
                        title="Show password">
                    </i>

                </div>


                <div class="password-strength">

                    <div class="strength-bar">

                        <span id="strengthBar"></span>

                    </div>

                    <span id="strengthText">
                        Password strength
                    </span>

                </div>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="input-box">

                <label for="confirm_password">
                    Confirm Password
                </label>

                <div class="input-field">

                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        minlength="8"
                        autocomplete="new-password"
                        required>

                    <i
                        class="fa-solid fa-eye toggle-confirm"
                        id="toggleConfirm"
                        title="Show password">
                    </i>

                </div>

                <small
                    id="passwordMatch"
                    class="password-match">
                </small>

            </div>


            <!-- INFORMATION -->

            <div class="terms">

                <i class="fa-solid fa-circle-info"></i>

                <span>
                    By creating an account, you agree to use
                    SecondPasal responsibly and provide accurate
                    information.
                </span>

            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="register-btn"
                id="registerBtn">

                <span>

                    <i class="fa-solid fa-user-plus"></i>

                    Create Account

                </span>

                <i class="fa-solid fa-arrow-right"></i>

            </button>

        </form>


        <!-- LOGIN -->

        <div class="login-link">

            <span>
                Already have an account?
            </span>

            <a href="login.php">

                Login

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>


        <!-- SECURITY -->

        <div class="secure-note">

            <i class="fa-solid fa-shield-halved"></i>

            Your information is securely protected.

        </div>

    </div>

</div>


<script src="../assets/js/register.js"></script>

</body>

</html>