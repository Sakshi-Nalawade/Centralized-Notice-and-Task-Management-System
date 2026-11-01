<?php
include_once('./includes/auth-portal.php');
include_once('./includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Entry Form / Report | MPSC Bank Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/entry-forms.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">

<nav class="sb-topnav navbar navbar-expand navbar-dark" style="background:#099c78;">
    <a class="navbar-brand ps-3" href="entry-forms.php">MPSC</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <ul class="navbar-nav ms-auto me-3 me-lg-4 align-items-center">
        <li class="nav-item me-3 text-white">
            <?php echo htmlspecialchars($_SESSION['branch_name']); ?>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($_SESSION['login']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<div id="layoutSidenav">
    <?php include_once('./includes/entry-sidebar.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4 pt-4">
                <div class="entry-page-box">
                    <h2 class="entry-page-title">Entry Form / Report</h2>
                    <p class="entry-page-text">
                        Select an option from the left sidebar.
                    </p>
                </div>
            </div>
        </main>

        <footer class="py-3 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="small text-muted">MPSC Bank Portal</div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="./js/scripts.js"></script>
</body>
</html>