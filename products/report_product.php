<?php

session_start();

include("../db.php");


/* =========================================================
   CHECK LOGIN
========================================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login/login.php");
    exit();

}


$userId = (int) $_SESSION['user_id'];


/* =========================================================
   CHECK PRODUCT ID
========================================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: products.php");
    exit();

}


$productId = (int) $_GET['id'];


/* =========================================================
   GET PRODUCT
========================================================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        title,
        user_id
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

    die("Product not found.");

}


$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================================
   PREVENT SELLER REPORTING OWN PRODUCT
========================================================= */

if ((int) $product['user_id'] === $userId) {

    die("You cannot report your own product.");

}


/* =========================================================
   VARIABLES
========================================================= */

$error = "";

$oldReason = "";
$oldDescription = "";


/* =========================================================
   FORM SUBMISSION
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $reason = trim($_POST['reason'] ?? '');

    $description = trim(
        $_POST['description'] ?? ''
    );


    $oldReason = $reason;

    $oldDescription = $description;


    /* =====================================================
       ALLOWED REASONS
    ====================================================== */

    $allowedReasons = [

        'Scam/Fraud',
        'Fake Product',
        'Inappropriate Content',
        'Wrong Information',
        'Other'

    ];


    /* =====================================================
       VALIDATE REASON
    ====================================================== */

    if (!in_array($reason, $allowedReasons, true)) {

        $error = "Please select a valid reason.";

    }


    /* =====================================================
       VALIDATE DESCRIPTION
    ====================================================== */

    elseif (empty($description)) {

        $error = "Please provide some details.";

    }


    elseif (strlen($description) < 10) {

        $error =
            "Please provide at least 10 characters of details.";

    }


    /* =====================================================
       CHECK DUPLICATE REPORT
    ====================================================== */

    else {

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


        $checkResult =
            mysqli_stmt_get_result($check);


        if (mysqli_num_rows($checkResult) > 0) {

            $error =
                "You already reported this product.";

        }


        mysqli_stmt_close($check);

    }


    /* =====================================================
       PROCESS EVIDENCE
    ====================================================== */

    $evidenceName = "";


    if ($error === "" && isset($_FILES['evidence'])) {


        $file = $_FILES['evidence'];


        /* -----------------------------------------------
           CHECK UPLOAD ERROR
        ------------------------------------------------ */

        if ($file['error'] !== UPLOAD_ERR_NO_FILE) {


            if ($file['error'] !== UPLOAD_ERR_OK) {

                $error =
                    "There was a problem uploading the evidence.";

            }


            /* -------------------------------------------
               MAXIMUM FILE SIZE: 5 MB
            -------------------------------------------- */

            elseif ($file['size'] > 5 * 1024 * 1024) {

                $error =
                    "Evidence file must be smaller than 5 MB.";

            }


            /* -------------------------------------------
               CHECK MIME TYPE
            -------------------------------------------- */

            else {

                $allowedTypes = [

                    'image/jpeg',
                    'image/png',
                    'image/webp'

                ];


                $fileInfo = finfo_open(
                    FILEINFO_MIME_TYPE
                );


                $mimeType = finfo_file(
                    $fileInfo,
                    $file['tmp_name']
                );


                finfo_close($fileInfo);


                if (!in_array(
                    $mimeType,
                    $allowedTypes,
                    true
                )) {

                    $error =
                        "Only JPG, PNG, and WEBP images are allowed.";

                }

            }

        }

    }


    /* =====================================================
       SAVE REPORT
    ====================================================== */

    if ($error === "") {


        /* -----------------------------------------------
           CREATE REPORT UPLOAD DIRECTORY
        ------------------------------------------------ */

        $uploadDirectory =
            "../uploads/reports/";


        if (!is_dir($uploadDirectory)) {

            if (!mkdir(
                $uploadDirectory,
                0755,
                true
            )) {

                $error =
                    "Unable to create evidence upload folder.";

            }

        }


        /* -----------------------------------------------
           GENERATE UNIQUE FILE NAME
        ------------------------------------------------ */

        if (
            $error === "" &&
            isset($_FILES['evidence']) &&
            $_FILES['evidence']['error'] === UPLOAD_ERR_OK
        ) {


            $file = $_FILES['evidence'];


            $extension = strtolower(
                pathinfo(
                    $file['name'],
                    PATHINFO_EXTENSION
                )
            );


            $evidenceName =
                "report_"
                . $userId
                . "_"
                . $productId
                . "_"
                . bin2hex(random_bytes(8))
                . "."
                . $extension;


            $evidencePath =
                $uploadDirectory
                . $evidenceName;


            if (!move_uploaded_file(
                $file['tmp_name'],
                $evidencePath
            )) {

                $error =
                    "Failed to save the evidence file.";

                $evidenceName = "";

            }

        }

    }


    /* =====================================================
       INSERT REPORT
    ====================================================== */

    if ($error === "") {


        $insert = mysqli_prepare(
            $conn,
            "INSERT INTO reports
            (
                reporter_id,
                product_id,
                reason,
                description,
                evidence
            )
            VALUES (?, ?, ?, ?, ?)"
        );


        mysqli_stmt_bind_param(
            $insert,
            "iisss",
            $userId,
            $productId,
            $reason,
            $description,
            $evidenceName
        );


        if (mysqli_stmt_execute($insert)) {


            mysqli_stmt_close($insert);


            header(
                "Location: product_details.php?id="
                . $productId
                . "&report=success"
            );

            exit();


        } else {


            /* -------------------------------------------
               DELETE UPLOADED FILE IF DB INSERT FAILS
            -------------------------------------------- */

            if (
                $evidenceName !== "" &&
                file_exists(
                    $uploadDirectory . $evidenceName
                )
            ) {

                unlink(
                    $uploadDirectory . $evidenceName
                );

            }


            $error =
                "Failed to submit report.";


            mysqli_stmt_close($insert);

        }

    }

}


