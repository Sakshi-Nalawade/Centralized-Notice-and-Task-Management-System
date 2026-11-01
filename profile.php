<?php
session_start();
include_once('./includes/config.php');

// Allow both admin and employee
if (!isset($_SESSION['userid']) || !isset($_SESSION['role'])) {
    header('location:index.php');
    exit();
}

$role = trim(strtolower($_SESSION['role']));
if ($role !== 'admin' && $role !== 'employee') {
    header('location:index.php');
    exit();
}

// Determine which sidebar and navbar to use
$isAdmin = ($role === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Profile | MPSC Internal Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<?php include_once('./includes/navbar.php'); ?>

<div id="layoutSidenav">
    <?php include_once('./includes/sidebar.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 pt-4">
                <h1 class="mt-2">My Profile</h1>
                <div class="card mb-4">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>Username</th>
                                <td><?php echo htmlspecialchars($_SESSION['login']); ?></td>
                            </tr>
                            <tr>
                                <th>Role</th>
                                <td><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></td>
                            </tr>
                            <?php if (!empty($_SESSION['branch_name'])) { ?>
                            <tr>
                                <th>Branch</th>
                                <td><?php echo htmlspecialchars($_SESSION['branch_name']); ?></td>
                            </tr>
                            <?php } ?>
                        </table>

                        <?php if ($isAdmin) { ?>
                        <a href="change-password.php" class="btn btn-primary">
                            <i class="fas fa-key me-1"></i> Change Password
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </main>
        <?php include_once('./includes/footer.php'); ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="./js/scripts.js"></script>
</body>
</html>