<?php

session_start();
include("../db.php");

if(isset($_POST['register']))
{
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Check if all fields are filled
    if(empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm))
    {
        $error = "Please fill in all fields.";
    }

    // Passwords must match
    elseif($password != $confirm)
    {
        $error = "Passwords do not match.";
    }

    else
    {
        // Check if email already exists
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if(mysqli_stmt_num_rows($check) > 0)
        {
            $error = "Email already exists.";
        }
        else
        {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = mysqli_prepare($conn,
                "INSERT INTO users(name,email,phone,password)
                 VALUES(?,?,?,?)");

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $phone,
                $hashedPassword
            );

            if(mysqli_stmt_execute($stmt))
            {
                header("Location: login.php?registered=1");
                exit();
            }
            else
            {
                $error = "Registration failed.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Register | SecondPasal</title>

</head>

<body>

<h2>Create Account</h2>

<?php
if(isset($error))
{
    echo "<p style='color:red;'>$error</p>";
}
?>

<form method="POST">

    <input
        type="text"
        name="name"
        placeholder="Full Name"
        required>

    <br><br>

    <input
        type="email"
        name="email"
        placeholder="Email"
        required>

    <br><br>

    <input
        type="text"
        name="phone"
        placeholder="Phone Number"
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
        onclick="togglePassword('password')">
        👁
    </button>

    <br><br>

    <input
        type="password"
        name="confirm_password"
        id="confirm_password"
        placeholder="Confirm Password"
        required>

    <button
        type="button"
        onclick="togglePassword('confirm_password')">
        👁
    </button>

    <br><br>

    <button
        type="submit"
        name="register">

        Register

    </button>

</form>

<p>

Already have an account?

<a href="login.php">

Login

</a>

</p>

<script>

function togglePassword(id)
{
    let input = document.getElementById(id);

    if(input.type === "password")
    {
        input.type = "text";
    }
    else
    {
        input.type = "password";
    }
}

</script>

</body>

</html>