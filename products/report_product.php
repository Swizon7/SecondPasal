<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$productId = (int) $_GET['id'];
$userId = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, user_id
     FROM products
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    die("Product not found.");
}

$product = mysqli_fetch_assoc($result);

// Prevent seller from reporting their own product.
if ((int)$product['user_id'] === $userId) {
    die("You cannot report your own product.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $reason = trim($_POST['reason'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $allowedReasons = [
        'Scam/Fraud',
        'Fake Product',
        'Inappropriate Content',
        'Wrong Information',
        'Other'
    ];

    if (!in_array($reason, $allowedReasons, true)) {

        $error = "Please select a valid reason.";

    } elseif (empty($description)) {

        $error = "Please provide some details.";

    } else {

        // Prevent duplicate pending reports from the same user.
        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM reports
             WHERE reporter_id = ?
             AND product_id = ?
             AND status = 'pending'
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "ii",
            $userId,
            $productId
        );

        mysqli_stmt_execute($check);

        $checkResult = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($checkResult) > 0) {

            $error = "You already reported this product.";

        } else {

            $insert = mysqli_prepare(
                $conn,
                "INSERT INTO reports
                (reporter_id, product_id, reason, description)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $insert,
                "iiss",
                $userId,
                $productId,
                $reason,
                $description
            );

            if (mysqli_stmt_execute($insert)) {

                header("Location: product_details.php?id=$productId&report=success");
                exit();

            } else {

                $error = "Failed to submit report.";
            }
        }
    }
}

include("../includes/header.php");

?>

<link rel="stylesheet" href="../assets/css/report_product.css">

<div class="report-page">

    <div class="report-card">

        <div class="report-title">

            <div class="report-icon">
                <i class="fa-solid fa-flag"></i>
            </div>

            <h1>Report Product</h1>

            <p>
                Help us keep SecondPasal safe and trustworthy.
            </p>

        </div>

        <div class="reported-product">

            <strong>Product:</strong>

            <?php echo htmlspecialchars($product['title']); ?>

        </div>

        <?php if ($error !== '') { ?>

            <div class="report-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>

        <form method="POST">

            <div class="form-group">

                <label>Reason</label>

                <select name="reason" required>

                    <option value="">
                        Select a reason
                    </option>

                    <?php
                    foreach (
                        [
                            'Scam/Fraud',
                            'Fake Product',
                            'Inappropriate Content',
                            'Wrong Information',
                            'Other'
                        ] as $reason
                    ) {
                    ?>

                        <option value="<?php echo htmlspecialchars($reason); ?>">
                            <?php echo htmlspecialchars($reason); ?>
                        </option>

                    <?php } ?>

                </select>

            </div>

            <div class="form-group">

                <label>Details</label>

                <textarea
                    name="description"
                    rows="6"
                    placeholder="Explain why you are reporting this product..."
                    required></textarea>

            </div>

            
            <button type="submit" class="report-submit">

                <i class="fa-solid fa-flag"></i>
                Submit Report

            </button>

            <a
                href="product_details.php?id=<?php echo $productId; ?>"
                class="cancel-report">

                Cancel

            </a>

        </form>

    </div>

</div>