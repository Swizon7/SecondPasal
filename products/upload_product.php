<?php

session_start();
include("../db.php");

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Get categories
$categoryQuery = mysqli_query(
    $conn,
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $userId = $_SESSION['user_id'];

    $title = trim($_POST['title']);
    $categoryId = (int) $_POST['category_id'];
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $condition = $_POST['condition_type'];
    $location = trim($_POST['location']);

    // Basic validation
    if (
        empty($title) ||
        empty($description) ||
        empty($location) ||
        $categoryId <= 0 ||
        $price <= 0
    ) {
        $error = "Please fill in all required fields correctly.";
    }

    // Validate condition
    elseif (!in_array($condition, ['New', 'Like New', 'Good', 'Fair'], true)) {
        $error = "Invalid product condition.";
    }

    // Image validation
    elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a product image.";
    }

    else {

        $image = $_FILES['image'];

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        $fileType = mime_content_type($image['tmp_name']);

        if (!isset($allowedTypes[$fileType])) {

            $error = "Only JPG, PNG and WEBP images are allowed.";

        } elseif ($image['size'] > 5 * 1024 * 1024) {

            $error = "Image size must be less than 5 MB.";

        } else {

            $uploadDir = "../uploads/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Create a unique filename
            $extension = $allowedTypes[$fileType];
            $fileName = time() . "_" . bin2hex(random_bytes(5)) . "." . $extension;

            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($image['tmp_name'], $targetPath)) {

                $error = "Failed to upload image.";

            } else {

                // Insert product
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO products
                    (user_id, category_id, title, description, price, location, image, condition_type, status, views)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available', 0)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "iissdsss",
                    $userId,
                    $categoryId,
                    $title,
                    $description,
                    $price,
                    $location,
                    $fileName,
                    $condition
                );

                if (mysqli_stmt_execute($stmt)) {

                    header("Location: my_listings.php?success=uploaded");
                    exit();

                } else {

                    // Remove uploaded image if database insert fails
                    if (file_exists($targetPath)) {
                        unlink($targetPath);
                    }

                    $error = "Failed to save product.";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

include("../includes/header.php");

?>


<div class="upload-page">

    <div class="upload-card">

        <div class="upload-title">

            <h1>
                <i class="fa-solid fa-tag"></i>
                Sell Your Item
            </h1>

            <p>
                Add your product and reach buyers on SecondPasal.
            </p>

        </div>

        <?php if (!empty($error)) { ?>

            <div class="error-message">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>

        <form method="POST"
              enctype="multipart/form-data">

            <!-- Product Title -->

            <div class="form-group">

                <label>
                    Product Title
                </label>

                <div class="input-field">

                    <i class="fa-solid fa-heading"></i>

                    <input
                        type="text"
                        name="title"
                        placeholder="e.g. HP Laptop 15s"
                        required
                        value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">

                </div>

            </div>

            <!-- Category -->

            <div class="form-group">

                <label>
                    Category
                </label>

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
                                if (
                                    isset($_POST['category_id']) &&
                                    $_POST['category_id'] == $category['id']
                                ) {
                                    echo "selected";
                                }
                                ?>>

                                <?php
                                echo htmlspecialchars($category['category_name']);
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>

            </div>

            <!-- Price -->

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
                        placeholder="Enter price"
                        required
                        value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">

                </div>

            </div>

            <!-- Condition -->

            <div class="form-group">

                <label>
                    Condition
                </label>

                <div class="condition-options">

                    <label class="condition-option">
                        <input
                            type="radio"
                            name="condition_type"
                            value="New"
                            required>
                        <span>New</span>
                    </label>

                    <label class="condition-option">
                        <input
                            type="radio"
                            name="condition_type"
                            value="Like New">
                        <span>Like New</span>
                    </label>

                    <label class="condition-option">
                        <input
                            type="radio"
                            name="condition_type"
                            value="Good"
                            checked>
                        <span>Good</span>
                    </label>

                    <label class="condition-option">
                        <input
                            type="radio"
                            name="condition_type"
                            value="Fair">
                        <span>Fair</span>
                    </label>

                </div>

            </div>

            <!-- Location -->

            <div class="form-group">

                <label>
                    Location
                </label>

                <div class="input-field">

                    <i class="fa-solid fa-location-dot"></i>

                    <input
                        type="text"
                        name="location"
                        placeholder="e.g. Kathmandu"
                        required
                        value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">

                </div>

            </div>

            <!-- Description -->

            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    rows="6"
                    placeholder="Describe your product..."
                    required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>

            </div>

            <!-- Image -->

            <div class="form-group">

                <label>
                    Product Image
                </label>

                <div class="image-upload">

                    <input
                        type="file"
                        name="image"
                        id="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required>

                    <p>
                        JPG, PNG or WEBP — Maximum 5 MB
                    </p>

                    <img
                        id="imagePreview"
                        src=""
                        alt="Image Preview"
                        style="display:none;">

                </div>

            </div>

            <button
                type="submit"
                class="upload-btn">

                <i class="fa-solid fa-upload"></i>
                Publish Product

            </button>

        </form>

    </div>

</div>

<script src="../assets/js/upload_product.js"></script>

</body>

</html>