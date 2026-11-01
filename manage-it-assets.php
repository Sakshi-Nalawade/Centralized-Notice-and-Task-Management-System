<?php
include_once('./includes/auth-employee.php');
include_once('./includes/config.php');

$branch = $_SESSION['branch_name'];

// ─── Category definitions with icons ───
$categories = [
    'CPU'           => 'fas fa-microchip',
    'Monitor'       => 'fas fa-desktop',
    'Keyboard'      => 'fas fa-keyboard',
    'Mouse'         => 'fas fa-mouse-pointer',
    'Aio Desktop'   => 'fas fa-tv',
    'Projector'     => 'fas fa-video',
    'Printer'       => 'fas fa-print',
    'Scanner'       => 'far fa-clone',
    'Laptop'        => 'fas fa-laptop',
    'CCTV DVR'      => 'fas fa-hdd',
    'CCTV Camera'   => 'fas fa-camera',
    'N/W Devices'   => 'fas fa-wifi',
    'UPS'           => 'fas fa-car-battery',
    'Tower Server'  => 'fas fa-server',
    'ATM Machine'   => 'fas fa-money-bill-wave',
    'Biometric'     => 'fas fa-fingerprint',
    'Micro ATM'     => 'fas fa-credit-card',
];

// ─── Fetch counts per category for this branch ───
$counts = [];
$totalAssets = 0;

$stmtCount = mysqli_prepare($con, "SELECT category, COUNT(*) AS cnt FROM it_assets WHERE branch_name = ? GROUP BY category");
mysqli_stmt_bind_param($stmtCount, "s", $branch);
mysqli_stmt_execute($stmtCount);
$resultCount = mysqli_stmt_get_result($stmtCount);
while ($row = mysqli_fetch_assoc($resultCount)) {
    $counts[$row['category']] = (int)$row['cnt'];
    $totalAssets += (int)$row['cnt'];
}
mysqli_stmt_close($stmtCount);

