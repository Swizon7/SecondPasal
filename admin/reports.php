<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}

$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$allowedStatuses = ['pending', 'resolved'];

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

$where = '';
$params = [];
$types = '';

if ($status !== '') {
    $where = "WHERE reports.status = ?";
    $params[] = $status;
    $types = 's';
}

$sql = "
    SELECT
        reports.id,
        reports.reason,
        reports.description,
        reports.status,
        reports.created_at,
        users.name AS reporter_name,
        products.id AS product_id,
        products.title AS product_title,
        seller.name AS seller_name
    FROM reports
    INNER JOIN users
        ON users.id = reports.reporter_id
    INNER JOIN products
        ON products.id = reports.product_id
    INNER JOIN users AS seller
        ON seller.id = products.user_id
    $where
    ORDER BY reports.id DESC
";

$stmt = mysqli_prepare($conn, $sql);

if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Reports | SecondPasal</title>
<link rel="stylesheet" href="../assets/css/admin_sidebar.css">
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<link rel="stylesheet" href="../assets/css/admin_reports.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="admin-layout">

<aside class="sidebar">

    <div class="admin-brand">
        <i class="fa-solid fa-user-shield"></i>
        <span>SecondPasal</span>
    </div>

    <p class="admin-label">ADMIN PANEL</p>

    <nav>

        <a href="dashboard.php">
            <i class="fa-solid fa-chart-line"></i>
            Dashboard
        </a>

        <a href="users.php">
            <i class="fa-solid fa-users"></i>
            Users
        </a>

        <a href="products.php">
            <i class="fa-solid fa-box"></i>
            Products
        </a>

        <a href="categories.php">
            <i class="fa-solid fa-list"></i>
            Categories
        </a>

        <a href="reports.php" class="active">
            <i class="fa-solid fa-flag"></i>
            Reports
        </a>

        <a href="../homepage/homepage.php">
            <i class="fa-solid fa-globe"></i>
            Visit Website
        </a>

        <a href="logout.php" class="logout-link">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>

    </nav>

</aside>

<main class="admin-main">

    <div class="topbar">

        <div>

            <h1>Reports</h1>

            <p>Review product reports submitted by users.</p>

        </div>

        <div class="admin-account">

            <i class="fa-solid fa-circle-user"></i>

            <?php echo htmlspecialchars($_SESSION['name']); ?>

        </div>

    </div>

    <?php if (isset($_GET['success'])) { ?>

        <div class="admin-success">

            <i class="fa-solid fa-circle-check"></i>

            Report updated successfully.

        </div>

    <?php } ?>

    <div class="reports-toolbar">

        <a
            href="reports.php"
            class="<?php echo $status === '' ? 'active' : ''; ?>">

            All

        </a>

        <a
            href="reports.php?status=pending"
            class="<?php echo $status === 'pending' ? 'active' : ''; ?>">

            Pending

        </a>

        <a
            href="reports.php?status=resolved"
            class="<?php echo $status === 'resolved' ? 'active' : ''; ?>">

            Resolved

        </a>

    </div>

    <div class="reports-card">

        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Reporter</th>
                        <th>Reason</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (mysqli_num_rows($result) > 0) { ?>

                    <?php while ($report = mysqli_fetch_assoc($result)) { ?>

                        <tr>

                            <td>
                                #<?php echo (int)$report['id']; ?>
                            </td>

                            <td>

                                <a
                                    href="../products/product_details.php?id=<?php echo $report['product_id']; ?>"
                                    target="_blank">

                                    <?php echo htmlspecialchars($report['product_title']); ?>

                                </a>

                            </td>

                            <td>
                                <?php echo htmlspecialchars($report['reporter_name']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($report['reason']); ?>
                            </td>

                            <td class="description-cell">
                                <?php echo htmlspecialchars($report['description']); ?>
                            </td>

                            <td>

                                <span class="report-status <?php echo strtolower($report['status']); ?>">

                                    <?php echo htmlspecialchars(ucfirst($report['status'])); ?>

                                </span>

                            </td>

                            <td>
                                <?php
                                echo date(
                                    "Y-m-d",
                                    strtotime($report['created_at'])
                                );
                                ?>
                            </td>

                            <td>

                                <?php if ($report['status'] === 'pending') { ?>

                                    <a
                                        href="report_action.php?action=resolve&id=<?php echo $report['id']; ?>"
                                        class="resolve-btn">

                                        Resolve

                                    </a>

                                <?php } else { ?>

                                    <span class="resolved-text">
                                        Resolved
                                    </span>

                                <?php } ?>

                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>

                        <td colspan="8" class="no-data">
                            No reports found.
                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

</div>

</body>

</html>