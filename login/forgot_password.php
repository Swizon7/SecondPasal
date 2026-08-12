<?php
session_start();

if(isset($_SESSION['user_id']))
{
    header("Location: ../homepage/homepage.php");
    exit();
}
?>
<?php
if (isset($_GET['success'])) {
    echo "<div class='success'>
            If an account with that email exists, a password reset link has been sent.
          </div>";
}

if (isset($_GET['error']) && $_GET['error'] == 'mail') {
    echo "<div class='error'>
            Unable to send the reset email. Please try again.
          </div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Forgot Password | SecondPasal</title>

<link rel="stylesheet" href="../assets/css/forgot_password.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="forgot-container">

    <div class="left-panel">

        <img src="../assets/images/logo.png" class="logo" alt="Logo">

        <h1>SecondPasal</h1>

        <p class="tagline">Buy. Sell. Save.</p>

        <p class="description">
            Enter your registered email address and we'll help you reset your password.
        </p>

    </div>

    <div class="right-panel">

        <h2>Forgot Password</h2>

        <p>Enter your registered email.</p>

        <?php
        if(isset($_GET['error']))
        {
            echo "<div class='error'>Email not found.</div>";
        }
        ?>

        <form action="forgot_password_process.php" method="POST">

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

            <button class="forgot-btn">

                Continue

            </button>

        </form>

        <div class="back">

            <a href="login.php">

                ← Back to Login

            </a>

        </div>

    </div>

</div>

</body>

</html>