<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}

if (
    !isset($_GET['action']) ||
    !isset($_GET['id'])
) {
    header("Location: reports.php");
    exit();
}

$action = $_GET['action'];
$reportId = (int)$_GET['id'];

if ($action === 'resolve') {

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE reports
         SET status = 'resolved'
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $reportId);

    if (mysqli_stmt_execute($stmt)) {

        header("Location: reports.php?success=resolved");

    } else {

        header("Location: reports.php?error=failed");
    }

    exit();
}

header("Location: reports.php");
exit();
?>