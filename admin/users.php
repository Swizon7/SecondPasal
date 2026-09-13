<?php

session_start();
include("../db.php");

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: admin_login.php?error=unauthorized");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

/* ==========================================
   SEARCH USERS
========================================== */

if ($search !== '') {

    $searchValue = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            id,
            name,
            email,
            phone,
            role,
            status,
            created_at
         FROM users
         WHERE role != 'admin'
         AND (
            name LIKE ?
            OR email LIKE ?
            OR phone LIKE ?
         )
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
        "SELECT
            id,
            name,
            email,
            phone,
            role,
            status,
            created_at
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Users | SecondPasal</title>
<link rel="stylesheet" href="../assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">

    <link rel="stylesheet" href="../assets/css/admin_users.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="admin-layout">

    <!-- ==========================================
         SIDEBAR
    =========================================== -->

    <aside class="sidebar">

        <div class="admin-brand">

            <div class="brand-icon">
                <i class="fa-solid fa-user-shield"></i>
            </div>

            <span>SecondPasal</span>

        </div>

        <p class="admin-label">
            ADMIN PANEL
        </p>

        <nav>

            <a href="dashboard.php">

                <i class="fa-solid fa-chart-line"></i>

                <span>Dashboard</span>

            </a>

            <a href="users.php" class="active">

                <i class="fa-solid fa-users"></i>

                <span>Users</span>

            </a>

            <a href="products.php">

                <i class="fa-solid fa-box"></i>

                <span>Products</span>

            </a>

            <a href="categories.php">

                <i class="fa-solid fa-list"></i>

                <span>Categories</span>

            </a>

            <a href="reports.php">

                <i class="fa-solid fa-flag"></i>

                <span>Reports</span>

            </a>

            <a href="../homepage/homepage.php">

                <i class="fa-solid fa-globe"></i>

                <span>Visit Website</span>

            </a>

            <a href="logout.php" class="logout-link">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </nav>

    </aside>


    <!-- ==========================================
         MAIN CONTENT
    =========================================== -->

    <main class="admin-main">

        <!-- TOPBAR -->

        <div class="topbar">

            <div class="page-heading">

                <span class="heading-label">
                    USER MANAGEMENT
                </span>

                <h1>
                    Manage Users
                </h1>

                <p>
                    View and manage registered users.
                </p>

            </div>

            <div class="admin-account">

                <div class="account-icon">
                    <i class="fa-solid fa-circle-user"></i>
                </div>

                <div class="account-info">

                    <span>Administrator</span>

                    <strong>
                        <?php echo htmlspecialchars($_SESSION['name']); ?>
                    </strong>

                </div>

            </div>

        </div>


        <!-- ==========================================
             SUCCESS MESSAGE
        =========================================== -->

        <?php if (isset($_GET['success'])) { ?>

            <div class="admin-message success-message">

                <i class="fa-solid fa-circle-check"></i>

                <div>

                    <?php

                    if ($_GET['success'] === 'status') {

                        echo "User status updated successfully.";

                    } elseif ($_GET['success'] === 'deleted') {

                        echo "User deleted successfully.";

                    } else {

                        echo "Action completed successfully.";

                    }

                    ?>

                </div>

                <button
                    type="button"
                    class="message-close"
                    onclick="this.parentElement.remove();">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

        <?php } ?>


        <!-- ==========================================
             ERROR MESSAGE
        =========================================== -->

        <?php if (isset($_GET['error'])) { ?>

            <div class="admin-message error-message">

                <i class="fa-solid fa-circle-exclamation"></i>

                <div>

                    <?php

                    if ($_GET['error'] === 'self') {

                        echo "You cannot delete your own admin account.";

                    } elseif ($_GET['error'] === 'failed') {

                        echo "Unable to complete the requested action.";

                    } elseif ($_GET['error'] === 'unauthorized') {

                        echo "You are not authorized to access this page.";

                    } else {

                        echo "Something went wrong.";

                    }

                    ?>

                </div>

                <button
                    type="button"
                    class="message-close"
                    onclick="this.parentElement.remove();">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

        <?php } ?>


        <!-- ==========================================
             SEARCH TOOLBAR
        =========================================== -->

        <div class="users-toolbar">

            <form
                method="GET"
                action="users.php"
                id="userSearchForm">

                <div class="user-search">

                    <i class="fa-solid fa-magnifying-glass search-icon"></i>

                    <input
                        type="text"
                        id="userSearch"
                        name="search"
                        autocomplete="off"
                        placeholder="Search by name, email or phone..."
                        value="<?php echo htmlspecialchars($search); ?>">

                    <button
                        type="button"
                        class="search-clear"
                        id="clearSearch"
                        title="Clear Search">

                        <i class="fa-solid fa-xmark"></i>

                    </button>

                    <div class="search-loading" id="searchLoading">

                        <i class="fa-solid fa-spinner"></i>

                    </div>

                </div>


                <button
                    type="submit"
                    class="search-btn">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Search

                </button>


                <a
                    href="users.php"
                    class="clear-btn">

                    <i class="fa-solid fa-rotate-left"></i>

                    Clear

                </a>

            </form>


            <div class="search-info">

                <i class="fa-solid fa-circle-info"></i>

                Search automatically after you stop typing.

            </div>

        </div>


        <!-- ==========================================
             USERS CARD
        =========================================== -->

        <div class="users-card">

            <div class="card-header">

                <div>

                    <h2>
                        Registered Users
                    </h2>

                    <p>
                        Manage marketplace members
                    </p>

                </div>

                <div class="user-count">

                    <i class="fa-solid fa-users"></i>

                    <?php echo mysqli_num_rows($result); ?>

                    Users

                </div>

            </div>


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

                                <!-- ID -->

                                <td>

                                    <span class="user-id">

                                        #<?php echo (int)$user['id']; ?>

                                    </span>

                                </td>


                                <!-- USER -->

                                <td>

                                    <div class="user-profile">

                                        <div class="user-avatar">

                                            <i class="fa-solid fa-user"></i>

                                        </div>

                                        <div>

                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $user['name']
                                                );
                                                ?>

                                            </strong>

                                            <small>
                                                Marketplace User
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <div class="email-cell">

                                        <i class="fa-solid fa-envelope"></i>

                                        <span>

                                            <?php
                                            echo htmlspecialchars(
                                                $user['email']
                                            );
                                            ?>

                                        </span>

                                    </div>

                                </td>


                                <!-- PHONE -->

                                <td>

                                    <div class="phone-cell">

                                        <i class="fa-solid fa-phone"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $user['phone']
                                        );
                                        ?>

                                    </div>

                                </td>


                                <!-- ROLE -->

                                <td>

                                    <span class="role-badge">

                                        <i class="fa-solid fa-user"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            ucfirst($user['role'])
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php
                                    $status = strtolower(
                                        trim($user['status'])
                                    );
                                    ?>

                                    <span
                                        class="user-status <?php echo htmlspecialchars($status); ?>">

                                        <span class="status-dot"></span>

                                        <?php
                                        echo htmlspecialchars(
                                            ucfirst($status)
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- JOINED -->

                                <td>

                                    <div class="joined-date">

                                        <i class="fa-regular fa-calendar"></i>

                                        <?php

                                        echo date(
                                            "M d, Y",
                                            strtotime($user['created_at'])
                                        );

                                        ?>

                                    </div>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="user-actions">

                                        <a
                                            href="user_action.php?action=toggle_status&id=<?php echo (int)$user['id']; ?>"
                                            class="status-btn"
                                            title="<?php echo $status === 'active' ? 'Disable User' : 'Activate User'; ?>">

                                            <?php if ($status === 'active') { ?>

                                                <i class="fa-solid fa-user-slash"></i>

                                                Disable

                                            <?php } else { ?>

                                                <i class="fa-solid fa-user-check"></i>

                                                Activate

                                            <?php } ?>

                                        </a>


                                        <a
                                            href="user_action.php?action=delete&id=<?php echo (int)$user['id']; ?>"
                                            class="delete-btn"
                                            title="Delete User"
                                            onclick="return confirm('Are you sure you want to delete this user?');">

                                            <i class="fa-solid fa-trash"></i>

                                        </a>

                                    </div>

                                </td>

                            </tr>


                        <?php } ?>


                    <?php } else { ?>


                        <tr>

                            <td colspan="8">

                                <div class="no-data">

                                    <div class="no-data-icon">

                                        <i class="fa-solid fa-users-slash"></i>

                                    </div>

                                    <h3>
                                        No Users Found
                                    </h3>

                                    <p>

                                        <?php if ($search !== '') { ?>

                                            No users matched
                                            "<strong><?php echo htmlspecialchars($search); ?></strong>".

                                        <?php } else { ?>

                                            There are no registered users yet.

                                        <?php } ?>

                                    </p>

                                    <?php if ($search !== '') { ?>

                                        <a href="users.php">

                                            <i class="fa-solid fa-rotate-left"></i>

                                            View All Users

                                        </a>

                                    <?php } ?>

                                </div>

                            </td>

                        </tr>


                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>


<script src="../assets/js/admin_users.js"></script>

</body>

</html>