<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$action = $_GET['action'];
$userId = (int) $_GET['id'];
$adminId = (int) $_SESSION['user_id'];

/*
 * Never allow an admin to modify their own account
 * through this page.
 */
if ($userId === $adminId) {
    header("Location: users.php?error=self");
    exit();
}

if ($action === 'toggle_status') {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT status
         FROM users
         WHERE id = ?
         AND role != 'admin'
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) !== 1) {
        header("Location: users.php?error=failed");
        exit();
    }

    $user = mysqli_fetch_assoc($result);

    $currentStatus = strtolower($user['status']);

    $newStatus = ($currentStatus === 'active')
        ? 'inactive'
        : 'active';

    $update = mysqli_prepare(
        $conn,
        "UPDATE users
         SET status = ?
         WHERE id = ?
         AND role != 'admin'"
    );

    mysqli_stmt_bind_param(
        $update,
        "si",
        $newStatus,
        $userId
    );

    if (mysqli_stmt_execute($update)) {

        header("Location: users.php?success=status");

    } else {

        header("Location: users.php?error=failed");
    }

    exit();
}

if ($action === 'delete') {

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM users
         WHERE id = ?
         AND role != 'admin'"
    );

    mysqli_stmt_bind_param($stmt, "i", $userId);

    if (mysqli_stmt_execute($stmt)) {

        header("Location: users.php?success=deleted");

    } else {

        header("Location: users.php?error=failed");
    }

    exit();
}

header("Location: users.php");
exit();

?>