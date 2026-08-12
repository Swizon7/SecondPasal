<?php
session_start();
include("../db.php");

if($_SERVER["REQUEST_METHOD"]!="POST")
{
    header("Location: register.php");
    exit();
}

$name=trim($_POST['name']);
$email=trim($_POST['email']);
$phone=trim($_POST['phone']);
$password=$_POST['password'];
$confirm=$_POST['confirm_password'];

if(empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm))
{
    header("Location: register.php?error=Please fill all fields.");
    exit();
}

if($password != $confirm)
{
    header("Location: register.php?error=Passwords do not match.");
    exit();
}

// Check email exists
$check=mysqli_prepare($conn,"SELECT id FROM users WHERE email=?");
mysqli_stmt_bind_param($check,"s",$email);
mysqli_stmt_execute($check);
$result=mysqli_stmt_get_result($check);

if(mysqli_num_rows($result)>0)
{
    header("Location: register.php?error=Email already registered.");
    exit();
}

// Hash password
$hashed=password_hash($password,PASSWORD_DEFAULT);

// Default role
$role="user";

// Insert user
$stmt=mysqli_prepare($conn,"INSERT INTO users(name,email,phone,password,role) VALUES(?,?,?,?,?)");
mysqli_stmt_bind_param($stmt,"sssss",$name,$email,$phone,$hashed,$role);

if(mysqli_stmt_execute($stmt))
{
    header("Location: login.php?registered=success");
    exit();
}
else
{
    header("Location: register.php?error=Registration failed.");
    exit();
}
?>