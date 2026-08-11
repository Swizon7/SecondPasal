<?php

session_start();
include("../db.php");

$message = "";

// Check if token exists
if(!isset($_GET['token']))
{
    die("Invalid Reset Link.");
}

$token = $_GET['token'];

// Verify token
$stmt = mysqli_prepare($conn,
"SELECT email, expires_at FROM password_resets WHERE token=?");

mysqli_stmt_bind_param($stmt,"s",$token);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    die("Invalid or expired token.");
}

$data = mysqli_fetch_assoc($result);

// Check expiry
if(strtotime($data['expires_at']) < time())
{
    die("This reset link has expired.");
}

$email = $data['email'];

// Update password
if(isset($_POST['change']))
{
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if($password != $confirm)
    {
        $message = "<p style='color:red;'>Passwords do not match.</p>";
    }
    else
    {
        $hash = password_hash($password,PASSWORD_DEFAULT);

        $update = mysqli_prepare($conn,
        "UPDATE users SET password=? WHERE email=?");

        mysqli_stmt_bind_param($update,"ss",$hash,$email);

        if(mysqli_stmt_execute($update))
        {
            // Delete used token
            $delete = mysqli_prepare($conn,
            "DELETE FROM password_resets WHERE email=?");

            mysqli_stmt_bind_param($delete,"s",$email);
            mysqli_stmt_execute($delete);

            header("Location: login.php?reset=success");
            exit();
        }
        else
        {
            $message = "<p style='color:red;'>Password update failed.</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Reset Password | SecondPasal</title>

</head>

<body>

<h2>Reset Password</h2>

<?php echo $message; ?>

<form method="POST">

<input
type="password"
id="password"
name="password"
placeholder="New Password"
required>

<button
type="button"
onclick="toggle('password')">

👁

</button>

<br><br>

<input
type="password"
id="confirm"
name="confirm_password"
placeholder="Confirm Password"
required>

<button
type="button"
onclick="toggle('confirm')">

👁

</button>

<br><br>

<button
type="submit"
name="change">

Update Password

</button>

</form>

<script>

function toggle(id)
{
    let input = document.getElementById(id);

    if(input.type==="password")
    {
        input.type="text";
    }
    else
    {
        input.type="password";
    }
}

</script>

</body>

</html>