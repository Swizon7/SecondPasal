<?php

session_start();
include("../db.php");

if(isset($_POST['login']))
{
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, name, password, role FROM users WHERE email=?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) == 1)
    {
        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password']))
        {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            header("Location: ../homepage/homepage.php");
            exit();
        }
        else
        {
            $error = "Incorrect password.";
        }
    }
    else
    {
        $error = "Email not found.";
    }
}
?>
<?php

if(isset($_GET['registered']))
{
    echo "<p style='color:green;'>Registration Successful. Please login.</p>";
}

if(isset($_GET['reset']))
{
    echo "<p style='color:green;'>Password changed successfully. Please login.</p>";
}

?>
<!DOCTYPE html>
<html>
<head>

<title>Login | SecondPasal</title>

</head>

<body>

<h2>Login</h2>

<?php
if(isset($error))
{
    echo "<p style='color:red;'>$error</p>";
}
?>

<form method="POST">

<input
type="email"
name="email"
placeholder="Email"
required>

<br><br>

<input
type="password"
name="password"
id="password"
placeholder="Password"
required>

<button
type="button"
onclick="togglePassword()">

👁

</button>

<br><br>

<button
type="submit"
name="login">

Login

</button>

</form>

<br>

<a href="forgot_password.php">

Forgot Password?

</a>

<br><br>

Don't have an account?

<a href="register.php">

Register

</a>

<script>

function togglePassword()
{
    let password = document.getElementById("password");

    if(password.type=="password")
    {
        password.type="text";
    }
    else
    {
        password.type="password";
    }
}

</script>

</body>
</html>