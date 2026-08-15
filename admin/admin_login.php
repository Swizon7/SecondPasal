<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard.php");
        exit();
    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Admin Login | SecondPasal</title>

    <link rel="stylesheet" href="../assets/css/admin_login.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="admin-login-container">

    <div class="admin-login-card">

        <div class="admin-icon">

            <i class="fa-solid fa-user-shield"></i>

        </div>

        <h1>Admin Login</h1>

        <p class="subtitle">
            SecondPasal Administration Panel
        </p>

        <?php

        if (isset($_GET['error'])) {

            if ($_GET['error'] === 'invalid') {

                echo '<div class="error-message">
                        Invalid admin email or password.
                      </div>';

            } elseif ($_GET['error'] === 'unauthorized') {

                echo '<div class="error-message">
                        You do not have administrator access.
                      </div>';
            }

        }

        ?>

        <form action="admin_login_process.php" method="POST">

            <div class="input-group">

                <label>Email</label>

                <div class="input-field">

                    <i class="fa-solid fa-envelope"></i>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter admin email"
                        required>

                </div>

            </div>

            <div class="input-group">

                <label>Password</label>

                <div class="input-field">

                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Enter admin password"
                        required>

                    <i
                        class="fa-solid fa-eye toggle-password"
                        id="togglePassword">
                    </i>

                </div>

            </div>

            <button type="submit" class="admin-login-btn">

                <i class="fa-solid fa-right-to-bracket"></i>
                Login as Admin

            </button>

        </form>

        <a href="../login/login.php" class="back-link">
            ← Back to User Login
        </a>

    </div>

</div>

<script>

const password = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");

togglePassword.addEventListener("click", function () {

    if (password.type === "password") {

        password.type = "text";

        this.classList.remove("fa-eye");
        this.classList.add("fa-eye-slash");

    } else {

        password.type = "password";

        this.classList.remove("fa-eye-slash");
        this.classList.add("fa-eye");

    }

});

</script>

</body>

</html>