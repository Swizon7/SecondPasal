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
elseif (
    !isset($_FILES['images']) ||
    !is_array($_FILES['images']['name']) ||
    count($_FILES['images']['name']) === 0
) {
    $error = "Please select at least one product photo.";
} else {

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $maxImages = 5;
    $totalImages = count($_FILES['images']['name']);

    if ($totalImages > $maxImages) {
        $error = "You can upload a maximum of 5 photos.";
    } else {

        $uploadDir = "../uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedFiles = [];

        for ($i = 0; $i < $totalImages; $i++) {

            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                $error = "One of the selected images could not be uploaded.";
                break;
            }

            $tmpName = $_FILES['images']['tmp_name'][$i];
            $fileSize = $_FILES['images']['size'][$i];

            $fileType = mime_content_type($tmpName);

            if (!isset($allowedTypes[$fileType])) {
                $error = "Only JPG, PNG and WEBP images are allowed.";
                break;
            }

            if ($fileSize > 5 * 1024 * 1024) {
                $error = "Each image must be less than 5 MB.";
                break;
            }

            $extension = $allowedTypes[$fileType];

            $fileName =
                time() . "_" .
                bin2hex(random_bytes(6)) . "." .
                $extension;

            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($tmpName, $targetPath)) {
                $error = "Failed to upload one of the images.";
                break;
            }

            $uploadedFiles[] = $fileName;
        }

        // Delete already uploaded files if any validation/upload failed
        if (!empty($error)) {

            foreach ($uploadedFiles as $uploadedFile) {
                $filePath = $uploadDir . $uploadedFile;

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

        } else {

            // First image remains compatible with the existing products table
            $mainImage = $uploadedFiles[0];

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO products
                (
                    user_id,
                    category_id,
                    title,
                    description,
                    price,
                    location,
                    image,
                    condition_type,
                    status,
                    views
                )
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
                $mainImage,
                $condition
            );

            if (mysqli_stmt_execute($stmt)) {

                $productId = mysqli_insert_id($conn);

                // Save all images
                $imageStmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO product_images
                    (product_id, image)
                    VALUES (?, ?)"
                );

                foreach ($uploadedFiles as $uploadedFile) {

                    mysqli_stmt_bind_param(
                        $imageStmt,
                        "is",
                        $productId,
                        $uploadedFile
                    );

                    mysqli_stmt_execute($imageStmt);
                }

                mysqli_stmt_close($imageStmt);
                mysqli_stmt_close($stmt);

                header("Location: my_listings.php?success=uploaded");
                exit();

            } else {

                // Remove uploaded files if product insert fails
                foreach ($uploadedFiles as $uploadedFile) {

                    $filePath = $uploadDir . $uploadedFile;

                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                mysqli_stmt_close($stmt);

                $error = "Failed to save product.";
            }
        }
    }
}
}

include("../includes/header.php");

?>
<link rel="stylesheet" href="../assets/css/upload_product.css">

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
<form
    action=""
    method="POST"
    enctype="multipart/form-data">

    <div class="form-section">

    <div class="section-heading">
        <div class="section-icon">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>

        <div>
            <h2>Listing Details</h2>
            <p>Tell buyers what you are selling.</p>
        </div>
    </div>
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
<div class="form-row">
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
</div>

<div class="form-section">

    <div class="section-heading">
        <div class="section-icon">
            <i class="fa-solid fa-location-dot"></i>
        </div>

        <div>
            <h2>Location & Description</h2>
            <p>Give buyers useful information about your item.</p>
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
</div>
            <!-- Image -->

<div class="form-section">

    <div class="section-heading">
        <div class="section-icon">
            <i class="fa-solid fa-camera"></i>
        </div>

        <div>
            <h2>Product Photos</h2>
            <p>Good photos help buyers understand your product.</p>
        </div>
    </div>
        
          <!-- Product Photos -->
<div class="form-group photo-upload-group">

    <label class="photo-label">
        Product Photos
        <span>Up to 5 photos</span>
    </label>

    <div class="photo-upload-box" id="photoUploadBox">

        <input
            type="file"
            name="images[]"
            id="productImages"
            accept="image/jpeg,image/png,image/webp"
            multiple
            hidden
            required
        >

        <label for="productImages" class="photo-upload-content">

            <div class="upload-icon">
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>

            <h3>Upload Product Photos</h3>

            <p>
                Click here to choose photos from your device or drag them here
            </p>

            <span>
                JPG, PNG or WEBP • Maximum 5 MB per photo
            </span>

        </label>

    </div>

    <div class="photo-counter" id="photoCounter">
        0 / 5 photos selected
    </div>

    <div class="photo-preview-grid" id="photoPreviewGrid"></div>

</div>

</div>

            <button
                type="submit"
                class="upload-btn">

                <i class="fa-solid fa-upload"></i>
                Publish Product

            </button>
</div>
        </form>

    </div>

</div>

<script src="../assets/js/upload_product.js"></script>

</body>

</html>