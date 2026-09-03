<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, phone, role, email_verified, created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    session_destroy();
    header("Location: ../login/login.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

?>

<?php include("../includes/header.php"); ?>

<link rel="stylesheet" href="../assets/css/profile.css">

<div class="profile-page">

    <div class="profile-card">

    
  <?php if (isset($_GET['updated']) && $_GET['updated'] === 'success') { ?>

        <div class="profile-success">
            <i class="fa-solid fa-circle-check"></i>
            Profile updated successfully.
        </div>

    <?php } ?>


        <div class="profile-header">

        
            <div class="profile-avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <div>
                <h1>
                    <?php echo htmlspecialchars($user['name']); ?>
                </h1>

                <p>
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>
            </div>

        </div>

        <div class="profile-info">

            <div class="info-item">

                <span class="info-label">
                    <i class="fa-solid fa-user"></i>
                    Full Name
                </span>

                <span class="info-value">
                    <?php echo htmlspecialchars($user['name']); ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    <i class="fa-solid fa-envelope"></i>
                    Email
                </span>

                <span class="info-value">
                    <?php echo htmlspecialchars($user['email']); ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    <i class="fa-solid fa-phone"></i>
                    Phone
                </span>

                <span class="info-value">
                    <?php echo htmlspecialchars($user['phone']); ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    <i class="fa-solid fa-shield-halved"></i>
                    Account Type
                </span>

                <span class="info-value">
                    <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    <i class="fa-solid fa-circle-check"></i>
                    Email Status
                </span>

                <span class="status-badge <?php echo ((int)$user['email_verified'] === 1) ? 'verified' : 'unverified'; ?>">

                    <?php
                    echo ((int)$user['email_verified'] === 1)
                        ? 'Verified'
                        : 'Not Verified';
                    ?>

                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    <i class="fa-solid fa-calendar"></i>
                    Joined
                </span>

                <span class="info-value">
                    <?php echo date("F d, Y", strtotime($user['created_at'])); ?>
                </span>

            </div>

        </div>


        <div class="profile-actions">

           <a href="edit_profile.php" class="edit-profile-btn">
    <i class="fa-solid fa-pen"></i>
    Edit Profile
</a>

            <a href="../products/my_listings.php" class="listings-btn">
                <i class="fa-solid fa-box"></i>
                My Listings
            </a>

            <a href="../login/logout.php" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>

        </div>

    </div>

</div>

<?php include("../includes/footer.php"); ?>