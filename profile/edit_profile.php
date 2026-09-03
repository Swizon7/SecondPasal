<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

$error = "";
$success = "";

// Get current user data
$stmt = mysqli_prepare(
    $conn,
    "SELECT name, email, phone
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    die("User not found.");
}

$user = mysqli_fetch_assoc($result);


// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || $phone === '') {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // Check whether email belongs to another user
        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM users
             WHERE email = ?
             AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "si",
            $email,
            $userId
        );

        mysqli_stmt_execute($check);

        $checkResult = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($checkResult) > 0) {

            $error = "That email address is already being used.";

        } else {

            $update = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET name = ?,
                     email = ?,
                     phone = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "sssi",
                $name,
                $email,
                $phone,
                $userId
            );

            if (mysqli_stmt_execute($update)) {

                // Update session name/email
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;

                header("Location: profile.php?updated=success");
                exit();

            } else {

                $error = "Failed to update profile.";
            }

            mysqli_stmt_close($update);
        }

        mysqli_stmt_close($check);
    }

    // Keep submitted values in form after validation error
    $user['name'] = $name;
    $user['email'] = $email;
    $user['phone'] = $phone;
}

?>

<?php include("../includes/header.php"); ?>

<link rel="stylesheet" href="../assets/css/edit_profile.css">

<div class="edit-profile-page">

    <div class="edit-profile-card">

        <div class="edit-profile-title">

            <div class="edit-profile-icon">
                <i class="fa-solid fa-user-pen"></i>
            </div>

            <h1>Edit Profile</h1>

            <p>Update your SecondPasal account information.</p>

        </div>


        <?php if ($error !== "") { ?>

            <div class="profile-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <form method="POST">

            <div class="form-group">

                <label>Full Name</label>

                <div class="input-field">

                    <i class="fa-solid fa-user"></i>

                    <input
                        type="text"
                        name="name"
                        required
                        value="<?php echo htmlspecialchars($user['name']); ?>">

                </div>

            </div>


            <div class="form-group">

                <label>Email</label>

                <div class="input-field">

                    <i class="fa-solid fa-envelope"></i>

                    <input
                        type="email"
                        name="email"
                        required
                        value="<?php echo htmlspecialchars($user['email']); ?>">

                </div>

            </div>


            <div class="form-group">

                <label>Phone Number</label>

                <div class="input-field">

                    <i class="fa-solid fa-phone"></i>

                    <input
                        type="text"
                        name="phone"
                        required
                        value="<?php echo htmlspecialchars($user['phone']); ?>">

                </div>

            </div>


            <div class="profile-buttons">

                <button type="submit" class="save-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Changes
                </button>

                <a href="profile.php" class="cancel-btn">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

<?php include("../includes/footer.php"); ?>