/* =========================================================
   HEADER
========================================================= */

include("../includes/header.php");

?>


<link
    rel="stylesheet"
    href="../assets/css/report_product.css?v=2">


<div class="report-page">


    <div class="report-card">


        <!-- =================================================
             REPORT HEADER
        ================================================== -->

        <div class="report-title">


            <div class="report-icon">

                <i class="fa-solid fa-flag"></i>

            </div>


            <div>

                <h1>
                    Report Product
                </h1>

                <p>
                    Help us keep SecondPasal safe and trustworthy.
                </p>

            </div>


        </div>



        <!-- =================================================
             PRODUCT BEING REPORTED
        ================================================== -->

        <div class="reported-product">


            <div class="reported-product-icon">

                <i class="fa-solid fa-box"></i>

            </div>


            <div>

                <span>
                    Product being reported
                </span>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $product['title']
                    );
                    ?>

                </strong>

            </div>


        </div>



        <!-- =================================================
             ERROR
        ================================================== -->

        <?php if ($error !== '') { ?>

            <div class="report-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <span>

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </span>

            </div>

        <?php } ?>



        <!-- =================================================
             REPORT FORM
        ================================================== -->

        <form
            method="POST"
            enctype="multipart/form-data"
            id="reportForm">


            <!-- =================================================
                 REASON
            ================================================== -->

            <div class="form-group">


                <label for="reason">

                    <i class="fa-solid fa-list"></i>

                    Reason

                </label>


                <select
                    name="reason"
                    id="reason"
                    required>


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
                        ] as $reasonOption
                    ) {

                    ?>

                        <option
                            value="<?php echo htmlspecialchars($reasonOption); ?>"
                            <?php
                            echo (
                                $oldReason === $reasonOption
                            )
                                ? 'selected'
                                : '';
                            ?>>

                            <?php
                            echo htmlspecialchars(
                                $reasonOption
                            );
                            ?>

                        </option>

                    <?php } ?>


                </select>


            </div>



            <!-- =================================================
                 DESCRIPTION
            ================================================== -->

            <div class="form-group">


                <label for="description">

                    <i class="fa-solid fa-align-left"></i>

                    Details

                </label>


                <textarea
                    name="description"
                    id="description"
                    rows="6"
                    maxlength="1000"
                    placeholder="Explain why you are reporting this product..."
                    required><?php echo htmlspecialchars($oldDescription); ?></textarea>


                <div class="character-count">

                    <span id="characterCount">
                        0
                    </span>

                    / 1000 characters

                </div>


            </div>



            <!-- =================================================
                 EVIDENCE UPLOAD
            ================================================== -->

            <div class="form-group">


                <label for="evidence">

                    <i class="fa-solid fa-image"></i>

                    Evidence

                    <span class="optional">
                        Optional
                    </span>

                </label>


                <div class="evidence-upload">


                    <input
                        type="file"
                        name="evidence"
                        id="evidence"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">


                    <label
                        for="evidence"
                        class="evidence-box">


                        <div class="evidence-icon">

                            <i class="fa-solid fa-cloud-arrow-up"></i>

                        </div>


                        <div class="evidence-text">

                            <strong>
                                Upload Evidence
                            </strong>

                            <span>
                                Click to select an image
                            </span>

                            <small>
                                JPG, PNG or WEBP • Maximum 5 MB
                            </small>

                        </div>


                    </label>


                    <!-- PREVIEW -->

                    <div
                        class="evidence-preview"
                        id="evidencePreview">


                        <img
                            id="evidenceImage"
                            src=""
                            alt="Evidence preview">


                        <div class="evidence-preview-info">

                            <strong id="evidenceFileName">
                                Evidence
                            </strong>

                            <span id="evidenceFileSize">
                                0 KB
                            </span>

                        </div>


                        <button
                            type="button"
                            id="removeEvidence"
                            class="remove-evidence"
                            title="Remove evidence">

                            <i class="fa-solid fa-xmark"></i>

                        </button>


                    </div>


                </div>


            </div>



            <!-- =================================================
                 WARNING
            ================================================== -->

            <div class="report-warning">

                <i class="fa-solid fa-shield-halved"></i>


                <div>

                    <strong>
                        Please report responsibly
                    </strong>

                    <p>
                        False or misleading reports may affect
                        your account. Only report products that
                        genuinely violate marketplace rules.
                    </p>

                </div>


            </div>



            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <div class="report-actions">


                <a
                    href="product_details.php?id=<?php echo $productId; ?>"
                    class="cancel-report">

                    <i class="fa-solid fa-arrow-left"></i>

                    Cancel

                </a>


                <button
                    type="submit"
                    class="report-submit">

                    <i class="fa-solid fa-flag"></i>

                    Submit Report

                </button>


            </div>


        </form>


    </div>

