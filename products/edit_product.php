<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my_listings.php");
    exit();
}

$productId = (int) $_GET['id'];
$userId = $_SESSION['user_id'];

$error = "";

// Get the product and make sure it belongs to the logged-in user
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, description, price, location,
            image, condition_type, category_id
     FROM products
     WHERE id = ? AND user_id = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $productId, $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Product not found or you do not have permission to edit it.");
}

$product = mysqli_fetch_assoc($result);

// Get categories
$categoryQuery = mysqli_query(
    $conn,
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST['title']);
    $categoryId = (int) $_POST['category_id'];
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $condition = $_POST['condition_type'];
    $location = trim($_POST['location']);

    if (
        empty($title) ||
        empty($description) ||
        empty($location) ||
        $categoryId <= 0 ||
        $price <= 0
    ) {
        $error = "Please fill in all required fields correctly.";
    }
    elseif (!in_array($condition, ['New', 'Like New', 'Good', 'Fair'], true)) {
        $error = "Invalid product condition.";
    }
    else {

        // Keep current image unless a new image is uploaded
        $imageName = $product['image'];

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK
        ) {

            $image = $_FILES['image'];

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            $fileType = mime_content_type($image['tmp_name']);

            if (!isset($allowedTypes[$fileType])) {

                $error = "Only JPG, PNG and WEBP images are allowed.";

            }
            elseif ($image['size'] > 5 * 1024 * 1024) {

                $error = "Image size must be less than 5 MB.";

            }
            else {

                $uploadDir = "../uploads/";

                $extension = $allowedTypes[$fileType];

                $newImageName =
                    time() . "_" .
                    bin2hex(random_bytes(5)) .
                    "." . $extension;

                $targetPath = $uploadDir . $newImageName;

                if (move_uploaded_file($image['tmp_name'], $targetPath)) {

                    // Delete old image if it exists
                    if (
                        !empty($product['image']) &&
                        file_exists($uploadDir . $product['image'])
                    ) {
                        unlink($uploadDir . $product['image']);
                    }

                    $imageName = $newImageName;

                } else {

                    $error = "Failed to upload new image.";
                }
            }
        }

        if (empty($error)) {

            $update = mysqli_prepare(
                $conn,
                "UPDATE products
                 SET title = ?,
                     description = ?,
                     price = ?,
                     location = ?,
                     image = ?,
                     condition_type = ?,
                     category_id = ?
                 WHERE id = ? AND user_id = ?"
            );

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

            if (mysqli_stmt_execute($update)) {

                header("Location: my_listings.php?success=updated");
                exit();

            } else {

                $error = "Failed to update product.";
            }

            mysqli_stmt_close($update);
        }
    }

    // Keep submitted values visible if validation fails
    $product['title'] = $title;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['location'] = $location;
    $product['condition_type'] = $condition;
    $product['category_id'] = $categoryId;
}

include("../includes/header.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Edit Product | SecondPasal</title>

    <link rel="stylesheet" href="../assets/css/upload_product.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="upload-page">

    <div class="upload-card">

        <div class="upload-title">

            <h1>
                <i class="fa-solid fa-pen-to-square"></i>
                Edit Product
            </h1>

            <p>
                Update your product information.
            </p>

        </div>

        <?php if (!empty($error)) { ?>

            <div class="error-message">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">

                <label>Product Title</label>

                <div class="input-field">

                    <i class="fa-solid fa-heading"></i>

                    <input
                        type="text"
                        name="title"
                        required
                        value="<?php echo htmlspecialchars($product['title']); ?>">

                </div>

            </div>

            <div class="form-group">

                <label>Category</label>

                <div class="input-field">

                    <i class="fa-solid fa-list"></i>

                    <select name="category_id" required>

                        <option value="">
                            Select Category
                        </option>

                        <?php while ($category = mysqli_fetch_assoc($categoryQuery)) { ?>

                            <option
                                value="<?php echo $category['id']; ?>"
                                <?php
                                echo ($category['id'] == $product['category_id'])
                                    ? 'selected'
                                    : '';
                                ?>>

                                <?php echo htmlspecialchars($category['category_name']); ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>

            </div>

            <div class="form-group">

                <label>Price (Rs.)</label>

                <div class="input-field">

                    <i class="fa-solid fa-money-bill"></i>

                    <input
                        type="number"
                        name="price"
                        min="1"
                        step="0.01"
                        required
                        value="<?php echo htmlspecialchars($product['price']); ?>">

                </div>

            </div>

            <div class="form-group">

                <label>Condition</label>

                <div class="condition-options">

                    <?php
                    $conditions = ['New', 'Like New', 'Good', 'Fair'];

                    foreach ($conditions as $condition) {
                    ?>

                        <label class="condition-option">

                            <input
                                type="radio"
                                name="condition_type"
                                value="<?php echo $condition; ?>"
                                <?php
                                echo ($product['condition_type'] === $condition)
                                    ? 'checked'
                                    : '';
                                ?>>

                            <span><?php echo $condition; ?></span>

                        </label>

                    <?php } ?>

                </div>

            </div>

            <div class="form-group">

                <label>Location</label>

                <div class="input-field">

                    <i class="fa-solid fa-location-dot"></i>

                    <input
                        type="text"
                        name="location"
                        required
                        value="<?php echo htmlspecialchars($product['location']); ?>">

                </div>

            </div>

            <div class="form-group">

                <label>Description</label>

                <textarea
                    name="description"
                    rows="6"
                    required><?php echo htmlspecialchars($product['description']); ?></textarea>

            </div>

            <div class="form-group">

                <label>Current Image</label>

                <div class="image-upload">

                    <?php if (!empty($product['image'])) { ?>

                        <img
                            src="../uploads/<?php echo htmlspecialchars($product['image']); ?>"
                            alt="Current Product Image"
                            style="display:block;">

                    <?php } ?>

                    <p>Choose a new image only if you want to replace the current one.</p>

                    <input
                        type="file"
                        name="image"
                        id="image"
                        accept=".jpg,.jpeg,.png,.webp">

                    <img
                        id="imagePreview"
                        src=""
                        alt="New Image Preview"
                        style="display:none;">

                </div>

            </div>

            <button type="submit" class="upload-btn">

                <i class="fa-solid fa-floppy-disk"></i>
                Save Changes

            </button>

        </form>

    </div>

</div>

<script src="../assets/js/upload_product.js"></script>

</body>

</html>