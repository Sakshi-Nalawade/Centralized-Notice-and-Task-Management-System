<?php
session_start();
include_once('./includes/auth-admin.php');
include_once('./includes/config.php');

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
    header('location:logout.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>IT Assets | MPSC Internal Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <style>
        .coming-soon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            padding: 40px 20px;
        }
        .coming-soon-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 48px 40px;
            text-align: center;
            max-width: 480px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .coming-soon-icon {
            font-size: 56px;
            color: #0f766e;
            margin-bottom: 18px;
            opacity: 0.8;
        }
        .coming-soon-title {
            font-size: 26px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }
        .coming-soon-text {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 24px;
            font-weight: 500;
        }
        .coming-soon-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: #ffffff;
            padding: 8px 22px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
    </style>
</head>
<body class="sb-nav-fixed">
<?php include_once('./includes/navbar.php'); ?>

<div id="layoutSidenav">
    <?php include_once('./includes/sidebar.php'); ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="coming-soon-wrap">
                <div class="coming-soon-card">
                    <div class="coming-soon-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <div class="coming-soon-title">IT Assets — Admin</div>
                    <div class="coming-soon-text">
                        This module is under development.<br>
                        Admin IT Assets management will be available soon.
                    </div>
                    <span class="coming-soon-badge">🚧 Coming Soon</span>
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