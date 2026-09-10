<?php

session_start();
include("../db.php");

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| PRODUCT ID CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || (int) $_GET['id'] <= 0) {
    header("Location: my_listings.php");
    exit();
}

$productId = (int) $_GET['id'];

$error = "";


/*
|--------------------------------------------------------------------------
| FUNCTION - GET PRODUCT IMAGES
|--------------------------------------------------------------------------
*/

function getProductImages($conn, $productId)
{
    $images = [];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, image
         FROM product_images
         WHERE product_id = ?
         ORDER BY id ASC"
    );

    if (!$stmt) {
        return $images;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $productId
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $images;
}


/*
|--------------------------------------------------------------------------
| GET PRODUCT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        title,
        description,
        price,
        location,
        image,
        condition_type,
        category_id
     FROM products
     WHERE id = ?
       AND user_id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Database error.");
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $productId,
    $userId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);

    die("Product not found or you do not have permission to edit it.");
}

$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| GET CATEGORIES
|--------------------------------------------------------------------------
*/

$categoryQuery = mysqli_query(
    $conn,
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

if (!$categoryQuery) {
    die("Failed to load categories.");
}


/*
|--------------------------------------------------------------------------
| GET EXISTING IMAGES
|--------------------------------------------------------------------------
*/

$productImages = getProductImages(
    $conn,
    $productId
);


/*
|--------------------------------------------------------------------------
| OLD PRODUCT IMAGE FALLBACK
|--------------------------------------------------------------------------
|
| Older products may have products.image but no product_images row.
|
|--------------------------------------------------------------------------
*/

if (
    empty($productImages) &&
    !empty($product['image'])
) {

    $productImages[] = [
        'id' => 0,
        'image' => $product['image']
    ];
}


/*
|--------------------------------------------------------------------------
| REMOVE EXISTING PHOTO
|--------------------------------------------------------------------------
|
| URL:
| edit_product.php?id=PRODUCT_ID&remove_photo=IMAGE_ID
|
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['remove_photo']) &&
    (int) $_GET['remove_photo'] > 0
) {

    $removePhotoId = (int) $_GET['remove_photo'];

    /*
    |--------------------------------------------------------------------------
    | Get image and verify ownership
    |--------------------------------------------------------------------------
    */

    $removeStmt = mysqli_prepare(
        $conn,
        "SELECT
            pi.id,
            pi.image
         FROM product_images pi
         INNER JOIN products p
            ON p.id = pi.product_id
         WHERE pi.id = ?
           AND pi.product_id = ?
           AND p.user_id = ?
         LIMIT 1"
    );

    if ($removeStmt) {

        mysqli_stmt_bind_param(
            $removeStmt,
            "iii",
            $removePhotoId,
            $productId,
            $userId
        );

        mysqli_stmt_execute($removeStmt);

        $removeResult =
            mysqli_stmt_get_result($removeStmt);

        if (
            mysqli_num_rows($removeResult) === 1
        ) {

            $photo =
                mysqli_fetch_assoc($removeResult);

            /*
            |--------------------------------------------------------------------------
            | Count actual database photos
            |--------------------------------------------------------------------------
            */

            $currentDbImages =
                getProductImages(
                    $conn,
                    $productId
                );

            /*
            |--------------------------------------------------------------------------
            | Do not allow deleting final image
            |--------------------------------------------------------------------------
            */

            if (count($currentDbImages) <= 1) {

                mysqli_stmt_close(
                    $removeStmt
                );

                header(
                    "Location: edit_product.php?id=" .
                    $productId .
                    "&error=last_photo"
                );

                exit();
            }

            /*
            |--------------------------------------------------------------------------
            | Delete database row
            |--------------------------------------------------------------------------
            */

            $deletePhotoStmt =
                mysqli_prepare(
                    $conn,
                    "DELETE FROM product_images
                     WHERE id = ?
                       AND product_id = ?"
                );

            if ($deletePhotoStmt) {

                mysqli_stmt_bind_param(
                    $deletePhotoStmt,
                    "ii",
                    $removePhotoId,
                    $productId
                );

                if (
                    mysqli_stmt_execute(
                        $deletePhotoStmt
                    )
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Delete physical file
                    |--------------------------------------------------------------------------
                    */

                    $photoPath =
                        "../uploads/" .
                        $photo['image'];

                    if (
                        !empty($photo['image']) &&
                        file_exists($photoPath)
                    ) {

                        unlink($photoPath);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Get remaining images
                    |--------------------------------------------------------------------------
                    */

                    $remainingImages =
                        getProductImages(
                            $conn,
                            $productId
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | First remaining photo becomes main photo
                    |--------------------------------------------------------------------------
                    */

                    $newMainImage = null;

                    if (
                        !empty($remainingImages)
                    ) {

                        $newMainImage =
                            $remainingImages[0]['image'];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update products.image
                    |--------------------------------------------------------------------------
                    */

                    $updateMainStmt =
                        mysqli_prepare(
                            $conn,
                            "UPDATE products
                             SET image = ?
                             WHERE id = ?
                               AND user_id = ?"
                        );

                    if ($updateMainStmt) {

                        mysqli_stmt_bind_param(
                            $updateMainStmt,
                            "sii",
                            $newMainImage,
                            $productId,
                            $userId
                        );

                        mysqli_stmt_execute(
                            $updateMainStmt
                        );

                        mysqli_stmt_close(
                            $updateMainStmt
                        );
                    }


                    mysqli_stmt_close(
                        $deletePhotoStmt
                    );

                    mysqli_stmt_close(
                        $removeStmt
                    );


                    header(
                        "Location: edit_product.php?id=" .
                        $productId .
                        "&success=photo_removed"
                    );

                    exit();

                } else {

                    mysqli_stmt_close(
                        $deletePhotoStmt
                    );
                }
            }
        }

        mysqli_stmt_close(
            $removeStmt
        );
    }
}


/*
|--------------------------------------------------------------------------
| HANDLE PRODUCT UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | FORM VALUES
    |--------------------------------------------------------------------------
    */

    $title =
        trim($_POST['title'] ?? '');

    $categoryId =
        (int) ($_POST['category_id'] ?? 0);

    $description =
        trim($_POST['description'] ?? '');

    $price =
        (float) ($_POST['price'] ?? 0);

    $condition =
        trim($_POST['condition_type'] ?? '');

    $location =
        trim($_POST['location'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $title === '' ||
        $description === '' ||
        $location === '' ||
        $categoryId <= 0 ||
        $price <= 0
    ) {

        $error =
            "Please fill in all required fields correctly.";

    } elseif (
        !in_array(
            $condition,
            ['New', 'Like New', 'Good', 'Fair'],
            true
        )
    ) {

        $error =
            "Invalid product condition.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | MAIN IMAGE
        |--------------------------------------------------------------------------
        */

        $imageName =
            $product['image'];


        /*
        |--------------------------------------------------------------------------
        | ENSURE OLD PRODUCT HAS product_images ENTRY
        |--------------------------------------------------------------------------
        |
        | This converts old products.image records into the new system.
        |
        |--------------------------------------------------------------------------
        */

        $dbImages =
            getProductImages(
                $conn,
                $productId
            );

        if (
            empty($dbImages) &&
            !empty($product['image'])
        ) {

            $legacyImage =
                $product['image'];

            $legacyStmt =
                mysqli_prepare(
                    $conn,
                    "INSERT INTO product_images
                    (product_id, image)
                    VALUES (?, ?)"
                );

            if ($legacyStmt) {

                mysqli_stmt_bind_param(
                    $legacyStmt,
                    "is",
                    $productId,
                    $legacyImage
                );

                mysqli_stmt_execute(
                    $legacyStmt
                );

                mysqli_stmt_close(
                    $legacyStmt
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | REPLACE MAIN IMAGE
        |--------------------------------------------------------------------------
        */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES['image']['error'] !==
                UPLOAD_ERR_OK
            ) {

                $error =
                    "Failed to upload the new main image.";

            } else {

                $image =
                    $_FILES['image'];

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                $fileType =
                    mime_content_type(
                        $image['tmp_name']
                    );


                if (
                    !isset(
                        $allowedTypes[$fileType]
                    )
                ) {

                    $error =
                        "Only JPG, PNG and WEBP images are allowed.";

                } elseif (
                    $image['size'] >
                    5 * 1024 * 1024
                ) {

                    $error =
                        "Image size must be less than 5 MB.";

                } else {

                    $uploadDir =
                        "../uploads/";

                    if (
                        !is_dir(
                            $uploadDir
                        )
                    ) {

                        mkdir(
                            $uploadDir,
                            0777,
                            true
                        );
                    }


                    $extension =
                        $allowedTypes[$fileType];

                    $newImageName =
                        time() .
                        "_" .
                        bin2hex(
                            random_bytes(6)
                        ) .
                        "." .
                        $extension;

                    $targetPath =
                        $uploadDir .
                        $newImageName;


                    /*
                    |--------------------------------------------------------------------------
                    | Move new image
                    |--------------------------------------------------------------------------
                    */

                    if (
                        move_uploaded_file(
                            $image['tmp_name'],
                            $targetPath
                        )
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Find current main image row
                        |--------------------------------------------------------------------------
                        */

                        $mainImageId = 0;

                        $findMainStmt =
                            mysqli_prepare(
                                $conn,
                                "SELECT id
                                 FROM product_images
                                 WHERE product_id = ?
                                   AND image = ?
                                 LIMIT 1"
                            );

                        if ($findMainStmt) {

                            mysqli_stmt_bind_param(
                                $findMainStmt,
                                "is",
                                $productId,
                                $product['image']
                            );

                            mysqli_stmt_execute(
                                $findMainStmt
                            );

                            $findResult =
                                mysqli_stmt_get_result(
                                    $findMainStmt
                                );

                            if (
                                mysqli_num_rows(
                                    $findResult
                                ) === 1
                            ) {

                                $mainRow =
                                    mysqli_fetch_assoc(
                                        $findResult
                                    );

                                $mainImageId =
                                    (int)
                                    $mainRow['id'];
                            }

                            mysqli_stmt_close(
                                $findMainStmt
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Replace existing main image row
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $mainImageId > 0
                        ) {

                            $replaceStmt =
                                mysqli_prepare(
                                    $conn,
                                    "UPDATE product_images
                                     SET image = ?
                                     WHERE id = ?
                                       AND product_id = ?"
                                );

                            if ($replaceStmt) {

                                mysqli_stmt_bind_param(
                                    $replaceStmt,
                                    "sii",
                                    $newImageName,
                                    $mainImageId,
                                    $productId
                                );

                                if (
                                    mysqli_stmt_execute(
                                        $replaceStmt
                                    )
                                ) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Delete old physical main image
                                    |--------------------------------------------------------------------------
                                    */

                                    $oldMainImage =
                                        $product['image'];

                                    if (
                                        !empty(
                                            $oldMainImage
                                        ) &&
                                        $oldMainImage !==
                                        $newImageName
                                    ) {

                                        $oldPath =
                                            "../uploads/" .
                                            $oldMainImage;

                                        if (
                                            file_exists(
                                                $oldPath
                                            )
                                        ) {

                                            unlink(
                                                $oldPath
                                            );
                                        }
                                    }

                                    $imageName =
                                        $newImageName;

                                } else {

                                    unlink(
                                        $targetPath
                                    );

                                    $error =
                                        "Failed to replace the main image.";
                                }

                                mysqli_stmt_close(
                                    $replaceStmt
                                );

                            } else {

                                unlink(
                                    $targetPath
                                );

                                $error =
                                    "Failed to prepare image replacement.";
                            }

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | No main row found
                            |--------------------------------------------------------------------------
                            */

                            $insertMainStmt =
                                mysqli_prepare(
                                    $conn,
                                    "INSERT INTO product_images
                                    (product_id, image)
                                    VALUES (?, ?)"
                                );

                            if ($insertMainStmt) {

                                mysqli_stmt_bind_param(
                                    $insertMainStmt,
                                    "is",
                                    $productId,
                                    $newImageName
                                );

                                if (
                                    mysqli_stmt_execute(
                                        $insertMainStmt
                                    )
                                ) {

                                    $imageName =
                                        $newImageName;

                                } else {

                                    unlink(
                                        $targetPath
                                    );

                                    $error =
                                        "Failed to save the new main image.";
                                }

                                mysqli_stmt_close(
                                    $insertMainStmt
                                );

                            } else {

                                unlink(
                                    $targetPath
                                );

                                $error =
                                    "Failed to prepare image storage.";
                            }
                        }
                    } else {

                        $error =
                            "Failed to upload new image.";
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ADD MULTIPLE NEW PHOTOS
        |--------------------------------------------------------------------------
        */

        if (
            empty($error) &&
            isset($_FILES['new_images']) &&
            is_array(
                $_FILES['new_images']['name']
            )
        ) {

            $currentImages =
                getProductImages(
                    $conn,
                    $productId
                );

            $currentCount =
                count($currentImages);

            $newImageFiles =
                $_FILES['new_images'];

            $validIndexes = [];


            /*
            |--------------------------------------------------------------------------
            | Find selected files
            |--------------------------------------------------------------------------
            */

            for (
                $i = 0;
                $i < count($newImageFiles['name']);
                $i++
            ) {

                if (
                    $newImageFiles['error'][$i] ===
                    UPLOAD_ERR_NO_FILE
                ) {
                    continue;
                }

                $validIndexes[] =
                    $i;
            }


            /*
            |--------------------------------------------------------------------------
            | Maximum 5 photos
            |--------------------------------------------------------------------------
            */

            if (
                $currentCount +
                count($validIndexes) >
                5
            ) {

                $availableSlots =
                    max(
                        0,
                        5 - $currentCount
                    );

                $error =
                    "You can add only " .
                    $availableSlots .
                    " more photo" .
                    (
                        $availableSlots === 1
                        ? "."
                        : "s."
                    );

            } else {

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                $uploadDir =
                    "../uploads/";


                if (
                    !is_dir(
                        $uploadDir
                    )
                ) {

                    mkdir(
                        $uploadDir,
                        0777,
                        true
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Validate ALL files before moving
                |--------------------------------------------------------------------------
                */

                foreach (
                    $validIndexes as $i
                ) {

                    if (
                        $newImageFiles['error'][$i] !==
                        UPLOAD_ERR_OK
                    ) {

                        $error =
                            "One of the new photos could not be uploaded.";

                        break;
                    }


                    $tmpName =
                        $newImageFiles['tmp_name'][$i];

                    $fileSize =
                        $newImageFiles['size'][$i];

                    $fileType =
                        mime_content_type(
                            $tmpName
                        );


                    if (
                        !isset(
                            $allowedTypes[$fileType]
                        )
                    ) {

                        $error =
                            "Only JPG, PNG and WEBP images are allowed.";

                        break;
                    }


                    if (
                        $fileSize >
                        5 * 1024 * 1024
                    ) {

                        $error =
                            "Each image must be less than 5 MB.";

                        break;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Upload files
                |--------------------------------------------------------------------------
                */

                if (empty($error)) {

                    foreach (
                        $validIndexes as $i
                    ) {

                        $extension =
                            $allowedTypes[
                                mime_content_type(
                                    $newImageFiles['tmp_name'][$i]
                                )
                            ];

                        $newFileName =
                            time() .
                            "_" .
                            bin2hex(
                                random_bytes(6)
                            ) .
                            "." .
                            $extension;

                        $targetPath =
                            $uploadDir .
                            $newFileName;


                        if (
                            !move_uploaded_file(
                                $newImageFiles['tmp_name'][$i],
                                $targetPath
                            )
                        ) {

                            $error =
                                "Failed to upload one of the photos.";

                            break;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Insert image into database
                        |--------------------------------------------------------------------------
                        */

                        $insertStmt =
                            mysqli_prepare(
                                $conn,
                                "INSERT INTO product_images
                                (product_id, image)
                                VALUES (?, ?)"
                            );

                        if (!$insertStmt) {

                            unlink(
                                $targetPath
                            );

                            $error =
                                "Failed to save uploaded photo.";

                            break;
                        }


                        mysqli_stmt_bind_param(
                            $insertStmt,
                            "is",
                            $productId,
                            $newFileName
                        );


                        if (
                            !mysqli_stmt_execute(
                                $insertStmt
                            )
                        ) {

                            unlink(
                                $targetPath
                            );

                            mysqli_stmt_close(
                                $insertStmt
                            );

                            $error =
                                "Failed to save uploaded photo.";

                            break;
                        }


                        mysqli_stmt_close(
                            $insertStmt
                        );
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE PRODUCT DETAILS
        |--------------------------------------------------------------------------
        */

        if (empty($error)) {

            /*
            |--------------------------------------------------------------------------
            | Make sure image exists
            |--------------------------------------------------------------------------
            */

            if (
                empty($imageName)
            ) {

                $latestImages =
                    getProductImages(
                        $conn,
                        $productId
                    );

                if (
                    !empty(
                        $latestImages
                    )
                ) {

                    $imageName =
                        $latestImages[0]['image'];
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Update product
            |--------------------------------------------------------------------------
            */

            $update =
                mysqli_prepare(
                    $conn,
                    "UPDATE products
                     SET title = ?,
                         description = ?,
                         price = ?,
                         location = ?,
                         image = ?,
                         condition_type = ?,
                         category_id = ?
                     WHERE id = ?
                       AND user_id = ?"
                );


            if (!$update) {

                $error =
                    "Failed to prepare product update.";

            } else {

                mysqli_stmt_bind_param(
                    $update,
                    "ssdsssiii",
                    $title,
                    $description,
                    $price,
                    $location,
                    $imageName,
                    $condition,
                    $categoryId,
                    $productId,
                    $userId
                );


                if (
                    mysqli_stmt_execute(
                        $update
                    )
                ) {

                    mysqli_stmt_close(
                        $update
                    );

                    header(
                        "Location: my_listings.php?success=updated"
                    );

                    exit();

                } else {

                    $error =
                        "Failed to update product.";

                    mysqli_stmt_close(
                        $update
                    );
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KEEP FORM VALUES
    |--------------------------------------------------------------------------
    */

    $product['title'] =
        $title;

    $product['description'] =
        $description;

    $product['price'] =
        $price;

    $product['location'] =
        $location;

    $product['condition_type'] =
        $condition;

    $product['category_id'] =
        $categoryId;
}


/*
|--------------------------------------------------------------------------
| REFRESH IMAGES BEFORE DISPLAY
|--------------------------------------------------------------------------
*/

$productImages =
    getProductImages(
        $conn,
        $productId
    );


/*
|--------------------------------------------------------------------------
| FALLBACK FOR OLD PRODUCTS
|--------------------------------------------------------------------------
*/

if (
    empty($productImages) &&
    !empty($product['image'])
) {

    $productImages[] = [
        'id' => 0,
        'image' => $product['image']
    ];
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include("../includes/header.php");

?>

<link rel="stylesheet" href="../assets/css/edit_product.css?v=2">


<div class="edit-page">

    <div class="edit-card">

        <!-- PAGE HEADER -->

        <div class="edit-title">

            <span class="edit-page-label">
                <i class="fa-solid fa-pen"></i>
                LISTING MANAGEMENT
            </span>

            <h1>
                <i class="fa-solid fa-pen-to-square"></i>
                Edit Product
            </h1>

            <p>
                Update your product information
                and keep your listing accurate.
            </p>

        </div>


        <!-- ERROR -->

        <?php if (
            isset($_GET['error']) &&
            $_GET['error'] === 'last_photo'
        ) { ?>

            <div class="message error-message">

                <i class="fa-solid fa-circle-exclamation"></i>

                <span>
                    You must keep at least one product photo.
                </span>

            </div>

        <?php } ?>


        <!-- SUCCESS -->

        <?php if (
            isset($_GET['success']) &&
            $_GET['success'] === 'photo_removed'
        ) { ?>

            <div class="message success-message">

                <i class="fa-solid fa-circle-check"></i>

                <span>
                    Photo removed successfully.
                </span>

            </div>

        <?php } ?>


        <!-- PHP ERROR -->

        <?php if (!empty($error)) { ?>

            <div class="message error-message">

                <i class="fa-solid fa-circle-exclamation"></i>

                <span>
                    <?php echo htmlspecialchars($error); ?>
                </span>

            </div>

        <?php } ?>


        <!-- FORM -->

        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- =========================================
                 LISTING DETAILS
            ========================================== -->

            <section class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>

                    <div>
                        <h2>
                            Listing Details
                        </h2>

                        <p>
                            Update the basic information of your product.
                        </p>
                    </div>

                </div>


                <!-- TITLE -->

                <div class="form-group">

                    <label>
                        Product Title
                    </label>

                    <div class="input-field">

                        <i class="fa-solid fa-heading"></i>

                        <input
                            type="text"
                            name="title"
                            maxlength="150"
                            placeholder="e.g. HP Laptop 15s"
                            required
                            value="<?php echo htmlspecialchars($product['title']); ?>"
                        >

                    </div>

                </div>


                <!-- CATEGORY + PRICE -->

                <div class="form-row">

                    <div class="form-group">

                        <label>
                            Category
                        </label>

                        <div class="input-field">

                            <i class="fa-solid fa-list"></i>

                            <select
                                name="category_id"
                                required
                            >

                                <option value="">
                                    Select Category
                                </option>

                                <?php while (
                                    $category =
                                    mysqli_fetch_assoc(
                                        $categoryQuery
                                    )
                                ) { ?>

                                    <option
                                        value="<?php echo (int) $category['id']; ?>"
                                        <?php
                                        echo (
                                            (int) $category['id'] ===
                                            (int) $product['category_id']
                                        )
                                        ? 'selected'
                                        : '';
                                        ?>
                                    >

                                        <?php echo htmlspecialchars(
                                            $category['category_name']
                                        ); ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                    </div>


                    <div class="form-group">

                        <label>
                            Price (Rs.)
                        </label>

                        <div class="input-field">

                            <i class="fa-solid fa-money-bill"></i>

                            <input
                                type="number"
                                name="price"
                                min="1"
                                step="0.01"
                                required
                                value="<?php echo htmlspecialchars($product['price']); ?>"
                            >

                        </div>

                    </div>

                </div>


                <!-- CONDITION -->

                <div class="form-group">

                    <label>
                        Condition
                    </label>

                    <div class="condition-options">

                        <?php

                        $conditions = [
                            'New',
                            'Like New',
                            'Good',
                            'Fair'
                        ];

                        foreach (
                            $conditions as $conditionValue
                        ) {

                        ?>

                            <label class="condition-option">

                                <input
                                    type="radio"
                                    name="condition_type"
                                    value="<?php echo htmlspecialchars($conditionValue); ?>"
                                    <?php
                                    echo (
                                        $product['condition_type'] ===
                                        $conditionValue
                                    )
                                    ? 'checked'
                                    : '';
                                    ?>
                                    required
                                >

                                <span>
                                    <?php echo htmlspecialchars(
                                        $conditionValue
                                    ); ?>
                                </span>

                            </label>

                        <?php } ?>

                    </div>

                </div>

            </section>


            <!-- =========================================
                 LOCATION & DESCRIPTION
            ========================================== -->

            <section class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>

                    <div>

                        <h2>
                            Location & Description
                        </h2>

                        <p>
                            Keep the product information clear and useful.
                        </p>

                    </div>

                </div>


                <!-- LOCATION -->

                <div class="form-group">

                    <label>
                        Location
                    </label>

                    <div class="input-field">

                        <i class="fa-solid fa-location-dot"></i>

                        <input
                            type="text"
                            name="location"
                            maxlength="150"
                            placeholder="e.g. Kathmandu"
                            required
                            value="<?php echo htmlspecialchars($product['location']); ?>"
                        >

                    </div>

                </div>


                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="6"
                        placeholder="Describe your product..."
                        required
                    ><?php echo htmlspecialchars($product['description']); ?></textarea>

                </div>

            </section>


            <!-- =========================================
                 PRODUCT PHOTOS
            ========================================== -->

            <section class="form-section">

                <div class="section-heading">

                    <div class="section-icon">
                        <i class="fa-solid fa-camera"></i>
                    </div>

                    <div>

                        <h2>
                            Product Photos
                        </h2>

                        <p>
                            Manage your listing photos and keep your best image first.
                        </p>

                    </div>

                </div>


                <!-- PHOTO MANAGEMENT -->

                <div class="edit-photo-area">

                    <div class="photo-management-header">

                        <div>

                            <strong>
                                Current Photos
                            </strong>

                            <span>
                                Your first photo is used as the main listing image.
                            </span>

                        </div>

                        <span class="photo-count">

                            <i class="fa-solid fa-images"></i>

                            <?php echo count($productImages); ?>
                            / 5

                        </span>

                    </div>


                    <!-- EXISTING PHOTO GRID -->

                    <div class="edit-photo-grid">

                        <?php foreach (
                            $productImages
                            as $index => $imageRow
                        ) { ?>

                            <?php

                            $editImage =
                                "no-image.png";

                            if (
                                !empty(
                                    $imageRow['image']
                                )
                            ) {

                                $editImagePath =
                                    "../uploads/" .
                                    $imageRow['image'];

                                if (
                                    file_exists(
                                        $editImagePath
                                    )
                                ) {

                                    $editImage =
                                        $imageRow['image'];
                                }
                            }

                            ?>

                            <div class="edit-photo-card">

                                <img
                                    src="../uploads/<?php echo htmlspecialchars($editImage); ?>"
                                    alt="Product Photo <?php echo $index + 1; ?>"
                                >


                                <!-- IMAGE NUMBER -->

                                <span class="photo-number">
                                    <?php echo $index + 1; ?>
                                </span>


                                <!-- MAIN PHOTO -->

                                <?php if (
                                    $index === 0
                                ) { ?>

                                    <span class="main-photo-badge">

                                        <i class="fa-solid fa-star"></i>

                                        Main Photo

                                    </span>

                                <?php } ?>


                                <!-- REMOVE -->

                                <?php if (
                                    (int) $imageRow['id'] > 0 &&
                                    count($productImages) > 1
                                ) { ?>

                                    <a
                                        href="edit_product.php?id=<?php echo $productId; ?>&remove_photo=<?php echo (int) $imageRow['id']; ?>"
                                        class="remove-existing-photo"
                                        title="Remove Photo"
                                        onclick="return confirm('Are you sure you want to remove this photo?');"
                                    >

                                        <i class="fa-solid fa-xmark"></i>

                                    </a>

                                <?php } ?>

                            </div>

                        <?php } ?>

                    </div>

                </div>


                <!-- =====================================
                     REPLACE MAIN PHOTO
                ====================================== -->

                <div class="replace-photo-area">

                    <div class="replace-photo-header">

                        <div>

                            <strong>
                                Replace Main Photo
                            </strong>

                            <span>
                                Upload a new image to replace the current main photo.
                            </span>

                        </div>

                        <span class="recommended-badge">
                            <i class="fa-solid fa-star"></i>
                            Main
                        </span>

                    </div>


                    <label
                        class="main-upload-box"
                        for="mainImage"
                    >

                        <input
                            type="file"
                            name="image"
                            id="mainImage"
                            accept="image/jpeg,image/png,image/webp"
                        >

                        <div class="main-upload-content">

                            <div class="main-upload-icon">
                                <i class="fa-solid fa-image"></i>
                            </div>

                            <div>

                                <strong>
                                    Choose a new main photo
                                </strong>

                                <span>
                                    JPG, PNG or WEBP • Maximum 5 MB
                                </span>

                            </div>

                            <div class="main-file-name" id="mainFileName">
                                No new main photo selected
                            </div>

                        </div>

                    </label>

                </div>


                <!-- =====================================
                     ADD MORE PHOTOS
                ====================================== -->

                <?php if (
                    count($productImages) < 5
                ) { ?>

                    <div class="add-photos-area">

                        <label class="replace-label">

                            Add More Photos

                        </label>

                        <div
                            class="edit-upload-box"
                            id="editUploadBox"
                        >

                            <input
                                type="file"
                                name="new_images[]"
                                id="newImages"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                            >

                            <div class="edit-upload-content">

                                <div class="upload-icon">

                                    <i class="fa-solid fa-cloud-arrow-up"></i>

                                </div>

                                <strong>
                                    Add product photos
                                </strong>

                                <span>
                                    JPG, PNG or WEBP • Maximum 5 MB per photo
                                </span>

                            </div>

                        </div>


                        <div
                            class="edit-photo-counter"
                            id="editPhotoCounter"
                        >
                            You can add
                            <?php echo 5 - count($productImages); ?>
                            more photos
                        </div>


                        <div
                            class="edit-new-preview"
                            id="editNewPreview"
                        >
                        </div>

                    </div>

                <?php } else { ?>

                    <div class="photo-limit-message">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            You have reached the maximum of 5 photos.
                        </span>

                    </div>

                <?php } ?>

            </section>


            <!-- =========================================
                 ACTIONS
            ========================================== -->

            <div class="edit-actions">

                <a
                    href="my_listings.php"
                    class="cancel-btn"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Cancel

                </a>


                <button
                    type="submit"
                    class="save-btn"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Changes

                </button>

            </div>


            <div class="edit-note">

                <i class="fa-solid fa-circle-info"></i>

                Changes will update your existing listing.

            </div>

        </form>

    </div>

</div>


<!-- =========================================
     JAVASCRIPT
========================================== -->

<script>

const newImagesInput =
    document.getElementById("newImages");

const editNewPreview =
    document.getElementById("editNewPreview");

const editPhotoCounter =
    document.getElementById("editPhotoCounter");

const mainImageInput =
    document.getElementById("mainImage");

const mainFileName =
    document.getElementById("mainFileName");


/*
|--------------------------------------------------------------------------
| PHOTO LIMIT
|--------------------------------------------------------------------------
*/

const currentPhotoCount =
    <?php echo count($productImages); ?>;

const maxPhotos = 5;

const remainingSlots =
    Math.max(
        0,
        maxPhotos - currentPhotoCount
    );


/*
|--------------------------------------------------------------------------
| UPDATE PHOTO COUNTER
|--------------------------------------------------------------------------
*/

function updatePhotoCounter(count)
{

    if (!editPhotoCounter) {
        return;
    }

    if (count === 0) {

        editPhotoCounter.textContent =
            "You can add " +
            remainingSlots +
            " more photo" +
            (remainingSlots === 1 ? "" : "s");

    } else {

        const remaining =
            Math.max(
                0,
                remainingSlots - count
            );

        editPhotoCounter.textContent =
            count +
            " new photo" +
            (count === 1 ? "" : "s") +
            " selected • " +
            remaining +
            " slot" +
            (remaining === 1 ? "" : "s") +
            " remaining";
    }
}


/*
|--------------------------------------------------------------------------
| MAIN IMAGE NAME
|--------------------------------------------------------------------------
*/

if (mainImageInput) {

    mainImageInput.addEventListener(
        "change",
        function () {

            if (
                this.files &&
                this.files.length > 0
            ) {

                const file =
                    this.files[0];

                if (
                    ![
                        "image/jpeg",
                        "image/png",
                        "image/webp"
                    ].includes(file.type)
                ) {

                    alert(
                        "Only JPG, PNG and WEBP images are allowed."
                    );

                    this.value = "";

                    mainFileName.textContent =
                        "No new main photo selected";

                    return;
                }


                if (
                    file.size >
                    5 * 1024 * 1024
                ) {

                    alert(
                        "Main image must be less than 5 MB."
                    );

                    this.value = "";

                    mainFileName.textContent =
                        "No new main photo selected";

                    return;
                }


                mainFileName.textContent =
                    file.name;

            } else {

                mainFileName.textContent =
                    "No new main photo selected";
            }

        }
    );
}


/*
|--------------------------------------------------------------------------
| MULTIPLE IMAGE SELECTION
|--------------------------------------------------------------------------
*/

if (newImagesInput) {

    updatePhotoCounter(0);


    newImagesInput.addEventListener(
        "change",
        function () {

            editNewPreview.innerHTML = "";

            let files =
                Array.from(this.files);


            /*
            |--------------------------------------------------------------------------
            | Limit number
            |--------------------------------------------------------------------------
            */

            if (
                files.length >
                remainingSlots
            ) {

                alert(
                    "You can add only " +
                    remainingSlots +
                    " more photo" +
                    (remainingSlots === 1
                        ? "."
                        : "s.")
                );

                files =
                    files.slice(
                        0,
                        remainingSlots
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Validate files
            |--------------------------------------------------------------------------
            */

            const validFiles = [];


            files.forEach(
                function (file) {

                    if (
                        ![
                            "image/jpeg",
                            "image/png",
                            "image/webp"
                        ].includes(file.type)
                    ) {

                        alert(
                            file.name +
                            " is not a JPG, PNG or WEBP image."
                        );

                        return;
                    }


                    if (
                        file.size >
                        5 * 1024 * 1024
                    ) {

                        alert(
                            file.name +
                            " is larger than 5 MB."
                        );

                        return;
                    }


                    validFiles.push(file);

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Rebuild input
            |--------------------------------------------------------------------------
            */

            const dataTransfer =
                new DataTransfer();


            validFiles.forEach(
                function (file) {

                    dataTransfer.items.add(
                        file
                    );

                }
            );


            this.files =
                dataTransfer.files;


            updatePhotoCounter(
                validFiles.length
            );


            /*
            |--------------------------------------------------------------------------
            | Show previews
            |--------------------------------------------------------------------------
            */

            validFiles.forEach(
                function (
                    file,
                    index
                ) {

                    const reader =
                        new FileReader();


                    reader.onload =
                        function (event) {

                            const preview =
                                document.createElement(
                                    "div"
                                );

                            preview.className =
                                "edit-new-photo";


                            preview.innerHTML = `

                                <img
                                    src="${event.target.result}"
                                    alt="New Product Photo ${index + 1}"
                                >

                                <span class="new-photo-number">
                                    ${index + 1}
                                </span>

                            `;


                            editNewPreview.appendChild(
                                preview
                            );

                        };


                    reader.readAsDataURL(
                        file
                    );

                }
            );

        }
    );
}

</script>


<?php include("../includes/footer.php"); ?>