// ─── Fetch all assets for the table ───
$stmtList = mysqli_prepare($con, "SELECT id, category, product, serial_id, label_name, status FROM it_assets WHERE branch_name = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmtList, "s", $branch);
mysqli_stmt_execute($stmtList);
$assetList = mysqli_stmt_get_result($stmtList);

// ─── Success message after add/edit ───
$success = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $success = 'Asset added successfully!';
    if ($_GET['msg'] === 'updated') $success = 'Asset updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>IT Assets | MPSC Bank Portal</title>
    <link href="./css/styles.css" rel="stylesheet" />
    <link href="./css/custom.css" rel="stylesheet" />
    <link href="./css/it-assets.css?v=2" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">

    <!-- ── Navbar (reuse entry-forms style for employee) ── -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background:#099c78;">
        <a class="navbar-brand ps-3" href="entry-forms.php">SDCC</a>
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
                <div class="container-fluid px-3">
                    <div class="it-assets-page">
                        <div class="it-assets-panel">

                            <!-- ── PAGE HEADER ── -->
                            <div class="ita-header">
                                <h1 class="ita-title"><i class="fas fa-desktop"></i> IT ASSETS</h1>
                                <div class="ita-branch-badge">
                                    <i class="fas fa-building"></i>
                                    <?php echo htmlspecialchars($branch); ?>
                                </div>
                            </div>

                            <?php if (!empty($success)) { ?>
                                <div class="ita-alert-success">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php } ?>

                            <!-- ── ASSET SUMMARY GRID ── -->
                            <div class="ita-summary-grid">
                                <?php foreach ($categories as $catName => $catIcon) {
                                    $cnt = isset($counts[$catName]) ? $counts[$catName] : 0;
                                ?>
                                    <div class="ita-card">
                                        <div class="ita-card-icon-wrap">
                                            <i class="<?php echo $catIcon; ?>"></i>
                                        </div>
                                        <div class="ita-card-label"><?php echo htmlspecialchars($catName); ?></div>
                                        <div class="ita-card-count"><?php echo $cnt; ?></div>
                                    </div>
                                <?php } ?>

                                <!-- TOTAL ASSETS card -->
                                <div class="ita-card ita-card-total">
                                    <div class="ita-card-icon-wrap total">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <div class="ita-card-label">TOTAL ASSETS</div>
                                    <div class="ita-card-count"><?php echo $totalAssets; ?></div>
                                </div>
                            </div>

                            <!-- ── ACTION BUTTONS ── -->
                            <div class="ita-actions">
                                <a href="add-it-asset.php" class="ita-btn ita-btn-primary" id="btnAddProduct">
                                    <i class="fas fa-plus-circle"></i> ADD PRODUCT
                                </a>
                                <button onclick="printTable()" class="ita-btn ita-btn-secondary" id="btnPrint">
                                    <i class="fas fa-print"></i> PRINT
                                </button>
                            </div>

                            <!-- ── ASSETS TABLE ── -->
                            <div class="ita-table-wrap" id="printableArea">
                                <div class="ita-table-header-bar">
                                    <span><i class="fas fa-list"></i> Asset List — <?php echo htmlspecialchars($branch); ?></span>
                                    <span class="ita-table-count"><?php echo $totalAssets; ?> records</span>
                                </div>
                                <div class="ita-table-scroll">
                                    <table class="ita-table" id="assetTable">
                                        <thead>
                                            <tr>
                                                <th>Sr.No</th>
                                                <th>Category</th>
                                                <th>Product</th>
                                                <th>Serial ID</th>
                                                <th>Label</th>
                                                <th>Status</th>
                                                <th class="no-print">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sr = 1;
                                            if ($assetList && mysqli_num_rows($assetList) > 0) {
                                                while ($asset = mysqli_fetch_assoc($assetList)) {
                                                    $statusClass = '';
                                                    switch ($asset['status']) {
                                                        case 'Available':
                                                            $statusClass = 'status-available';
                                                            break;
                                                        case 'Not Available':
                                                            $statusClass = 'status-unavailable';
                                                            break;
                                                        case 'Pending Transfer':
                                                            $statusClass = 'status-pending-transfer';
                                                            break;
                                                        case 'Transferred':
                                                            $statusClass = 'status-transferred';
                                                            break;
                                                        case 'Send To Repair':
                                                            $statusClass = 'status-repair';
                                                            break;
                                                        case 'Request to delete':
                                                            $statusClass = 'status-delete';
                                                            break;
                                                        case 'Replaced':
                                                            $statusClass = 'status-replaced';
                                                            break;
                                                        case 'Scrap':
                                                            $statusClass = 'status-scrap';
                                                            break;
                                                    }
                                            ?>
                                                    <tr>
                                                        <td><?php echo $sr++; ?></td>
                                                        <td><?php echo htmlspecialchars($asset['category']); ?></td>
                                                        <td><?php echo htmlspecialchars($asset['product']); ?></td>
                                                        <td><?php echo htmlspecialchars($asset['serial_id'] ?? '—'); ?></td>
                                                        <td><?php echo htmlspecialchars($asset['label_name'] ?? '—'); ?></td>
                                                        <td>
                                                            <span class="ita-status-badge <?php echo $statusClass; ?>">
                                                                <?php echo htmlspecialchars($asset['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="no-print">
                                                            <a href="edit-it-asset.php?id=<?php echo $asset['id']; ?>" class="ita-edit-btn" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                            ?>
                                                <tr>
                                                    <td colspan="7" class="ita-empty-row">
                                                        <i class="fas fa-inbox"></i> No assets found for this branch. Click "ADD PRODUCT" to get started.
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
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
    <script>
        function printTable() {
            window.print();
        }

        // Auto-hide success alert after 4 seconds
        const alertEl = document.querySelector('.ita-alert-success');
        if (alertEl) {
            setTimeout(() => {
                alertEl.style.transition = 'opacity 0.5s';
                alertEl.style.opacity = '0';
                setTimeout(() => alertEl.remove(), 500);
            }, 4000);
        }
    </script>
</body>

</html>