</div>



<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* =================================================
           DESCRIPTION CHARACTER COUNT
        ================================================== */

        const description =
            document.getElementById("description");

        const characterCount =
            document.getElementById("characterCount");


        function updateCharacterCount() {

            characterCount.textContent =
                description.value.length;

        }


        description.addEventListener(
            "input",
            updateCharacterCount
        );


        updateCharacterCount();



        /* =================================================
           EVIDENCE UPLOAD
        ================================================== */

        const evidence =
            document.getElementById("evidence");

        const preview =
            document.getElementById("evidencePreview");

        const previewImage =
            document.getElementById("evidenceImage");

        const fileName =
            document.getElementById("evidenceFileName");

        const fileSize =
            document.getElementById("evidenceFileSize");

        const removeButton =
            document.getElementById("removeEvidence");


        evidence.addEventListener(
            "change",
            function () {


                if (!this.files || !this.files[0]) {

                    return;

                }


                const file =
                    this.files[0];


                /* -----------------------------
                   FILE SIZE
                ----------------------------- */

                if (
                    file.size >
                    5 * 1024 * 1024
                ) {

                    alert(
                        "Evidence file must be smaller than 5 MB."
                    );

                    this.value = "";

                    preview.style.display =
                        "none";

                    return;

                }


                /* -----------------------------
                   FILE TYPE
                ----------------------------- */

                const allowedTypes = [

                    "image/jpeg",
                    "image/png",
                    "image/webp"

                ];


                if (
                    !allowedTypes.includes(
                        file.type
                    )
                ) {

                    alert(
                        "Only JPG, PNG, and WEBP images are allowed."
                    );

                    this.value = "";

                    preview.style.display =
                        "none";

                    return;

                }


                /* -----------------------------
                   SHOW PREVIEW
                ----------------------------- */

                const reader =
                    new FileReader();


                reader.onload =
                    function (event) {

                        previewImage.src =
                            event.target.result;

                        preview.style.display =
                            "flex";

                    };


                reader.readAsDataURL(file);


                /* -----------------------------
                   FILE NAME
                ----------------------------- */

                fileName.textContent =
                    file.name;


                /* -----------------------------
                   FILE SIZE
                ----------------------------- */

                let size =
                    file.size / 1024;


                if (size >= 1024) {

                    fileSize.textContent =
                        (
                            size / 1024
                        ).toFixed(2)
                        + " MB";

                } else {

                    fileSize.textContent =
                        size.toFixed(1)
                        + " KB";

                }

            }
        );



        /* =================================================
           REMOVE EVIDENCE
        ================================================== */

        removeButton.addEventListener(
            "click",
            function () {

                evidence.value = "";

                previewImage.src = "";

                preview.style.display =
                    "none";

            }
        );


    }
);

</script>


<?php

include("../includes/footer.php");

?>