<?php

session_start();
include("../db.php");


/* =========================================================
   ADMIN LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}


/* =========================================================
   CHECK ACTION AND PRODUCT ID
========================================================= */

if (
    !isset($_GET['action']) ||
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: products.php");
    exit();
}


$action = $_GET['action'];
$productId = (int) $_GET['id'];


/* =========================================================
   TOGGLE PRODUCT STATUS
========================================================= */

if ($action === 'toggle_status') {


    /* -----------------------------------------------
       GET CURRENT STATUS
    ------------------------------------------------ */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT status
         FROM products
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $productId
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) !== 1) {

        mysqli_stmt_close($stmt);

        header("Location: products.php?error=1");
        exit();
    }


    $product = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    /* -----------------------------------------------
       CHANGE STATUS
    ------------------------------------------------ */

    if ($product['status'] === 'available') {

        $newStatus = 'sold';

    } else {

        $newStatus = 'available';

    }


    /* -----------------------------------------------
       UPDATE STATUS
    ------------------------------------------------ */

    $update = mysqli_prepare(
        $conn,
        "UPDATE products
         SET status = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $update,
        "si",
        $newStatus,
        $productId
    );


    if (mysqli_stmt_execute($update)) {

        mysqli_stmt_close($update);

        header(
            "Location: products.php?success=status"
        );

        exit();

    }


    mysqli_stmt_close($update);

    header("Location: products.php?error=1");
    exit();
}


/* =========================================================
   DELETE PRODUCT
========================================================= */

if ($action === 'delete') {


    /* -----------------------------------------------
       GET MAIN PRODUCT IMAGE
    ------------------------------------------------ */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT image
         FROM products
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $productId
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) !== 1) {

        mysqli_stmt_close($stmt);

        header("Location: products.php?error=1");
        exit();
    }


    $product = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    /* -----------------------------------------------
       GET ALL PRODUCT IMAGES
    ------------------------------------------------ */

    $images = [];


    $imageStmt = mysqli_prepare(
        $conn,
        "SELECT image
         FROM product_images
         WHERE product_id = ?"
    );

    mysqli_stmt_bind_param(
        $imageStmt,
        "i",
        $productId
    );

    mysqli_stmt_execute($imageStmt);

    $imageResult =
        mysqli_stmt_get_result($imageStmt);


    while (
        $imageRow =
        mysqli_fetch_assoc($imageResult)
    ) {

        if (!empty($imageRow['image'])) {

            $images[] =
                $imageRow['image'];
        }
    }


    mysqli_stmt_close($imageStmt);


    /* -----------------------------------------------
       DELETE PRODUCT
    ------------------------------------------------ */

    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM products
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $delete,
        "i",
        $productId
    );


    if (mysqli_stmt_execute($delete)) {


        mysqli_stmt_close($delete);


        /* -------------------------------------------
           DELETE MAIN IMAGE
        -------------------------------------------- */

        if (!empty($product['image'])) {

            $imagePath =
                "../uploads/"
                . $product['image'];


            if (file_exists($imagePath)) {

                unlink($imagePath);

            }
        }


        /* -------------------------------------------
           DELETE ALL ADDITIONAL IMAGES
        -------------------------------------------- */

        foreach ($images as $image) {

            $imagePath =
                "../uploads/"
                . $image;


            if (file_exists($imagePath)) {

                unlink($imagePath);

            }

        }


        /* -------------------------------------------
           SUCCESS
        -------------------------------------------- */

        header(
            "Location: products.php?success=deleted"
        );

        exit();

    }


    /* -----------------------------------------------
       DELETE FAILED
    ------------------------------------------------ */

    mysqli_stmt_close($delete);

    header("Location: products.php?error=1");
    exit();
}


/* =========================================================
   INVALID ACTION
========================================================= */

header("Location: products.php");
exit();

?>