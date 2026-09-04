<?php

session_start();
include("../db.php");


// ==========================
// ADMIN SECURITY
// ==========================

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {

    header("Location: admin_login.php?error=unauthorized");
    exit();

}


// ==========================
// ADD / UPDATE CATEGORY
// ==========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST['action'] ?? '';

    $name = trim($_POST['category_name'] ?? '');

    $icon = trim($_POST['icon'] ?? 'fa-box');


    // Category name required
    if ($name === '') {

        header("Location: categories.php?error=empty");
        exit();

    }


    // ==========================
    // ADD CATEGORY
    // ==========================

    if ($action === 'add') {

        // Check duplicate category
        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM categories
             WHERE LOWER(category_name) = LOWER(?)
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $name
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);


        if (mysqli_num_rows($result) > 0) {

            header("Location: categories.php?error=duplicate");
            exit();

        }


        // Insert category + icon
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO categories
            (category_name, icon)
            VALUES (?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $name,
            $icon
        );


        if (mysqli_stmt_execute($stmt)) {

            header("Location: categories.php?success=added");
            exit();

        } else {

            header("Location: categories.php?error=failed");
            exit();

        }

    }


    // ==========================
    // UPDATE CATEGORY
    // ==========================

    if ($action === 'update') {

        $id = (int)($_POST['id'] ?? 0);


        if ($id <= 0) {

            header("Location: categories.php?error=failed");
            exit();

        }


        // Check duplicate name
        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM categories
             WHERE LOWER(category_name) = LOWER(?)
             AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "si",
            $name,
            $id
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);


        if (mysqli_num_rows($result) > 0) {

            header("Location: categories.php?edit=$id&error=duplicate");
            exit();

        }


        // Update category + icon
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE categories
             SET category_name = ?,
                 icon = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $name,
            $icon,
            $id
        );


        if (mysqli_stmt_execute($stmt)) {

            header("Location: categories.php?success=updated");
            exit();

        } else {

            header("Location: categories.php?error=failed");
            exit();

        }

    }

}


// ==========================
// DELETE CATEGORY
// ==========================

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'delete' &&
    isset($_GET['id'])
) {

    $id = (int)$_GET['id'];


    // Check if products are using category
    $check = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM products
         WHERE category_id = ?"
    );

    mysqli_stmt_bind_param(
        $check,
        "i",
        $id
    );

    mysqli_stmt_execute($check);

    $result = mysqli_stmt_get_result($check);

    $row = mysqli_fetch_assoc($result);


    if ((int)$row['total'] > 0) {

        header("Location: categories.php?error=inuse");
        exit();

    }


    // Delete category
    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM categories
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $delete,
        "i",
        $id
    );


    if (mysqli_stmt_execute($delete)) {

        header("Location: categories.php?success=deleted");
        exit();

    } else {

        header("Location: categories.php?error=failed");
        exit();

    }

}


// ==========================
// DEFAULT
// ==========================

header("Location: categories.php");
exit();

?>