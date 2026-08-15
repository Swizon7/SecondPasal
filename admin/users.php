<?php

session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {

    $searchValue = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, phone, role, status, created_at
         FROM users
         WHERE role != 'admin'
         AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)
         ORDER BY id DESC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $searchValue,
        $searchValue,
        $searchValue
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $result = mysqli_query(
        $conn,
        "SELECT id, name, email, phone, role, status, created_at
         FROM users
         WHERE role != 'admin'
         ORDER BY id DESC"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Manage Users | SecondPasal</title>

    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin_users.css">

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

            <a href="users.php" class="active">
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

            <a href="reports.php">
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

                <h1>Manage Users</h1>

                <p>
                    View and manage registered users.
                </p>

            </div>

            <div class="admin-account">

                <i class="fa-solid fa-circle-user"></i>

                <?php echo htmlspecialchars($_SESSION['name']); ?>

            </div>

        </div>

        <?php if (isset($_GET['success'])) { ?>

            <div class="admin-success">
                <i class="fa-solid fa-circle-check"></i>

                <?php

                if ($_GET['success'] === 'status') {
                    echo "User status updated successfully.";
                } elseif ($_GET['success'] === 'deleted') {
                    echo "User deleted successfully.";
                }

                ?>
            </div>

        <?php } ?>

        <?php if (isset($_GET['error'])) { ?>

            <div class="admin-error">
                <i class="fa-solid fa-circle-exclamation"></i>

                <?php

                if ($_GET['error'] === 'self') {
                    echo "You cannot delete your own admin account.";
                } elseif ($_GET['error'] === 'failed') {
                    echo "Unable to complete the requested action.";
                }

                ?>
            </div>

        <?php } ?>

        <div class="users-toolbar">

            <form method="GET">

                <div class="user-search">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        name="search"
                        placeholder="Search by name, email or phone..."
                        value="<?php echo htmlspecialchars($search); ?>">

                </div>

                <button type="submit" class="search-btn">
                    Search
                </button>

                <a href="users.php" class="clear-btn">
                    Clear
                </a>

            </form>

        </div>

        <div class="users-card">

            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($result) > 0) { ?>

                        <?php while ($user = mysqli_fetch_assoc($result)) { ?>

                            <tr>

                                <td>
                                    #<?php echo (int)$user['id']; ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user['phone']); ?>
                                </td>

                                <td>

                                    <span class="role-badge">
                                        <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                    </span>

                                </td>

                                <td>

                                    <span class="user-status <?php echo strtolower($user['status']); ?>">

                                        <?php echo htmlspecialchars(ucfirst($user['status'])); ?>

                                    </span>

                                </td>

                                <td>
                                    <?php
                                    echo date(
                                        "Y-m-d",
                                        strtotime($user['created_at'])
                                    );
                                    ?>
                                </td>

                                <td>

                                    <div class="user-actions">

                                        <a
                                            href="user_action.php?action=toggle_status&id=<?php echo $user['id']; ?>"
                                            class="status-btn">

                                            <?php if (strtolower($user['status']) === 'active') { ?>

                                                Disable

                                            <?php } else { ?>

                                                Activate

                                            <?php } ?>

                                        </a>

                                        <a
                                            href="user_action.php?action=delete&id=<?php echo $user['id']; ?>"
                                            class="delete-btn"
                                            onclick="return confirm('Are you sure you want to delete this user?');">

                                            Delete

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="8" class="no-data">
                                No users found.
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