<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}

/* =========================
   ADD / UPDATE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST['action'] ?? '';
    $name = trim($_POST['category_name'] ?? '');

    if ($name === '') {
        header("Location: categories.php?error=empty");
        exit();
    }

    if ($action === 'add') {

        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM categories
             WHERE LOWER(category_name) = LOWER(?)
             LIMIT 1"
        );

        mysqli_stmt_bind_param($check, "s", $name);
        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {
            header("Location: categories.php?error=duplicate");
            exit();
        }

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO categories (category_name)
             VALUES (?)"
        );

        mysqli_stmt_bind_param($stmt, "s", $name);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: categories.php?success=added");
        } else {
            header("Location: categories.php?error=failed");
        }

        exit();
    }

    if ($action === 'update') {

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            header("Location: categories.php?error=failed");
            exit();
        }

        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM categories
             WHERE LOWER(category_name) = LOWER(?)
             AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($check, "si", $name, $id);
        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {
            header("Location: categories.php?edit=$id&error=duplicate");
            exit();
        }

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE categories
             SET category_name = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "si", $name, $id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: categories.php?success=updated");
        } else {
            header("Location: categories.php?error=failed");
        }

        exit();
    }
}

/* =========================
   DELETE
========================= */

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'delete' &&
    isset($_GET['id'])
) {

    $id = (int)$_GET['id'];

    $check = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM products
         WHERE category_id = ?"
    );

    mysqli_stmt_bind_param($check, "i", $id);
    mysqli_stmt_execute($check);

    $result = mysqli_stmt_get_result($check);
    $row = mysqli_fetch_assoc($result);

    if ((int)$row['total'] > 0) {
        header("Location: categories.php?error=inuse");
        exit();
    }

    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM categories
         WHERE id = ?"
    );

    mysqli_stmt_bind_param($delete, "i", $id);

    if (mysqli_stmt_execute($delete)) {
        header("Location: categories.php?success=deleted");
    } else {
        header("Location: categories.php?error=failed");
    }

    exit();
}

header("Location: categories.php");
exit();