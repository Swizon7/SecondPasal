<?php

session_start();
include("../db.php");

$message = "";

if(isset($_POST['reset']))
{
    $email = trim($_POST['email']);

    // Check if the email exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email=?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) == 1)
    {
        // Generate token
        $token = bin2hex(random_bytes(32));

        // Token expires in 1 hour
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Remove old tokens for this email
        $delete = mysqli_prepare($conn, "DELETE FROM password_resets WHERE email=?");
        mysqli_stmt_bind_param($delete, "s", $email);
        mysqli_stmt_execute($delete);

        // Save new token
        $insert = mysqli_prepare($conn,
            "INSERT INTO password_resets(email, token, expires_at)
             VALUES(?,?,?)");

        mysqli_stmt_bind_param($insert, "sss", $email, $token, $expires);
        mysqli_stmt_execute($insert);

        $message = "
        <div style='color:green;'>
            Reset link generated successfully.<br><br>

            <a href='reset_password.php?token=$token'>
                Click here to reset your password
            </a>
        </div>";
    }
    else
    {
        $message = "<div style='color:red;'>Email not found.</div>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password | SecondPasal</title>
</head>
<body>

<h2>Forgot Password</h2>

<?php echo $message; ?>

<form method="POST">

    <input
        type="email"
        name="email"
        placeholder="Enter your email"
        required>

    <br><br>

    <button
        type="submit"
        name="reset">

        Send Reset Link

    </button>

</form>

<br>

<a href="login.php">
    Back to Login
</a>

</body>
</html>